(function(w, b, _){

    if (typeof(w.Stereo) != "undefined") {
        return;
    }

    var App = {
        
    };

    (function() {

        App.Song = Backbone.Model.extend({
            initialize: function(options) {
                this.id = options.id;
                this.snd = false;
            },
            play: function() { 
                if (!this.snd) 
                    this.snd = this.createSound(true);
            },
            pause: function() {
                if (this.snd)
                    this.snd.pause();
            },
            stop: function() {
                if (this.snd)
                    this.snd.stop();
                this.destroySound();
            },
            seek: function() {
            },
            getInfo: function(cb) {
            },
            createSound: function(autoplay) {
                return soundManager.createSound({
                    id: this.id,
                    url: "http://mik.bik/_dev/stereo2/_mp3/rain.mp3",
                    autoPlay: (autoplay === true)
                });
            },
            destroySound: function() {
                soundManager.destroySound(this.id);
                this.snd = false;
            }
        });

    })();

    (function() {


        var _lookupIndex = function(id) {
            if (typeof(id) == "undefined")
                return false;
            return this.indexOf(this.get(id));
        },
        _move = function(index, add) {
            var _mod = function (n, m) {
                return ((m % n) + n) % n;
            };
            if (this.length === 0) {
                return false;
            }
            index = (index + add);
            if (this.repeat) {
                return _mod(this.length, index);

            } else {
                if (index >= this.length || index < 0) {
                    return false;
                }
                return index;
            }

        };

        App.Playlist = Backbone.Collection.extend({
            model: App.Song,
            currentSong: false,
            repeat: false,
            setRepeat: function(b) {
                this.repeat = (b === true);
            },
            getPrev: function(index) {
                return _move.call(this, index, -1);
            },
            getPrevById: function(id) {
                var index = _lookupIndex.call(this, id);
                return _move.call(this, index, -1);
            },
            getNext: function(index) {
                return _move.call(this, index, 1);
            },
            getNextById: function(id) {
                var index = _lookupIndex.call(this, id);
                return _move.call(this, index, 1);
            }

        });

    })() ;
    
    App.playlist = new App.Playlist();
    App.playlist.on('remove', function(o) { o.destroySound(); });

    App.Player = Backbone.Model.extend({
        playlist: App.playlist,
        playState: 0,
        song: 0,

        play: function() { 
            var s = this.playlist.at(this.song);
            if (s) s.play();
        },
        pause: function() { 
            var s = this.playlist.at(this.song);
            if (s) s.pause();
        },
        stop: function() { 
            var s = this.playlist.at(this.song);
            if (s) s.stop();
        },
        prev: function() {
            this.stop();
            this.song = this.playlist.getPrev(this.song);
            this.play();
        },
        next: function() { 
            this.stop();
            this.song = this.playlist.getNext(this.song);
            this.play();
        }
        //TODO: get events and set playState, create/destroy etc
    });


    App.player = new App.Player();


    w.Stereo = App;

})(window, Backbone, _);

