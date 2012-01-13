(function($) {
    $.fn.yuckboxControls = function( options ) {  
        var settings = $.extend( {
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
            }).on("pause.yuckbox", function(ev, snd) {
            }).on("stop.yuckbox finish.yuckbox", function(ev, snd) {
            });

        return this;

  };
})(jQuery)

