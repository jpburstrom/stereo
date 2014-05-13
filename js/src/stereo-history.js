/**
 * Stereo - a javascript audio player
 *
 * Johannes Burström 2013
 * Stereo may be freely distributed under the MIT license.
 */

 //Bug: anchor links don't work on index (root) page

/*global window: false, Backbone:false, _:false, console:false, jQuery:false*/

(function(w, b, _, $){

    "use strict";

    var App;

    w.Stereo = w.Stereo || {};
    App = w.Stereo;

    //Overriding the navigate method
    b.History.prototype.navigate = function(fragment, options) {
        

        if (!b.History.started) return false;
        if (!options || options === true) options = {trigger: !!options};


        var url = this.root + (fragment = this.getFragment(fragment || ''));

        // Don't strip the fragment of the query and hash for matching.
        if (this.fragment === fragment) return;
        this.fragment = fragment;

        // Don't include a trailing slash on the root.
        if (fragment === '' && url !== '/') url = url.slice(0, -1);

        // If pushState is available, we use it to set the fragment as a real URL.
        if (this._hasPushState) {
            this.history[options.replace ? 'replaceState' : 'pushState']({}, w.document.title, url);
            //Else redirect
        } else {
            return this.location.assign(url);
        }
        if (options.trigger) return this.loadUrl(fragment);
    };

    App.HistoryRouter = b.Router.extend({
        initialize: function () {
            var that = this;
            $(function () {

                //TODO: if App.options.history.enable
                b.history.start({
                    pushState: true, 
                    hashChange:false, 
                    //We don't want the initial routing
                    silent: true,
                    //Take root from urlRoot
                    root: App.options.history.urlRoot.replace(/^.*\/\/[^\/]*/, '')
                });
                //We need the hash here -- b seems to drop it from history otherwise
                that.navigate(w.location.pathname + w.location.hash);

            });
        },
        routes: {
            '*page' : 'newPage'
        },

        newPage: function(page) {
            //Hack
            App.e.trigger("history:load-start", w.location.pathname + w.location.hash);

        }
    });

    (function() {
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
        App.View.History = b.View.extend({
            initialize: function() {
                var $scrollRoot = $('html,body');
                var elements = App.options.links.elements ? [App.options.links.elements] : [];

                elements.push('[target="_blank"]');

                if (App.options.history.ignore) {
                    elements.push(App.options.history.ignore);
                }



                this.listenTo(App.e, 'history:load-start', this.onNewPage);
                this.listenTo(App.e, 'history:load-finish', function() {
                    App.e.trigger("history:scroll");
                });
                this.listenTo(App.e, 'history:scroll', function() {
                    var offset, 
                        hash = w.location.hash,
                        target = $(hash);
                    //If no elem found, search for elem with name attribute
                    target = target.length ? target : $('[name=' + hash.slice(1) +']');
                    //Calc offset
                    offset = target.length ? target.offset().top : 0;
                    $scrollRoot.animate({
                        scrollTop: offset
                    }, App.options.history.scrollTime, function() {
                        if (hash !== '') {
                            w.location.hash = hash;
                        }
                    });
                });

                this.$el.on('click', 'a:nomedia:internalLink:not(' + elements.join(',') + ')', this.navigateLink)
                    // Add a small element to use for spinner
                    .append('<div class="stereo-spinner"/>');
            },

            //called when links are clicked
            navigateLink: function(ev) {
                var href = this.href;

                //If only hash is changed (anchor links),
                //trigger navigate (for history) and scroll to new position
                if (this.hash !== '' && w.location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && w.location.hostname == this.hostname) {
                    App.historyRouter.navigate(href.replace(App.options.history.urlRoot, ''));
                    App.e.trigger("history:scroll");

                //If the link is not internal, check if player is playing and we're not right-clicking etc
                } else if ( !( ev.which == 2 || ev.metaKey || ev.shiftKey || false === App.player.isPlaying() ) ) { 
                    //If player is playing and url points to different page,
                    //navigate to page (for history) and trigger the new page actions
                    App.historyRouter.navigate(href.replace(App.options.history.urlRoot, ''));
                    App.historyRouter.newPage(href.replace(App.options.history.urlRoot, ''));
                } else {
                    //Else return, continue propagation etc
                    return;
                }
                ev.stopPropagation();
                ev.preventDefault();

            },

            onNewPage: function(url) {
                var $el = this.$el;
                $el.addClass("stereo-loading");
                if (!url) return;
                $.ajax({
                    url: url,
                    data: {
                        //Send a variable
                        stereo_ajax : true
                    },
                    dataType: "html",

                    success: function(data, textStatus, response){
                        // Prepare
                        
                        var $data, $dataBody, contentHtml, $scripts;
                        //Check that the response is a html file, otherwise redirect
                        if (response.getResponseHeader('Content-Type').indexOf('html') == -1) {
                            w.location = url;
                            return;
                        }

                        //Get the treated data
                        $data = $($.trim(documentHtml(data)));

                        //Get the data body. TODO: this could be replaced for the container class maybe?
                        $dataBody = $data.find('[data-history-body]');

                        //Replace container classes
                        $el.removeClass();
                        if (App.options.history.container == "body") {
                           $el.addClass($dataBody.attr("class")); 
                        } else {
                            $el.addClass($dataBody.find(App.options.history.container));
                        }

                        // Update the content on each of the elements
                        $(App.options.history.elements).stop(true,true).each(function() {
                            $(this).each(function() {
                                var $self = $(this);
                                var $other = $dataBody.find("#" + $self.attr("id"));
                                $self.html($other.html()).removeClass().addClass($other.attr("class"));
                            });
                        });
                        
                        //Replace document title
                        w.document.title = 
                            w.document.title = $data.find('.data-history-title:first').text();
                        try {
                            w.document.getElementsByTagName('title')[0].innerHTML = w.document.title.replace('<','&lt;').replace('>','&gt;').replace(' & ',' &amp; ');
                        }
                        catch ( Exception ) { }
                    
                        //A load-data event for your convenience
                        App.e.trigger("history:load-data", $data);

                        //Stop loading
                        $el.removeClass("stereo-loading");

                        //Trigger the finish event, success is true
                        App.e.trigger("history:load-finish", true);

                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        //Trigger the finish event, success is false
                        App.e.trigger("history:load-finish", false);

                        w.document.location.href = url;
                        
                        return;
                    }
                }); // end ajax
            }

                
        });
    })();

    App.e.on("init", function(options) {

        App.options.history = _.extend({
            //Full URL to index page
            urlRoot: 'http://jb.dev',
            
            //Everything in container is affected by history
            container: 'body',

            //All elements get replaced on ajax page load
            elements: '#content',

            //Links to ignore, to load normally
            ignore: '',

            scrollTime: 0,
            
            enable: false

        }, options.history);

        if (options.history.enable && options.history.elements) {

            App.historyRouter = new App.HistoryRouter();
            App.views.history = [];
            $(options.history.container).each(function() {
                App.views.history.push( new App.View.History({ 
                    el: this
                }));

            });


        }

    });

    // Creating custom :external selector
    $.expr[':'].internalLink = function(obj){
        //True if internal link
        var int = (obj.nodeName == "A") && (obj.hostname == w.location.hostname) && !obj.href.match(/^mailto\:/);
        return int;
    };
    // Creating custom :nomedia selector, works for html and php files, add your own extensions if you want
    $.expr[':'].nomedia = function(obj){
        //Select links with no extension,
        //Or links with html/php extensions
        return (obj.nodeName == "A") && (! obj.href.match(/\.([a-z]{2,4}$)/i) || obj.href.match(/\.([psx]?htm[l]?|php[34]?)/gi)) ;
    };
    

})(window, Backbone, _, jQuery);

