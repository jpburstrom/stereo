(function($) {
    $.fn.yuckboxLink = function( options ) {  
        var settings = $.extend( {
            autoplay: false,
            idAttr: false,
            baseURL: ""
        }, options);

        return this.each(function() {
            var url = $(this).attr("href");
            if (url) {
                $(this).click(function(ev) {
                    var play = $(this).hasClass("play") || settings.autoplay; 
                    var title = $(this).text();
                    yuckbox.addSong( { url: url, title: title }, play);
                    ev.preventDefault();
                });
            } else { 
                this.find("a").yuckboxLink();
            }

        });

  };
})(jQuery)


$(document).ready(function() {
    $(".yuckbox-link").yuckboxLink();
});

