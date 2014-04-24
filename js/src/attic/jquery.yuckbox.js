/**
 * YuckBox 
 * Playback controller & playlist
 *
 * Johannes Burström 2012
 */

(function($) {

    YuckBox = function( options ) {  

        var sm = soundManager;
        var self = this;
        this.api = {};
        this.songs = [];
        this.sIndex = -1;
        this.currentSong = null;
        this.options = {};
        this.playing = false;
        this.baseurl = "";
        this.seeking = false;

        this.api.load = this.load = function() { 
            self.currentSong.load(); 
        };
        this.api.play = this.play = function(id) { 
            if (self.playing && self.currentSong.sID == id) {
                self.currentSong.pause();
                return;
            }
            if (typeof(id) === "undefined" || self._setSongFromURI(id) ) {
                if (self.playing) {
                    this.playing.stop();
                }
                self.currentSong.play();
            }
        };
        this.api.stop = this.stop = function() {  if (null !== self.currentSong) self.currentSong.stop().setPosition(0); };
        this.api.togglePause = this.togglePause = function() { if (null !== self.currentSong) self.currentSong.togglePause(); };
        this.api.pause = this.pause = function() { 
           if (null !== self.currentSong) self.currentSong.pause();
        };
        this.api.resume = this.resume = function() { if (null !== self.currentSong) self.currentSong.resume(); };


        this.api.isPlaying = function() {
            return self.playing !== false;
        };

        this.api.get = function(v) {
            return self[v];
        };


    /**
     * Init function
     * @param Object options 
    */
        this.api.init = this.init = function(options) {
            options = $.extend( { repeat: false, playAll: true },  options );
            //self.addSongs(options.songs);
            self._setSong(0);
            self.options = options;
            if (options.baseurl) {
                this.baseurl = options.baseurl;
            }
            //if (self.songs.length > 0)
            //    self.load();

        };

    /**
     * Go to next song
     * @see this._prevnext
    */
        this.api.next = this.next = function (play) {
            self._prevNext(1, play);
        };

    /**
     * Go to prev song
     * @see this._prevnext
    */
        this.api.prev = this.prev = function (play) {
            self._prevNext(-1, play);
        };

    /**
     * Set volume for all songs
    */
        this.api.setVolume = this.setVolume = function(vol) {
            for (var i in self.songs) {
                self.songs[i].setVolume(vol);
            }
        };

        this.api.addSongs = this.addSongs = function(s, play) {
            var currentLength = self.songs.length;
            for (var i in s) {
                self.addSong(s[i], false);
            }
            if (play && !this.playing && (currentLength != self.songs.length)) {
                self._setSong(currentLength);
                self.play();
            }
        }

        this.api.addSong = this.addSong = function(s, playAction) {
            var in_array = false;
            //Check for matching url
            for (var x in self.songs) {
                if (self.songs[x].sID == s.id) {
                    in_array = x;
                    break;
                }
            }
            if (in_array === false) {
                var baseurl = (typeof(s.baseurl) == "undefined" || false === s.baseurl) ? self.baseurl : s.baseurl;
                var options = $.extend( { 
                    id : "yuckbox-" + self.songs.length,  //default
                    url : "",
                    title : "",
                    artist : "",
                    album : "",
                    multiShot : false, 
                    onload : self.events.load,      //load finished
                    onstop : self.events.stop,      //user stop
                    onfinish : self.events.finish,    //sound finished playing
                    onpause : self.events.pause,     //pause
                    onplay : self.events.play,      //play
                    onresume : self.events.play,    //pause toggle
                    whileloading : self.events.whileloading,
                    whileplaying : self.events.whileplaying
                } , s );
                if (options.url.indexOf("http://")) {
                    options.url = baseurl + options.url;
                }
                var snd = sm.createSound(options);
                if (snd) {
                    self.songs.push(snd);
                    in_array = self.songs.length - 1;
                    $(document).trigger("addedsong.yuckbox", snd);
                } else {
                    return false;
                }
            }

            if (playAction) {
                if (in_array != self.sIndex) {
                    self.stop();
                    self._setSong(in_array);
                    self.play();
                } 
                else if (!self.playing) {
                    self._setSong(in_array);
                    self.playing = self.currentSong;//First we set the current song as playing, needed for fast clicks...
                    self.play();
                } else {
                    self.pause();
                }
            } else if (!self.playing && !self.currentSong) {
                self._setSong(in_array);
            }
            return true;
        };

        this.api.sort = this.sort = function(fn) {
            fn && (self.songs = self.songs.sort(fn));
            self.sIndex = $.inArray(self.currentSong, self.songs);
        }

        this._prevNext = function (pn, play) {
            if (self.songs.length > 1) {
                i = (self.sIndex + pn + self.songs.length) % self.songs.length;
                if (self.playing || play) {
                    self.songs[self.sIndex].stop();
                    self.songs[i].play();
                } else {
                    self.songs[i].load();
                }

                self._setSong(i);
            }
        };

    /**
     * Play next song if...
*/
        this._playNext = function() {
            if ((self.options.playAll && ((self.sIndex + 1) != self.songs.length))
                || (self.options.playAll && self.options.repeat)) {
                    self._prevNext(1, true);
                } else if (self.options.playAll) {
                    self._prevNext(1);
                    return true;
                }
                return false;
        }

        this._setSongFromURI = function(id) {
            if (false === id) return false;
            song = false;
            for (var x in self.songs) {
                if (self.songs[x].options.id == id) {
                    song = x;
                    break;
                }
            }
            if (song) {
                self._setSong(parseInt(song));
                return true;
            } else {
                return false;
            }
        }

        this._setSong = function(index) {
            self.sIndex = parseInt(index);
            self.currentSong = (self.songs[self.sIndex] != null) ? self.songs[self.sIndex] : null;
            $(document).trigger("currentchanged.yuckbox", self.currentSong);
        }

        this.events = {
            load : function(success) {
                $(document).trigger("load.yuckbox", [this, success]);
            },      //load finished
            stop : function () {
                self.playing = false;
                $(document).trigger("stop.yuckbox", this);
            },      //user stop
            finish : function() {
                self.playing = false;
                var playlistEnd = self._playNext();
                $(document).trigger("finish.yuckbox", [this, playlistEnd]);
            },    //sound finished playing
            pause : function() {
                self.playing = false;
                if (self.seeking) {
                    $(document).trigger("seek.yuckbox", this);
                } else {
                    $(document).trigger("pause.yuckbox", this);
                }
            },     //pause
            play : function() {
                self.playing = this;
                $(document).trigger("play.yuckbox", this);
            },      //play
        /* resume = play
        resume : function() {
            //console.log("resume");
        },    //pause toggle
*/
            whileloading : function() {
                amt = this.bytesLoaded / this.bytesTotal;
                $(document).trigger("whileloading.yuckbox", [this, amt]);
            },
            whileplaying : function() {
                var d = (this.readyState == 1) ? this.durationEstimate : this.duration; 
                amt = this.position / d;
                $(document).trigger("whileplaying.yuckbox", [this, amt]);
            }
        }

        //this.init(options);
        
        return self.api;
    };

    yuckbox = new YuckBox();
    window.YuckBox = YuckBox;
    window.yuckbox = yuckbox; 

})(jQuery);

