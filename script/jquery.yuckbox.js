$.fn.log = function() {
    if (window.console && console.log) {
        console.log(this);
    }
    return this;
};


(function( $ ){
    
    var sm = soundManager;
    var songs = [];
    var sIndex = -1;

    //methods to pass on to sm
    var smSoundMethods = ["load", "stop", "play", "togglePause", "pause", "resume"];
    var smMethods = [];

    var addSongs = function(s) {
        for (var i in s) {
            var in_array = false;
            //Check for matching url
            for (var x in songs) {
                if (songs[x].url == s[i].url) {
                    in_array = true;
                    break;
                }
            }
            if (!in_array) {
                var snd = sm.createSound($.extend( { id : "yuckbox-" + songs.length, multiShot : false, } , s[i] ));
                if (snd) songs.push(snd);
            }
        }
    }

    var prevNext = function (pn, play) {
        if (songs.length > 1) {
            i = (sIndex + pn) % songs.length;
            console.log(i);
            if (songs[sIndex].playState == 1 || play) {
                songs[sIndex].stop();
                songs[i].play();
            }
            sIndex = i;
        }
    };

    var methods = {
        init: 
            function( options ) {
                if (options !== undefined) {
                    addSongs(options.songs);
                    sIndex = 0;
                }
                data = false;
                return this.each(function(){

                   // If the plugin hasn't been initialized yet
                   if ( ! data ) {

                   }
               });
            },
        addSongs: 
            function(options) {
                addSongs(options);
            },
        getSongs: 
            function() {
                return songs;
            },
        /**
         * Prev/Next: if play = true, start playing
         */
        next: 
            function (play) {
                prevNext(1, play);
            },
        prev: 
            function (play) {
                prevNext(-1, play);
            },
        /**
         * Set volume for all songs
         */
        setVolume: 
            function(vol) {
                for (var i in songs) {
                    songs[i].setVolume(vol);
                }

            },

    };

    $.fn.yuckbox = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else if ( songs[sIndex] !== undefined && $.inArray(method, smSoundMethods)) {
            return songs[sIndex][method]();
        } else if ( $.inArray(method, smMethods)) {
            sm[method];
            return this;
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.yuckbox' );
        }    

    };

})( jQuery );

