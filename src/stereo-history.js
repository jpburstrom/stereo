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
                    //Take root from urlRoot
                    root: App.options.history.urlRoot.replace(/^.*\/\/[^\/]*/, '')
                });
            });
        },
        routes: {
            '*page' : 'newPage'
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
        //TODO: maybe stupid to have this as a view?
        App.View.History = b.View.extend({
            initialize: function() {
                var elements = App.options.links.elements ? [App.options.links.elements] : [];
                elements.push('[target="_blank"]');

                if (App.options.history.ignore) {
                    elements.push(App.options.history.ignore);
                }

                App.e.on('load-start', this.onNewPage);


                this.$el.on('click', 'a:nomedia:internalLink:not(' + elements.join(',') + ')', this.navigateLink)
                    // Add a small element to use for spinner
                    .append('<div class="stereo-spinner"/>');
            },

            navigateLink: function(ev) {
                var href = ev.currentTarget.href;
                console.log("navigateLink");
                ev.stopPropagation();
                ev.preventDefault();
                if ( ev.which == 2 || ev.metaKey || ev.shiftKey || true === App.player.isPlaying() ) { 
                    w.location = href;
                    return false; 
                }

                $(App.options.history.container).addClass("stereo-loading");
                App.historyRouter.navigate(href.replace(App.options.history.urlRoot, ''));
                App.e.trigger("load-start", href);

            },
            onNewPage: function(url) {
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
                        console.log(response.getResponseHeader('Content-Type'));
                        if (response.getResponseHeader('Content-Type').indexOf('html') == -1) {
                            w.location = url;
                            return false;
                        }

                        console.log("Data loading...");
                        //Get the treated data
                        $data = $($.trim(documentHtml(data)));

                        $dataBody = $data.find('[data-history-body]');


                        // Update the content
                        $(App.options.history.elements).stop(true,true).each(function() {
                            $(this).each(function() {
                                var $self = $(this);
                                var $other = $dataBody.find("#" + $self.attr("id"));
                                $self.html($other.html()).removeClass().addClass($other.attr("class"));
                            });
                        });
                        
                        //Trigger the finish event, success is true
                        App.e.trigger("history:load-data", $data);


                        w.document.title = 
                            w.document.title = $data.find('.data-history-title:first').text();
                        try {
                            w.document.getElementsByTagName('title')[0].innerHTML = w.document.title.replace('<','&lt;').replace('>','&gt;').replace(' & ',' &amp; ');
                        }
                        catch ( Exception ) { }
                    
                        $(App.options.history.container).removeClass("stereo-loading");

                        //Trigger the finish event, success is true
                        App.e.trigger("history:load-finish", true);

                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        console.log("ERROER");
                        //document.location.href = url;
                        
                        //Trigger the finish event, success is false
                        App.e.trigger("history:load-finish", false);
                        
                        return false;
                    }
                }); // end ajax
            }

                
        });
    })();

    if (!App.options) App.options = {}; 

    _.extend(App.options, {
        history: {
            //Full URL to index page
            urlRoot: 'http://jb.dev',
            
            //Everything in container is affected by history
            container: 'body',

            //All elements get replaced on ajax page load
            elements: '#content',

            //Links to ignore, to load normally
            ignore: '',

            enable: false


        }
    });

    App.e.on("init", function(options) {

        if (options.history && options.history.elements) {

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

