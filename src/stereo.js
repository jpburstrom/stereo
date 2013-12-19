/**
 * Stereo - a javascript audio player
 *
 * Johannes Burström 2013
 * Stereo may be freely distributed under the MIT license.
 */

/*global window: false, Backbone:false, _:false, console:false, jQuery:false*/

(function(w, b, _, $){

    "use strict";

    var App;

    w.Stereo = w.Stereo || {};
    App = w.Stereo;

    App.e = _.clone(Backbone.Events);

    (function() {

        //Taken from backbone
        var urlError = function() {
            throw new Error('A "url" property or function must be specified');
        };

        App.SongInfo = b.Model.extend({

            defaults: function() {
                return {
                    id: false,
                    title: "",
                    artist: "",
                    playlist: ""
                };
            },

            _isSynced: false,

            initialize: function() {
                this.once('change', function() {
                    this._isSynced = true;
                    this.trigger('hasInfo');
                });
            },

            urlRoot: function () {
                return App.options.urlRoot + "tracks/";
            },

            /**
             * Get SongInfo attributes
             * Doesn't return info, but triggers a hasInfo event as soon as info is available
             */
            getInfo: function(cb) {
                if (_.isFunction(cb)) {
                    this.once('hasInfo', cb);
                }
                if (!this._isSynced) {
                    this.fetch();
                } else {
                    this.trigger('hasInfo');
                }

            }

        });


        App.Song = b.Model.extend({

            /**
             * initialize
             *
             * @param string url Unique relative or absolute url
             * @param object options (optional) options
             */
            initialize: function(url, options) {
                this.id = url;
                this.options = _.extend({}, options);
                //this.url = this._fullURL(url, App.options.baseURL);
                this.info = new App.SongInfo({
                    id: url
                });
            },

            play: function() { 
                if (!this.snd) 
                    this.createSound();
                this.snd.play();
            },
            
            pause: function() {
                if (this.snd)
                    this.snd.pause();
            },

            stop: function() {
                if (this.snd) {
                    this.snd.stop();
                    this.destroySound();
                }
            },

            seek: function() {
                console.log("Seek: Not implemented");
            },

            createSound: function() {
                var self = this;
                this.snd = w.soundManager.createSound({
                    id: this.options.id,
                    url: this.url(),
                    onload: function() {
                        self.trigger('load', this);
                    },
                    onplay: function() {
                        self.trigger('play', this);
                    },
                    onpause: function() {
                        self.trigger('pause', this);
                    },
                    onstop: function() {
                        self.trigger('stop', this);
                    },
                    onfinish: function() {
                        self.trigger('finish', this);
                    },
                    onwhileloading : function() {
                        var amt = this.bytesLoaded / this.bytesTotal;
                        self.trigger("whileloading", this, amt);
                    },
                    onwhileplaying : function() {
                        var d = (this.readyState == 1) ? this.durationEstimate : this.duration,
                            amt = this.position / d;
                        self.trigger("whileplaying", this, amt);
                    }

                });
            },

            destroySound: function() {
                w.soundManager.destroySound(this.snd.id);
                delete this.snd;
            },

            urlRoot: function () {
                return App.options.urlRoot + App.options.streamingSlug;
            },

            /*
             * Create full URL
             * @param uri part of ur
             */
            url: function() {
                var u, url = this.id, base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
                if (this.isNew()) return base;
                if (base) {
                    u = base;
                    //If url is full path, return it
                    if (url.indexOf("http://") === 0 || 0 === url.indexOf("https://") || 0 === url.indexOf("//")) {
                        return url;
                    } 
                    //Remove leading slash
                    if (url.indexOf("/") === 0) {
                        url = url.substr(1, url.length);
                    }
                    if (u.lastIndexOf('/') !== u.length - 1) {
                        // append trailing slash, if needed
                        u += '/';
                    }
                    url = (u && u.lastIndexOf('/') !== -1 ? u.substr(0, u.lastIndexOf('/') + 1) : './') + url;
                }
                return  url;
            }
        });

    })();

    (function() {


        var _lookupIndex = function(id) {
            var c = this.get(id);
            if (typeof(c) == "undefined")
                return false;
            return this.indexOf(c);
            },
            _mod = function (n, m) {
                return ((m % n) + n) % n;
            },
            _move = function(index, add) {
                if (this.length === 0) {
                    this._index = -1;
                    return false;
                }
                index = (index + add);
                if (this._repeat) {
                    index = _mod(this.length, index);
                    this._index = index;
                    return this.at(index).id;

                } else {
                    if (index >= this.length || index < 0) {
                        this._index = -1;
                        return false;
                    }
                    this._index = index;
                    return this.at(index).id;
                }

            };

        App.Playlist = b.Collection.extend({
            model: App.Song,
            _repeat: true,
            _index: -1,
            /*
            setRepeat: function(b) {
                this._repeat = (b === true);
            },
            getRepeat: function() {
                return this._repeat;
            },
            */
            /**
            * Get id of previous song, given an index
            * If collection is empty, return false
            * @param int index index
            * @return string id
            */
            getPrev: function(index) {
                return _move.call(this, index, -1);
            },
            /**
            * Get id of previous song, given an id
            * If collection is empty, return false
            * If id doesn't exist, return id of first song in collection
            * @param string id id
            * @return string id
            */
            getPrevById: function(id) {
                var index = _lookupIndex.call(this, id);
                if (false === index)
                    return _move.call(this, index, 0);
                return _move.call(this, index, -1);
            },
            /**
            * Get id of next song, given an index
            * @param int index index
            * @return string id
            */
            getNext: function(index) {
                return _move.call(this, index, 1);
            },
            /**
            * Get id of next song, given an id
            * @param string id id
            * @return string id
            */
            getNextById: function(id) {
                var index = _lookupIndex.call(this, id);
                if (false === index)
                    return _move.call(this, index, 0);
                return _move.call(this, index, 1);
            },
            getSafe: function(id) {
                var index = _lookupIndex.call(this, id);
                return _move.call(this, index, 0);
            }
        });

    })() ;
    
    App.playlist = new App.Playlist();


    App.Player = b.Model.extend({
        _playStateLabels: ['stopped', 'playing', 'paused', 'loading'],
        playlist: App.playlist,
        initialize: function() {
            //Internal play state, use isPlaying etc instead
            this.set('playState', 0);
            //Song ID
            this.set('song', false);

            /**
             * Destroy sound and set playstate on remove
             */
            this.listenTo(this.playlist, 'remove', function(o) {
                if (o.id == this.get('song')) {
                    this.set('playState', 0);
                    this.set('song', false);
                }
                if (o.snd)
                    o.destroySound(); 
            });
        },
        play: function() { 
            this._play(this.getSongSafe());
        },
        pause: function() { 
            var s = this.getSongSafe();
            if (s) {
                this.set('playState', 2);
                s.pause();
            }
        },
        stop: function() { 
            var s = this.getSongSafe();
            this._stop(this.getSongSafe());
        },

        /**
         * Things to do when a song is finished
         */
        onFinish: function() {
            this.next();
            if (false === this.get('song')) {
                this.set('playState', 0);
            }

        },

        /**
         * Convenient functionz
         * @param string id Song to play
         */
        playPause: function(id) {
            var p = this.get('playState');
            //
            if (id && id != this.get('song')) {
                if (p > 0) this.stop();
                p = 0;
                this.setSong(id);
            }
            if (p == 1) {
                this.pause();
            } else {
                this.play();
            }
        },

        prev: function() {
            this._prevNext(-1);
        },
        next: function() { 
            this._prevNext(1);
        },
        isPlaying: function() {
            return this.get('playState') == 1;
        },
        isStopped: function() {
            return this.get('playState') === 0;
        },
        isPaused: function() {
            return this.get('playState') == 2;
        },

        /**
         * Play a song s, set playState and set up event listening
         * @param song s
         */
        _play: function(s) {
            if (s) {
                this.listenToOnce(s, 'finish', this.onFinish );
                this.set('playState', 1);
                s.play();
            } else {
                this.set('playState', 0);
            }
        },

        /**
         * Stop a song s, set playState and remove event listening
         * @param song s
         */
        _stop: function(s) {
            if (s) {
                this.set('playState', 0);
                this.stopListening(s);
                s.stop();
            }
        },

        /**
         * Set previous/next song, continuing to play if playstate > 0
         * @param int dir Direction (prev is < 0)
         */
        _prevNext: function(dir) {
            var s, o;
            this._stop(this.getSong());
            if (dir < 0) {
                s = this.playlist.getPrevById(this.get('song'));
            } else {
                s = this.playlist.getNextById(this.get('song'));
            }
            //This will stop and unsubscribe
            this.set('song', s);
            //..and let's play the new song
            this._play(this.getSong());

        },
        /**
         * Get song
         *
         * If song doesn't exist, returns false
         *
         * @return Song|bool
         */
        getSong: function() {
            return this.playlist.get(this.get('song')) || false;
        },

        /**
         * Get current song. 
         * If song doesn't exist, get the first available song from the playlist
         * and update the song attribute
         *
         * @return Song
         */
        getSongSafe: function() {
            var s;
            s = this.playlist.get(this.get('song'));
            if (!s) {
                s = this.playlist.getSafe(this.get('song'));
                this.set('song', s);
                return this.playlist.get(s);
            } else {
                return s;
            }

        },
        /**
         * Set song attribute
         * If id doesn't exist in playlist, set it to false
         *
         * @param string id
         */
        setSong: function(id) {
            var s = this.playlist.get(id);
            s = (s) ? s.id : false;
            this.set('song', s);
        },

        getPlayStateLabel: function(state) {
            state = (state === undefined) ? this.get('playState') : state;
            return this._playStateLabels[state];
        }
    });

    App.player = new App.Player();

    App.View = {};

    App.View.Buttons = b.View.extend({

        template: App.Tmpl.button,
        model: App.player,
        className: 'stereo-buttons',

        events: {
            "click .prev": function() { this.model.prev(); },
            "click .stop": function() { this.model.stop(); },
            "click .play": function() { this.model.play(); },
            //"click .pause": function() { this.model.pause(); },
            "click .next": function() { this.model.next(); }
        },

        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
            this.render();
        },

        render: function() {
            this.$el.html(this.template());
            return this;
        }

    });

    App.View.Label = b.View.extend({
        template: App.Tmpl.label,
        className: 'stereo-label',
        model: App.player,
        song:false,

        initialize: function() {
            if (this.options.template) {
                this.template = this.options.template;
            }
            this.listenTo(this.model, 'change', this.changeSong);
        },

        changeSong: function() {
            if (this.model.hasChanged("song")) {
                if (this.song) {
                    this.stopListening(this.song);
                }
                this.song = this.model.getSong();
                if (!this.song) {
                    this.render(true);
                } else {
                    this.listenToOnce(this.song.info, 'hasInfo', this.render);
                    this.song.info.getInfo();
                }
            }
        },

        render: function(empty) {
            /*
            if (empty) 
                this.$el.html(this.template({}));
            else
                this.$el.html(this.template(this.song.info.attributes));
                */

            return this;
        }

    });

    App.View.ContinousSongData = b.View.extend({
        model: App.player,
        song:false,

        initialize: function() {
            this.listenTo(this.model, 'change', this.changeSong);
        },

        changeSong: function() {
            var s;
            if (this.model.hasChanged("song")) {
                if (this.song) {
                    this.stopListening(this.song);
                }
                this.song = this.model.getSong();
                if (!this.song) {
                    this.empty();
                } else {
                    this.listenTo(this.song, 'whileloading', this.render);
                    this.listenTo(this.song, 'whileplaying', this.render);
                }
            }
        },

        empty: function() {
        },

        render: function(empty) {
            /*
            if (empty) 
                this.$el.html(this.template({}));
            else
                this.$el.html(this.template(this.song.info.attributes));
                */

            return this;
        }

    });

    App.View.Position = App.View.ContinousSongData.extend({
        //TODO: fix drag things
        initialize: function() {
            this.data = {};
            this.listenTo(this.model, 'change', this.changeSong);
        },
        className: 'stereo-position',
        template: App.Tmpl.position
    });

    App.View.Time = App.View.ContinousSongData.extend({
        className: 'stereo-time',
        template: App.Tmpl.time
    });

    App.View.ClassChanger = b.View.extend({
        _doChangeClass: function(url) {
            if (url && this.model.hasChanged('song')) {
                if (this.model.get('song') != url) {
                    this.el.className = this.className;
                } else {
                    this.$el.addClass("active");
                }
            }
            
            if (this.model.hasChanged("playState") && (!url || this.model.get('song') == url)) {
                this.$el.removeClass(this.model.getPlayStateLabel(this.model.previous('playState')))
                    .addClass(this.model.getPlayStateLabel());
            }
        }
    });

    App.View.Controls = App.View.ClassChanger.extend({
        className: 'stereo-controls',
        views: {},
        initialize: function() {
            var self = this;
            this.model = App.player;
            this.$el.addClass(this.className);
            _.each(self.options.order, function(thing) {
                self.views[thing] = new App.View[thing]();
                self.views[thing].render();
                self.$el.append(self.views[thing].$el);
            });
            this.listenTo(this.model, 'change', this.changeClass);
            return this;
        },

        changeClass: function() {
            this._doChangeClass();
            return this;
        }
    });

    App.View.PlaylistItem = App.View.ClassChanger.extend({

        initialize: function() {
            this.model = App.player;
            this.className = this.className || this.el.className;
            if (!this.options.url) {
                this.options.url = this.$el.data('stereo-track').toString();
            }
            this.options = _.extend(this.defaults(), this.options);
            if (this.options.template) {
                this.template = this.options.template;
            }
            this.listenTo(this.model, 'change', this.changeClass);
        },

        defaults: function() {
            return {
                url: false
            };
        },

        events: {
            "click": function(ev) {  
                ev.preventDefault();
                if (!this.model.playlist.get(this.options.url)) {
                    this.model.playlist.add(this.options.url);
                }
                this.model.playPause(this.options.url); 
            }
        },

        changeClass: function() {
            this._doChangeClass(this.options.url);
        }
    });

    App.views = {};

    if (!App.options) App.options = {}; 

    App.options = _.extend({
        urlRoot: "./_mp3/",
        streamingSlug: "stream",
        playlist: {
            onload: false, //('all'|id) //Fallback to all songs or playlist id
            repeat: true,
            shuffle: false //Shuffle loaded files
        },
        controls: {
            //Pass an id of the control container, which should exist in the source
            elements: "#stereo_controls",
            //Choose which components, and their source order
            order: ['Buttons', 'Label', 'Position', 'Time']
        },
        links: {
            elements: "[data-stereo-track]"
        },
        sm: {
        }
    }, App.options);

    App.init = function(options) {

        var rebuildViews = function(elements, items, constructor) {
            _.each(items, function(el) {
                //Shouldn't be a problem to remove these, since we're on a new page
                el.remove();
            });
            //items is passed by reference, so empty it like this:
            items.length = 0;

            $(elements).each(constructor);
        };

        options = _.extend({}, App.options, options); 

        //If controls, make controls
        if (options.controls && options.controls.elements) {
            App.views.controls = [];
            rebuildViews(options.controls.elements, App.views.controls, function() {
                App.views.controls.push( new App.View.Controls({ 
                    el: this,
                    order: options.controls.order
                }));
            });
        }

        if (options.links && options.links.elements) {
            //Rebuild links on init and history reload
            App.views.links = [];
            App.e.on("init history:load-finish", function(success_or_object) {
                if (false !== success_or_object) {
                    rebuildViews(options.links.elements, App.views.links, function() {
                        App.views.links.push(new App.View.PlaylistItem({
                            el: this
                        }));
                        App.playlist.add($(this).data("stereo-track").toString());
                    });
                }
            });
        }

        App.e.trigger("init", options);
    };

    w.soundManager.setup(App.options.sm);

    w.soundManager.onload = function() {
        if (App.options.doInit) {
            App.init();
        }
    };


})(window, Backbone, _, jQuery);

