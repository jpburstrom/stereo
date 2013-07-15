/**
 * Stereo - a javascript audio player
 *
 * Johannes Burström 2013
 * Stereo may be freely distributed under the MIT license.
 */

/*global window: false, Backbone:false, _:false, console:false, jQuery:false*/

(function(w, b, _, $){

    "use strict";

    if (typeof(w.Stereo) != "undefined") {
        return;
    }

    var App = { 
        options: {}
    };


    (function() {

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
                return App.options.infoURL;
            },

            /**
             * Get SongInfo attributes
             * Doesn't return info, but triggers a hasInfo event as soon as info is available
             */
            getInfo: function() {
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
                return App.options.urlRoot;
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
            _repeat: false,
            _index: -1,
            setRepeat: function(b) {
                this._repeat = (b === true);
            },
            getRepeat: function() {
                return this._repeat;
            },
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
            var s = this.getSong();
            if (s) {
                this.set('playState', 1);
                s.play();
            }
        },
        pause: function() { 
            var s = this.getSong();
            if (s) {
                this.set('playState', 2);
                s.pause();
            }
        },
        stop: function() { 
            var s = this.getSong();
            if (s) {
                this.set('playState', 0);
                s.stop();
            }
        },

        /**
         * Convenient
         */
        playPause: function(s) {
            var p = this.get('playState');
            if (s && s != this.get('song')) {
                if (p > 0) this.stop();
                p = 0;
                this.setSong(s);
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
         * Set previous/next song, continuing to play if playstate > 0
         * @param int dir Direction (prev is < 0)
         */
        _prevNext: function(dir) {
            var s;
            if (dir < 0) {
                s = this.playlist.getPrevById(this.get('song'));
            } else {
                s = this.playlist.getNextById(this.get('song'));
            }
            if (this.get('playState') === 0) {
                this.set('song', s);
                return;
            }

            this.stop();
            this.set('song', s);
            this.play();
        },
        /**
         * Get current song, and update the song attribute
         *
         * @return Song
         */
        getSong: function() {
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
        setSong: function(id) {
            var s = this.playlist.get(id);
            s = (s) ? s.id : false;
            this.set('song', s);
        }
    });

    App.player = new App.Player();

    App.View = {};

    App.View.Buttons = b.View.extend({

        template: function() { return App.Tmpl.button; },
        model: App.player,

        events: {
            "click .prev": function() { this.model.prev(); },
            "click .stop": function() { this.model.stop(); },
            "click .play": function() { this.model.play(); },
            //"click .pause": function() { this.model.pause(); },
            "click .next": function() { this.model.next(); }
        },

        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
        },

        render: function(redraw) {
            //TODO
            return this;
        }

    });

    App.View.PlaylistItem = b.View.extend({
        defaults: function() {
            return {
                songid: false
            };
        },

        model: App.player,

        events: {
            "click": function() {  this.model.playPause(this.options.songid); }
        },

        initialize: function() {
            this.options = _.extend(this.defaults(), this.options);
            if (this.options.template) {
                this.template = this.options.template;
            }
            this.listenTo(this.model, 'change', this.render);
        },

        render: function(redraw) {
            if (this.model.hasChanged('song') && this.model.get('song') != this.options.songid) {
                this.$el.removeClass('stopped playing paused active');
            } 
            if (this.model.hasChanged("playState") && this.model.get('song') == this.options.songid) {
                switch(this.model.get('playState')) {
                    case 0:
                        this.$el.addClass("active stopped").removeClass("playing paused");
                        break;
                    case 1:
                        this.$el.addClass("active playing").removeClass("stopped paused");
                        break;
                    case 2:
                        this.$el.addClass("active paused").removeClass("stopped playing");
                        break;
                }
            }
            return this;
        }
    });

    App.View.Label = b.View.extend({
        template: function(d) { return App.Tmpl.label(d); },
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
            if (empty) 
                this.$el.html(this.template({}));
            else
                this.$el.html(this.template(this.song.info.attributes));

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
                    this.render(true);
                } else {
                    this.listenTo(this.song, 'change', this.render);
                }
            }
        },

        render: function(empty) {
            if (empty) 
                this.$el.html(this.template({}));
            else
                this.$el.html(this.template(this.song.info.attributes));

            return this;
        }

    });

    App.View.Position = App.View.ContinousSongData.extend({
        template: function(d) { return App.Tmpl.position(d); }
    });

    App.View.Time = App.View.ContinousSongData.extend({
        template: function(d) { return App.Tmpl.time(d); }
    });

    w.Stereo = App;

})(window, Backbone, _, jQuery);

