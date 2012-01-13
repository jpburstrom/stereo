$.fn.log = function() {
    if (window.console && console.log) {
        console.log(this);
    }
    return this;
};

/*
test = function() {
    this.foo = 2;
    
    this.fap = function () {
        console.log(this.foo);
    }

}

t = new test();
t.fap();
*/

YuckBox = function(options) {
    
    var $ = jQuery;
    var sm = soundManager;
    var self = this;
    this.songs = [];
    this.sIndex = -1;
    this.options = {};
    this.playing = false;

    this.load = function() { self.songs[self.sIndex].load() };
    this.play = function() { self.songs[self.sIndex].play() };
    this.stop = function() { self.songs[self.sIndex].stop() };
    this.togglePause = function() { self.songs[self.sIndex].togglePause() };
    this.pause = function() { self.songs[self.sIndex].pause() };
    this.resume = function() { self.songs[self.sIndex].resume() };


    /**
     * Init function
     * @param Object options 
     */
    this.init = function(options) {
        options = $.extend( { repeat: false, playAll: true, songs : [] },  options );
        self.addSongs(options.songs);
        self.sIndex = 0;
        self.options = options;
        if (self.songs.length > 0)
            self.load();

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
            self.sIndex = currentLength;
            self.play();
        }
    }

    this.addSong = function(s, play) {
        var in_array = false;
        //Check for matching url
        for (var x in self.songs) {
            if (self.songs[x].url == s.url) {
                in_array = true;
                break;
            }
        }
        if (!in_array) {
            var snd = sm.createSound($.extend( { 
                id : "yuckbox-" + self.songs.length, 
                multiShot : false, 
                onload : self.events.load,      //load finished
                onstop : self.events.stop,      //user stop
                onfinish : self.events.finish,    //sound finished playing
                onpause : self.events.pause,     //pause
                onplay : self.events.play,      //play
                onresume : self.events.play,    //pause toggle
                whileloading : self.events.whileloading,
                whileplaying : self.events.whileplaying
            } , s ));
            if (snd) self.songs.push(snd);
        }
        if (play && !this.playing) {
            console.log("PLAY");
            self.sIndex = self.songs.length - 1;
            self.play();
        }
    };

    this._prevNext = function (pn, play) {
        if (self.songs.length > 1) {
            i = (self.sIndex + pn) % self.songs.length;
            if (self.songs[self.sIndex].playState == 1 || play) {
                self.songs[self.sIndex].stop();
                self.songs[i].play();
            }
            self.sIndex = i;
        }
    };

    /**
     * Play next song if...
     */
    this._playNext = function() {
        if ((self.options.playAll && ((self.sIndex + 1) != self.songs.length))
                || self.options.playAll && self.options.repeat) {
            self._prevNext(1, true);
        } else if (self.options.playAll && ((self.sIndex + 1))) {
            self._prevNext(1);
            return true;
        }
        return false;
    }

    this.events = {
        load : function() {
            $(document).trigger("load.yuckbox", this);
        },      //load finished
        stop : function () {
            this.playing = false;
            $(document).trigger("stop.yuckbox", this);
        },      //user stop
        finish : function() {
            this.playing = false;
            var playlistEnd = self._playNext();
            $(document).trigger("finish.yuckbox", [this, playlistEnd]);
        },    //sound finished playing
        pause : function() {
            this.playing = false;
            $(document).trigger("pause.yuckbox", this);
        },     //pause
        play : function() {
            this.playing = true;
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

    this.init(options);
};

//yuckbox = new YuckBox();

