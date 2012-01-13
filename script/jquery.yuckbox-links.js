(function($) {
    $.fn.yuckboxLinks = function( options ) {  
        var settings = $.extend( {
            idAttr: false,
            baseURI: false,
            loadOnLoad: false,
            loadOnClick: false,
            playOnClick: true
        }, options);

        function loadElement(el, play) {
            o = el.data("yuckboxSong");
            return yuckbox.addSong(o, play);
        };

        function playElement(el) {
            o = el.data("yuckboxSong");
            return yuckbox.play(o.id);
        };

        $(document).on("play.yuckbox", function(ev, snd) {
            $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("paused").addClass("playing").log();
            }).on("pause.yuckbox", function(ev, snd) {
                $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("playing").addClass("paused");
            }).on("stop.yuckbox finish.yuckbox", function(ev, snd) {
                $('[data-yuckbox-id="' + snd.options.id + '"]').removeClass("playing paused");
            });

        this.on("click", "[data-yuckbox-song]", function(ev) {
            var play = $(this).hasClass("play") || settings.playOnClick; 
            if (settings.loadOnClick) {
                loadElement($(this), play);
            } else if (settings.playOnClick) {
                yuckbox.play($(this).data("yuckboxSong").id);
            }
            ev.preventDefault();
            
        });
        return this.find("[data-yuckbox-song]").each(function() {
            $(this).attr("data-yuckbox-id", $(this).data("yuckboxSong").id);
            if (settings.loadOnLoad)
                loadElement($(this), false);
            $(this).addClass("yuckbox-playable");

        });

  };
})(jQuery)


//testing
$(document).ready(function() {
    $("p").yuckboxLinks();
});

