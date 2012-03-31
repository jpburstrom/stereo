/**
 * YuckBox Links
 * Johannes Burström 2012
 */

(function($) {
    $.fn.yuckboxLinks = $.fn.yuckboxLinks || function( options ) {  
        var settings = $.extend( {
            baseURI: false,
            loadOnLoad: false,
            loadOnFirstLoad: true,
            loadOnClick: false,
            playOnClick: true,
            containerElement: "body",
            postCreation: function(e) {},
            defaultPlaylist: false
        }, options);

        var self = $(this);
        var currentSongs = null;

        function sortSongs() 
        {
            yuckbox.sort(function(a, b) {
                return $.inArray(a.sID, currentSongs) - $.inArray(b.sID, currentSongs);
            });
        }

        function loadElement(el, play) {
            o = el.data("yuckboxSong");
            return yuckbox.addSong(o, play);
        };

        function playElement(el) {
            o = el.data("yuckboxSong");
            return yuckbox.play(o.id);
        };

        function newPage(firstLoad) {
            currentSongs = []
            var len = $(settings.containerElement).find("[data-yuckbox-song]").each(function() {
                e = $(this);
                e.attr("data-yuckbox-id", e.data("yuckboxSong").id);
                if (settings.loadOnLoad || (settings.loadOnFirstLoad && firstLoad)) {
                    currentSongs.push(e.data("yuckboxSong").id);
                    loadElement(e, false);
                }
                e.addClass("yuckbox-playable");
                if (yuckbox.playing && e.data("yuckboxSong").id == yuckbox.currentSong.sID) {
                    e.addClass("playing")
                }
                settings.postCreation(e);
            }).length;
            if (len == 0 && firstLoad && settings.defaultPlaylist) {
                yuckbox.addSongs(settings.defaultPlaylist);
            }
            if (settings.loadOnLoad || (settings.loadOnFirstLoad && firstLoad)) {
                sortSongs();
            }
        }

        $(document).on("play.yuckbox", function(ev, snd) {
            $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("paused").addClass("playing");
        }).on("pause.yuckbox", function(ev, snd) {
                $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("playing").addClass("paused");
        }).on("stop.yuckbox finish.yuckbox", function(ev, snd) {
                $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("playing paused");
        })

        .on("click", "[data-yuckbox-song]", function(ev) {
            var playAction = $(this).hasClass("play") || settings.playOnClick; 
            var loadAction = $(this).hasClass("load") || settings.loadOnClick; 
            if (loadAction) {
                loadElement($(this), playAction);
            } else if (playAction) {
                playAction($(this).data("yuckboxSong").id);
            }
            ev.preventDefault();
            
        })
        .on("newpageload.yuckbox", function(ev) {
            newPage();
        });

        newPage(true);
        return this;

  };
})(jQuery)
