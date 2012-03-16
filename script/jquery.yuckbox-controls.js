/**
 * YuckBox Controls
 * Playback controller GUI
 *
 * Johannes Burström 2012
 */

(function($) {
    $.fn.yuckboxControls = function( options ) {  
        var settings = $.extend( {
            controlTemplate: "<span class='yuckbox-controls'> <button class='prev'/> <button class='stop'/> <button class='play'/> <button class='next'/> <span class='label'> <span class='artist'/> <span class='album'/> <span class='title'/> </span> <span class='progress'> <span class='loaded'/> <span class='played'/> </span> <span class='time'> <span class='min'/><span class='delim'/><span class='sec'/> </span> </span>",
            useThrottling: true,
            progressClass: ".progress",
            buildPlaylist: true
        }, options);

        function loadElement(el, play) {
            o = el.data("yuckboxSong");
            return yuckbox.addSong(o, play);
        };

        function playElement(el) {
            o = el.data("yuckboxSong");
            return yuckbox.play(o.id);
        };

        setPosition = function(e) {
            // called from slider control
            var oThis = self.getTheDamnTarget(e),
            x, oControl, oSound, nMsecOffset;
            if (!oThis) {
                return true;
            }
            oControl = oThis;
            while (!self.hasClass(oControl,'controls') && oControl.parentNode) {
                oControl = oControl.parentNode;
            }
            oSound = self.lastSound;
            x = parseInt(e.clientX,10);
            // play sound at this position
            nMsecOffset = Math.floor((x-self.getOffX(oControl)-4)/(oControl.offsetWidth)*self.getDurationEstimate(oSound));
            if (!isNaN(nMsecOffset)) {
                nMsecOffset = Math.min(nMsecOffset,oSound.duration);
            }
            if (!isNaN(nMsecOffset)) {
                oSound.setPosition(nMsecOffset);
            }
        };

        handleMouseDown = function(e) {
            // a sound link was clicked
            self.dragActive = true;
            yuckbox.pause();
            setPosition(e);
            $(document).on('mousemove', handleMouseMove);
            //self.addClass(self.lastSound._data.oControls,'dragging');
            e.preventDefault();
            //return self.stopEvent(e);
        };

        handleMouseMove = function(e) {
            /*
            if (isTouchDevice && e.touches) {
                e = e.touches[0];
            }
            */
            // set position accordingly
            if (self.dragActive) {
                if (settings.useThrottling) {
                    // be nice to CPU/externalInterface
                    var d = new Date();
                    if (d-self.dragExec>20) {
                        setPosition(e);
                    } else {
                        window.clearTimeout(self.dragTimer);
                        self.dragTimer = window.setTimeout(function(){setPosition(e);},20);
                    }
                    self.dragExec = d;
                } else {
                    // oh the hell with it
                    setPosition(e);
                }
            } else {
                stopDrag();
            }
            //e.stopPropagation = true;
            return false;
        };

        setPosition = function(e) {
            // called from slider control
            
            var oSound = yuckbox.songs[yuckbox.sIndex],
            oControl = self.find(settings.progressClass)[0],
            duration;
            
            duration = oSound.durationEstimate;
            
            x = parseInt(e.clientX,10);
            // play sound at this position
            nMsecOffset = Math.floor(( x - getOffX(oControl)-4)/(oControl.offsetWidth)*duration);
            if (!isNaN(nMsecOffset)) {
                nMsecOffset = Math.min(nMsecOffset,duration);
            }
            if (!isNaN(nMsecOffset)) {
                oSound.setPosition(nMsecOffset);
            }
        };
        getOffX = function(o) {
            // http://www.xs4all.nl/~ppk/js/findpos.html
            var curleft = 0;
            if (o.offsetParent) {
                while (o.offsetParent) {
                    curleft += o.offsetLeft;
                    o = o.offsetParent;
                }
            }
            else if (o.x) {
                curleft += o.x;
            }
            return curleft;
        };
        
        
        stopDrag = function(e) {
            if (self.dragActive) {
                yuckbox.songs[yuckbox.sIndex].resume(); //XXX
                $(document).off('mousemove', handleMouseMove);
                self.dragActive = false;
                e.preventDefault();
                return;
            }
        };
        

        var self = $(this);
        self.html(settings.controlTemplate)
            .find(".play").click(function() { yuckbox.togglePause() } ).end()
            .find(".stop").click(function() { yuckbox.stop() } ).end()
            .find(".prev").click(function() { yuckbox.play(); yuckbox.prev() } ).end()
            .find(".next").click(function() { yuckbox.play(); yuckbox.next() } ).end()
            .find(settings.progressClass).on("mousedown", handleMouseDown );
        $(document).on("mouseup", stopDrag );

        var played = self.find(".played"),
            loaded = self.find(".loaded"),
            preload = false;


        $(document).on("play.yuckbox", function(ev, snd) {
                self.addClass("preload");
                preload = true;
                loaded.css("width", 0);
                self.addClass("playing").removeClass("paused");
            }).on("currentchanged.yuckbox", function(ev, snd) {
                self.find(".artist").html(snd.options.artist);
                self.find(".album").html(snd.options.album);
                self.find(".title").html(snd.options.title);
                link = (snd.options.info_url) ? $("<a href='"+ snd.options.info_url +"'/>") : $();

                self.find(".label")
                    .not(":has(a)").wrap(link).end()
                    .children(".artist,.album").not(":empty").not(":first").prepend(" – ");

                $(document).trigger("newlabel.yuckbox", snd);
            }).on("pause.yuckbox", function(ev, snd) {
                self.addClass("paused").removeClass("playing")
            }).on("stop.yuckbox", function(ev, snd) {
                self.removeClass("paused playing")
                played.css("width", 0);
            }).on("finish.yuckbox", function(ev, snd, really) {
                if (really) {
                    self.removeClass("paused playing")
                    played.css("width", 0);
                }
            }).on("whileplaying.yuckbox", function(ev, snd, amt) {
                if (isNaN(amt)) return;
                played.css("width", (amt * 100) + "%");
                if (preload) { 
                    self.removeClass("preload");
                    preload = false;
                }
            }).on("whileloading.yuckbox", function(ev, snd, amt) {
                loaded.css("width", (amt * 100) + "%");

            }) ;

        return this;

  };
})(jQuery)


