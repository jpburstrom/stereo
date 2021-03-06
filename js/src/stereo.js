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

    App.e = _.clone(b.Events);
    App.backbone = b;
    App.underscore = _;

    //HACK: We need a global underscore for templates
    if (typeof window._ == "undefined") {
        window._ = _;
    }

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
                this.set("id", url.toString());
                this.options = _.extend({}, options);
                //this.url = this._fullURL(url, App.options.baseURL);
                this.info = new App.SongInfo({
                    id: this.id
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

            seek: function(x) {
                if (this.snd) {
                    this.snd.setPosition(x * this.snd.durationEstimate);
                }
            },

            createSound: function() {
                var self = this;
                this.snd = w.soundManager.createSound({
                    id: this.id,
                    url: this.url(),
                    onload: function() {
                        if (this.readyState == 2) {
                            self.trigger('loaderror', this);
                        } else {
                            self.trigger('load', this);
                            self.off('load');
                        }
                    },
                    onplay: function() {
                        self.once('load', function() {
                            self.trigger('play', this);
                        });
                    },
                    onresume: function() {
                        self.trigger('resume', this);
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
                    whileloading : function() {
                        var amt = this.bytesLoaded / this.bytesTotal;
                        self.trigger("whileloading", this, amt);
                    },
                    whileplaying : function() {
                        var d = (this.readyState == 1) ? this.durationEstimate : this.duration,
                            amt = this.position / d;
                        self.trigger("whileplaying", this, amt, this.position);
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
        _playStateLabels: ['stopped', 'playing', 'paused', 'loading', 'error'],
        _orphanSong: false,
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
            this.listenTo(this.playlist, 'reset', function(o, opt) {
                if (this.get('playState') == 1) {
                    var self = this;
                    if (this._orphanSong === false) {
                        this._orphanSong = _.filter(opt.previousModels, function(m) {
                            return m.id == self.get('song');
                        })[0];
                    }
                }
            });

            this.listenTo(this.playlist, 'add', function(o) {
                if (this.playlist.length == 1 && this.get('playState') === 0) {
                    this.setSong(this.playlist.at(0));
                }
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
            this._stop(this.getSongSafe());
        },

        seek: function(x) {
            var s = this.getSongSafe();
            if (s) {
                s.seek(x);
            }
        },

        /**
         * Things to do when a song is finished
         */
        onFinish: function() {
            if (this._orphanSong) {
                this._stop(this._orphanSong);
                this._orphanSong = false;
            }
            this.next();
            if (false === this.get('song')) {
                this.set('playState', 0);
            }

        },

        onError: function() {
            this.set('playState', 4);
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
                this.listenToOnce(s, 'loaderror', this.onError );
                this.set('playState', 3);
                this.listenToOnce(s, 'play resume', function() {
                    this.set('playState', 1);
                });
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
            this._orphanSong = false;
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
            var s = (this._orphanSong !== false)  ? this._orphanSong : this.playlist.get(this.get('song'));
            return s || false;
        },

        /**
         * Get current song. 
         * If song doesn't exist, get the first available song from the playlist
         * and update the song attribute
         *
         * @return Song
         */
        getSongSafe: function() {
            var s = (this._orphanSong !== false)  ? this._orphanSong : this.playlist.get(this.get('song'));
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
            "click .playpause": function() { this.model.playPause(); },
            "click .pause": function() { this.model.pause(); },
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
        template: function(obj) {
            var __p = "";
            _.each(App.options.controls.label_order, function(el) {
                __p += App.Tmpl[el](obj);
            });
            return __p;
        },
        className: 'stereo-label',
        model: App.player,
        song:false,
        $current: false,
        ticker:false,

        initialize: function(options) {
            this.listenTo(this.model, 'change', this.changeSong);
        },

        changeSong: function() {
            if (this.model.hasChanged("song")) {
                if (this.song !== false) {
                    this.stopListening(this.song);
                }
                this.song = this.model.getSong();
                this.listenToOnce(this.song.info, 'hasInfo', this.render);
                this.listenTo(this.song, 'loaderror', this.renderError);
                this.song.info.getInfo();
            }
        },

        renderError: function() {
            this.stopListening(this.song.info);
            this.$el.html("<span class='load-error'>Error loading file</span>");
        },
        render: function() {
            if (this.song !== false) {
                this.$el.html(this.template(this.song.info.attributes));
                if (App.options.controls.labelTicker) {
                    if (this.$el.children(":visible").length > 1) {
                        this.$current = this.$el.children(":first:visible").css("top", "100%").animate({top: 0}, 400);
                        this.animate();
                    }
                } else {
                    this.$el.css("display", "none").fadeIn(100);
                }
            }

            return this;
        },

        animate: function() {
            w.clearInterval(this.ticker);
            this.ticker = w.setInterval(_.bind(this.tick, this), 4000);
        },

        tick: function() {
            this.$current.animate({top: "-100%"}, 200);
            if (this.$current.siblings(":visible").first().length > 0) {
                this.$current = this.$current.siblings(":visible").first();
            } else {
                this.$current = this.$current.siblings().first(":visible");
            }
            this.$current.css("top", "100%").animate({top: 0}, 400);
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
                    this.listenTo(this.song, 'whileloading', this.whileloading);
                    this.togglePlayProgress(true);
                }
            }
        },

        togglePlayProgress: function(play) {
            if (play) {
                this.listenTo(this.song, 'whileplaying', this.whileplaying);
            } else {
                this.stopListening(this.song, 'whileplaying');
            }
        }

    });

    App.View.Position = App.View.ContinousSongData.extend({
        //TODO: fix drag things
        initialize: function() {
            
            this.listenTo(this.model, 'change', this.changeSong);
            this.$el.html(this.template);
            this.$el.on('tap', this.ontap.bind(this));
            this.$el.on('drag', this.onmove.bind(this));
            this.$loaded = this.$el.find('.loaded');
            this.$played = this.$el.find('.played');
            
        },
        className: 'stereo-position',
        template: App.Tmpl.position,
        empty: function() {
            this.$loaded.css("width", "0%");
            this.$played.css("left", "0%");
        },
        whileloading: function(ev, val) {
            this.$loaded.css("width", (val * 100) + "%");
        },
        whileplaying: function(ev, val, pos) {
            this.$played.css("left", (val * 100) + "%").attr("title", pos);
        },
        ontap: function(ev) {
            if (!this.model.isPlaying()) {
                this.model.play();
            }
            this.model.seek(this._calcSeek(ev.x));
        },
        onmove: function(ev) {
            if (ev.end === false) {
                this.togglePlayProgress(false);
                this.$played.css("left", this._calcPos(ev.x) + "px");
            } else {
                this.togglePlayProgress(true);
                if (!this.model.isPlaying()) {
                    this.model.play();
                }
                this.model.seek(this._calcSeek(ev.x));
            }
            ev.preventDefault();
            ev.stopPropagation();
        },
        _calcSeek: function(x) {
            return (x - this.$el.offset().left) / this.$el.width();
        },
        _calcPos: function(x) {
            return (x - this.$el.offset().left);
        }

    });

    App.View.Time = App.View.ContinousSongData.extend({
        className: 'stereo-time',
        template: App.Tmpl.time
    });

    App.View.ClassChanger = b.View.extend({
        _changeActive: function(url) {
            if (this.model.get('song') != this.url) {
                this.el.className = this.className;
            } else {
                this.$el.addClass("active");
            }
        },
        _changePlayState: function() {
            if (!this.url || this.model.get('song') == this.url) {
                this.$el.removeClass(this.model.getPlayStateLabel(this.model.previous('playState')))
                    .addClass(this.model.getPlayStateLabel());
            }
        },

        _initClass: function() {
            this._changeActive();
            this._changePlayState();
        },
        _doChangeClass: function(url) {
            if (url && this.model.hasChanged('song')) {
                this._changeActive(url);
            }
            if (this.model.hasChanged("playState")) {
                this._changePlayState();
            }
        }
    });

    App.View.Controls = App.View.ClassChanger.extend({
        className: 'stereo-controls',
        views: {},
        initialize: function(options) {
            var self = this;
            this.model = App.player;
            this._initClass();
            this.$el.addClass(this.className);
            if (App.options.controls.labelTicker) {
                this.$el.addClass('ticker');
            }
            _.each(options.order, function(thing) {
                self.views[thing] = new App.View[thing]();
                self.views[thing].render();
                self.$el.append(self.views[thing].$el);
            });
            this.listenTo(this.model, 'change', this.changeClass);
            //this._initClass();
            return this;
        },

        changeClass: function() {
            this._doChangeClass();
            return this;
        }
    });

    App.View.PlaylistItem = App.View.ClassChanger.extend({

        initialize: function(options) {
            this.model = App.player;
            this.className = this.className || this.el.className;
            if (!options.url) {
                options.url = this.$el.data('stereo-track');
            }
            this.url = options.url;
            this.template = options.template;
            this.listenTo(this.model, 'change', this.changeClass);
            this._initClass();
        },

        events: {
            "click": function(ev) {  
                ev.preventDefault();
                if (!this.model.playlist.get(this.url)) {
                    this.model.playlist.add(this.url);
                }
                this.model.playPause(this.url); 
            }
        },

        changeClass: function() {
            this._doChangeClass(this.url);
        }
    });

    App.views = {};

    if (!App.options) App.options = {}; 

    App.options = _.extend({
        urlRoot: "./_mp3/",
        streamingSlug: "stream",
        doInit: true,
        playlist: {
            onload: false, //('all'|id) //Fallback to all songs or playlist id
            repeat: true,
            shuffle: false //Shuffle loaded files
        },
        controls: {
            //Pass an id of the control container, which should exist in the source
            elements: "#stereo_controls",
            //Choose which components, and their source order
            order: ['Buttons', 'Label', 'Position', 'Time'],
            label_order: ['title', 'playlist-artist', 'playlist'],
            labelTicker: false
        },
        links: {
            elements: "[data-stereo-track]"
        },
        sm: {
        },
        default_tracks: false
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

        //Make controls. 
        if (options.controls && options.controls.elements) {
            App.views.controls = [];
            rebuildViews(options.controls.elements, App.views.controls, function() {
                App.views.controls.push( new App.View.Controls({ 
                    el: this,
                    order: options.controls.order
                }));
            });
        }

        //
        //Fill playlist with link urls, and connect views to the links.
        if (options.links && options.links.elements) {
            App.views.links = [];
            //Rebuild links on init and history reload
            App.e.on("init history:load-finish", function(success_or_object) {
                var do_reset = true;
                if (false !== success_or_object) {
                    rebuildViews(options.links.elements, App.views.links, function() {
                        //If we have links on the page, let's reset the playlist and
                        //use these links as playlist instead. Since this is running in a
                        //loop, only do it once.
                        if (do_reset) {
                            App.playlist.reset();
                            do_reset = false;
                        }
                        App.views.links.push(new App.View.PlaylistItem({
                            el: this
                        }));
                        App.playlist.add($(this).data("stereo-track"));
                    });
                }
            });
        }

        if (options.default_tracks) {
            var t = options.default_tracks;
            if (t.default_track_mode == "random") {
                t.tracks = _.shuffle(t.tracks).slice(0, Math.max(0, t.track_count));
            }

            //We add default tracks. They will be removed if there are other tracks on the page.
            App.playlist.add(t.tracks);
        }

        App.e.trigger("init", options);
    };

    w.soundManager.setup(App.options.sm);

    w.soundManager.onload = function() {
        if (App.options.doInit) {
            App.init();
        }
    };


})(window, Backbone.noConflict(), _.noConflict(), jQuery);

