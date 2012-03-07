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
            postCreation: function(e) {}
        }, options);

        var self = $(this);

        function loadElement(el, play) {
            o = el.data("yuckboxSong");
            return yuckbox.addSong(o, play);
        };

        function playElement(el) {
            o = el.data("yuckboxSong");
            return yuckbox.play(o.id);
        };

        function newPage(firstLoad) {
            $(settings.containerElement).find("[data-yuckbox-song]").each(function() {
                $(this).attr("data-yuckbox-id", $(this).data("yuckboxSong").id);
                if (settings.loadOnLoad || (settings.loadOnFirstLoad && firstLoad)) {
                    loadElement($(this), false);
                }
                $(this).addClass("yuckbox-playable");
                settings.postCreation($(this));
            })
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
