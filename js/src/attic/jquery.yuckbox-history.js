/**
 * YuckBox History
 * Using History.js to ajax-load pages when music is playing
 *
 * Based on this gist: https://gist.github.com/854622
 * Johannes Burström 2012
 */

(function($){

    var inited = false;
	
    $.fn.yuckboxHistory = $.fn.yuckboxHistory || function( options ) {  
        //settings object
        if ( !History.enabled) {
            return this;
        }

        var settings = $.extend( {
            menu : '#primary',
            menuActiveClass : {
                item : 'current-menu-item',
                parent : 'current-menu-parent',
                ancestor : 'current-menu-ancestor'
            },
            menuActiveSelector : {
                item : '.current-menu-item',
                parent : '.current-menu-parent',
                ancestor : '.current-menu-ancestor'
            },
            menuChildren : 'li',
            body : document.body,
            rootUrl : History.getRootUrl(),
            scrollOptions : {
                duration: 800,
                easing:'swing'
            },
            content : this,
            noAjaxLink : ".no-ajax",
            loader: null
        }, options);

        settings.menuActiveClasses = settings.menuActiveClass.item + " " + settings.menuActiveClass.parent + " " + settings.menuActiveClass.ancestor;

        if ( $(settings.content).length === 0 ) {
            settings.content = settings.body;
        }

        // Internal Helper
        $.expr[':'].internal = function(obj, index, meta, stack){
            var $this = $(obj), url = $this.attr('href')||'', isInternalLink;
            isInternalLink = url.substring(0,settings.rootUrl.length) === settings.rootUrl || url.indexOf(':') === -1;
            return isInternalLink;
        };


        var documentHtml = function(html){
            // Prepare
            var result = String(html)
                .replace(/<\!DOCTYPE[^>]*>/i, '')
                .replace(/<(html|head|body|title|meta|script)([\s\>])/gi,'<div data-history-$1="true"$2')
                .replace(/<\/(html|head|body|title|meta|script)\>/gi,'</div>')
            ;
            
            // Return
            return result;
        };
            
        $(document).on("play.yuckbox", function() {
            $(settings.body).on("click.yuckboxHistory", "a:internal:not("+ settings.noAjaxLink +")", function(event) {
                if ( event.which == 2 || event.metaKey ) { return true; }
                
                // Ajaxify this link
                var $this = $(this);
                History.pushState(null,$this.attr("title") || null, $this.attr("href"));

                event.preventDefault();
                return false;
            }); 
        }).on("stop.yuckbox pause.yuckbox", function(ev) {
            $(settings.body).off("click.yuckboxHistory");
        }).on("load.yuckbox", function(ev, snd, success) {
            if (!success)
                $(settings.body).off("click.yuckboxHistory");
        }).on("finish.yuckbox", function(ev, snd, really) {
            if (really) {
                $(settings.body).off("click.yuckboxHistory");
            }
        });
            
            // Hook into State Changes
        $(window).bind('statechange',function(){
            // Prepare Variables
            var
                State = History.getState(),
                url = State.url,
                relativeUrl = url.replace(settings.rootUrl,'');

            // Set Loading
            $(settings.body).addClass('loading');
            //$(document).scrollTop(0);

            // Start Fade Out
            // Animating to opacity to 0 still keeps the element's height intact
            // Which prevents that annoying pop bang issue when loading in new content
            $(settings.content).animate({opacity:0},400);
            
            // Ajax Request the Traditional Page
            $.ajax({
                url: url,
                data: {
                    yuckbox_ajax : true
                },
                dataType: "html",
                success: function(data, textStatus, jqXHR){
                    // Prepare
                    var $data = $(documentHtml(data)),
                        $dataBody = $data.find('[data-history-body]'),
                        contentHtml, $scripts;
                    
                    // TODO: Fetch the scripts
                    $scripts = $dataBody.find('[data-history-script]');
                    if ( $scripts.length ) {
                        $scripts.detach();
                    }

                    // Fetch the content
                    /*
                    if ( !$data.html() ) {
                        document.location.href = url;
                        return false;
                    }
                    */

                    // Update the content
                    $(settings.content).stop(true,true).each(function() {
                        $(this).each(function() {
                            self = $(this);
                            other = $dataBody.find("#" + self.attr("id"));
                            self.html(other.html()).removeClass().addClass(other.attr("class"));
                        });
                    }).css('opacity',100).show(); /* you could fade in here if you'd like */

                    // Update the menu
                    $(settings.menu).find(settings.menuChildren)
                        .removeClass(settings.menuActiveClasses)
                        .has('a[href^="'+relativeUrl+'"],a[href^="/'+relativeUrl+'"],a[href^="'+url+'"]')
                        .last().addClass(settings.menuActiveClass.item) 
                            .parentsUntil(settings.menu).filter("li").addClass(settings.menuActiveClass.ancestor)
                                .first().addClass(settings.menuActiveClass.parent); 
                    // Update the title
                    document.title = $data.find('.data-history-title:first').text();
                    try {
                        document.getElementsByTagName('title')[0].innerHTML = document.title.replace('<','&lt;').replace('>','&gt;').replace(' & ',' &amp; ');
                    }
                    catch ( Exception ) { }
                    
                    // Add the scripts
                    $scripts.each(function(){
                        var $script = $(this), scriptText = $script.text(), scriptNode = document.createElement('script');
                        scriptNode.appendChild(document.createTextNode(scriptText));
                        $(settings.content).get(0).appendChild(scriptNode);
                    });

                    // Complete the change
                    $body = $(settings.body);
                    if ( $body.ScrollTo||false ) { $body.ScrollTo(scrollOptions); } /* http://balupton.com/projects/jquery-scrollto */
                    $body.removeClass().addClass($dataBody.attr("class"));
    
                    // Inform Google Analytics of the change
                    if ( typeof window.pageTracker !== 'undefined' ) {
                        window.pageTracker._trackPageview(relativeUrl);
                    }

                    // Inform ReInvigorate of a state change
                    if ( typeof window.reinvigorate !== 'undefined' && typeof window.reinvigorate.ajax_track !== 'undefined' ) {
                        reinvigorate.ajax_track(url);
                        // ^ we use the full url here as that is what reinvigorate supports
                    }

                    settings.loader && settings.loader();
                    $(document).trigger("newpageload.yuckbox");

                },
                error: function(jqXHR, textStatus, errorThrown){
                    document.location.href = url;
                    return false;
                }
            }); // end ajax

        }); // end onStateChange

        settings.loader && settings.loader();
        return this;

    }

})(jQuery); // end closure
