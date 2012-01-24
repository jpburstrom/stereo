(function($) {
    $.fn.yuckboxLinks = $.fn.yuckboxLinks || function( options ) {  
        var settings = $.extend( {
            baseURI: false,
            loadOnLoad: false,
            loadOnClick: false,
            playOnClick: true,
            containerElement: "body"
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

        function newPage() {
            $(settings.containerElement).find("[data-yuckbox-song]").each(function() {
                $(this).attr("data-yuckbox-id", $(this).data("yuckboxSong").id);
                if (settings.loadOnLoad)
                    loadElement($(this), false);
                $(this).addClass("yuckbox-playable");
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
            var play = $(this).hasClass("play") || settings.playOnClick; 
            if (settings.loadOnClick) {
                loadElement($(this), play);
            } else if (settings.playOnClick) {
                yuckbox.play($(this).data("yuckboxSong").id);
            }
            ev.preventDefault();
            
        })
        .on("newpageload.yuckbox", function(ev) {
            newPage();
        });

        newPage();
        return this;

  };
})(jQuery)
