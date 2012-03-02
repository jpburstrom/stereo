/**
 * YuckBox 
 * Playback controller & playlist
 *
 * Johannes Burström 2012
 */

YuckBox = function(options) {
    
    var $ = jQuery;
    var sm = soundManager;
    var self = this;
    this.songs = [];
    this.sIndex = -1;
    this.currentSong = null;
    this.options = {};
    this.playing = false;
    this.baseURI = "";

    this.load = function() { self.songs[self.sIndex].load() };
    this.play = function(id) { 
        if(self._setSongFromURI(id) || typeof(id) === "undefined") {
            if (self.playing) {
                this.playing.stop();
            }
            self.currentSong.play() 
        }
    };
    this.stop = function() { ( self.currentSong && self.currentSong.stop().setPosition(0)); };
    this.togglePause = function() { (self.currentSong && self.currentSong.togglePause()) };
    this.pause = function() { (self.currentSong && self.currentSong.pause()) };
    this.resume = function() { self.currentSong && self.currentSong.resume() };


    /**
     * Init function
     * @param Object options 
     */
    this.init = function(options) {
        options = $.extend( { repeat: false, playAll: true },  options );
        //self.addSongs(options.songs);
        self._setSong(0);
        self.options = options;
        if (options.baseURI) {
            this.baseURI = options.baseURI;
        }
        //if (self.songs.length > 0)
        //    self.load();

    };

    /**
     * Go to next song
     * @see this._prevnext
     */
    this.next = function (play) {
        self._prevNext(1, play);
    };

    /**
     * Go to prev song
     * @see this._prevnext
     */
    this.prev = function (play) {
        self._prevNext(-1, play);
    };

    /**
     * Set volume for all songs
     */
    this.setVolume = function(vol) {
        for (var i in self.songs) {
            self.songs[i].setVolume(vol);
        }
    };

    this.addSongs = function(s, play) {
        var currentLength = self.songs.length;
        for (var i in s) {
            self.addSong(s[i], false);
        }
        if (play && !this.playing && (currentLength != self.songs.length)) {
            self._setSong(currentLength);
            self.play();
        }
    }

    this.addSong = function(s, play) {
        var in_array = false;
        //Check for matching url
        for (var x in self.songs) {
            if (self.songs[x].id == s.id) {
                in_array = true;
                break;
            }
        }
        if (!in_array) {
            var baseURI = (typeof(s.baseURI) == "undefined" || false === s.baseURI) ? self.baseURI : s.baseURI;
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
            options.url = baseURI + options.url;
            var snd = sm.createSound(options);
            if (snd) {
                self.songs.push(snd);
                $(document).trigger("addedsong.yuckbox", this);
            }
        }
        if (in_array || snd) {
            if (play && !this.playing) {
                self._setSong(self.songs.length - 1);
                self.play();
            }
            return true;
        }
        return false;
    };

    this._prevNext = function (pn, play) {
        if (self.songs.length > 1) {
            i = (self.sIndex + pn) % self.songs.length;
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
        self.sIndex = index;
        self.currentSong = (self.songs[self.sIndex] != null) ? self.songs[self.sIndex] : null;
    }

    this.events = {
        load : function() {
            $(document).trigger("load.yuckbox", this);
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
            $(document).trigger("pause.yuckbox", this);
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
};

yuckbox = new YuckBox();

