(function($) {
    $.fn.yuckboxControls = function( options ) {  
        var settings = $.extend( {
            controlTemplate: "<span class='yuckbox-controls'> <button class='prev'/> <button class='stop'/> <button class='play'/> <button class='next'/> <span class='label'> <span class='artist'/> <span class='album'/> <span class='title'/> </span> <span class='progress'> <span class='loaded'/> <span class='played'/> </span> <span class='time'> <span class='min'/><span class='delim'/><span class='sec'/> </span> </span>",
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

        var self = $(this);
        self.html(settings.controlTemplate)
            .find(".play").click(function() { yuckbox.togglePause() } ).end()
            .find(".stop").click(function() { yuckbox.stop() } ).end()
            .find(".prev").click(function() { yuckbox.prev() } ).end()
            .find(".next").click(function() { yuckbox.next() } ).end()
        ;

        var played = self.find(".played");
        var loaded = self.find(".loaded");


        $(document).on("play.yuckbox load.yuckbox", function(ev, snd) {
                if (ev.type == "play")
                    self.addClass("playing").removeClass("paused")
                self.find(".artist").html(  snd.options.artist);
                self.find(".album").html(snd.options.album);
                self.find(".title").html(snd.options.title);
            }).on("pause.yuckbox", function(ev, snd) {
                self.addClass("paused").removeClass("playing")
            }).on("stop.yuckbox finish.yuckbox", function(ev, snd) {
                self.removeClass("paused playing")
                played.css("width", 0);
            }).on("whileplaying.yuckbox", function(ev, snd, amt) {
                played.css("width", amt * 100);
            }).on("whileloading.yuckbox", function(ev, snd, amt) {
                loaded.css("width", amt * 100);
            }) ;

        return this;

  };
})(jQuery)


