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
            defaultPlaylist: false,
            playlistElem: false
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
            return yuckbox.play(el.data("yuckboxId"));
        };

        function buildPlaylist() {
            console.log(settings.playlistElem);
            $(settings.playlistElem).each(function() {
                pl = $("<ul class='playlist template-attachment' id='yuckboxLinksPlaylist'>").appendTo($(this));
                $.each(yuckbox.songs, function(i, val) {
                    var title = val._iO.artist + " – " + val._iO.title;
                    $('<li data-yuckbox-song="" data-yuckbox-id="'+ val.sID +'" class="noload item-' + i + ' single tracks yuckbox-playable"><span class="icon"/>'+title+'</li>').data(val._iO).appendTo(pl);
                });
            });
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
            buildPlaylist();
        }

        $(document).on("play.yuckbox", function(ev, snd) {
            $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("paused").addClass("playing");
        }).on("pause.yuckbox", function(ev, snd) {
                $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("playing").addClass("paused");
        }).on("stop.yuckbox finish.yuckbox", function(ev, snd) {
                $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("playing paused");
        })

        .on("click", "[data-yuckbox-song]", function(ev) {
            var playAction = !($(this).hasClass("noplay")) && ($(this).hasClass("play") || settings.playOnClick); 
            var loadAction = !($(this).hasClass("noload")) && ($(this).hasClass("load") || settings.loadOnClick); 
            if (loadAction) {
                loadElement($(this), playAction);
            } else if (playAction) {
                playElement($(this));
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
