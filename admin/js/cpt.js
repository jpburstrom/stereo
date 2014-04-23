jQuery(document).ready(function($) {
     var recount = function(obj, i) {
          $(obj).find(".stereo-track-number").text(i);
          $(obj).find(".stereo-track-number-input").val(i);
     },
     recountAll = function() {
          var i = 1;
         $(this).find(".stereo-track").each(function() {
             recount(this, i);
             i++;
         });
     },
     add_track_gui = function() {
        var count = $("#stereo_tracks").find(".stereo-track").size() + 1,
        $item = $("#stereo_tracks").append($("#stereo_track_template").html()).children().last();
        recount($item, count);
        return $item;
     },
     hide_delete_if_empty = function() {
        if ( $('#stereo_tracks').children().length > 0 ) {
            $("#stereo_delete_tracks").show();
        } else {
            $("#stereo_delete_tracks").hide();
        }
     },
     add_icon = function(host, $item) {
         var html;
         if (host == 'wp') {
            html =  "<span title='Hosted locally' class='host-icon icon-wordpress'></span>";
         } else if (host == 'sc') {
            html = "<span title='Hosted by SoundCloud' class='host-icon icon-soundcloud'></span>";
         }
         $item.find(".metadata").find('.host-icon').remove().end().prepend(html);
     },
     add_track = function(data) {
        var $item = add_track_gui();
        replace_track($item, data);
        bindAudioEvents.apply($item.find(".stereo-preview").attr("src", data.url ));
        $("#stereo_delete_tracks").show();
        return $item;
     },
     replace_track = function($item, data) {
        if (data.title) {
            $item.find(".stereo-track-name").val(data.title);
        }
        $item.find(".stereo-track-host").val(data.host);
        $item.find(".stereo-track-fileid").val(data.id);
        if (data.host) {
            $item.removeClass("nofile");
            add_icon(data.host, $item);
        } else {
            $item.addClass("nofile");
        }
     },
     delete_track = function($track) {
        var id = $track.find(".stereo-track-id").val();
        $track.hide(200, function() { 
            (this).remove();
        
            recountAll.apply($("#stereo_tracks")[0]);
            $("<input type='hidden' name='stereo_delete_track[]'>").val(id).appendTo("#stereo_container");
            hide_delete_if_empty();
        
        });
     },
    add_sc_track = function(data) {
         data.url = data.stream_url + "?client_id=" + stereo_sc_id;
         data.host = "sc";
         return add_track(data);
     },
     replace_sc_track = function($item, data) {
         data.url = data.stream_url + "?client_id=" + stereo_sc_id;
         data.host = "sc";
         replace_track($item, data);
     },
     bindAudioEvents = function() {
        $(this).on("loadeddata", function() {
            var min = Math.floor(this.duration/60);
            var sec = ("0" + Math.floor(this.duration % 60)).slice(-2);
            $(this).parent().append( "<span class='duration'>" + min + ":" + sec + "</span>").addClass('active').removeClass(".loading");

        });
        $(this).on("error", function() {
            $(this).parent().append( "<span class='error'>Error loading file</span");
        });
     },
     basename = function(path) {
         return path.split('/').reverse()[0];
     }
     ;

    $("#stereo_tracks").sortable({
        //connectWith: '.metabox-holder',
        update: recountAll,
        start: function () {
            $(".stereo-player.playing").each(function() { $(this).removeClass("playing").find('audio')[0].pause(); } );
        }
    });

    $("#stereo_add_track").click(function(ev) {
        add_track_gui().addClass('nofile');
        ev.preventDefault();
    });

    $("#stereo_soundcloud_import").click(function(ev) {
        $("#stereo_soundcloud_import_container").toggle(200);
        ev.preventDefault();
    });

    $("#stereo_sc_sets").change(function(ev) {
        $.each($(this).children(":selected").data("stereo_tracks"), function() {
            add_sc_track(this);
        });
    });
    
    $("#stereo_sc_tracks").change(function() {
        var data = $(this).children(":selected").data("stereo_tracks");
        add_sc_track(data);
    });

    $(".stereo-cancel").click(function(ev) {
        $(this).parent().hide(200);
        ev.preventDefault();
    });
    
    $("#stereo_delete_tracks").on("click", function(ev) {
        var r=confirm("Are you sure? Press OK to delete all tracks.");
        ev.preventDefault();
        if (r === true) {
            $(".stereo-track").each(function() {
                delete_track($(this));
            });
        }

    });

    $(document).on("click", ".stereo-delete-track", function(ev) {
        ev.preventDefault();
        delete_track($(this).parents(".stereo-track"));
    });

    $(document).on("click", ".stereo-play", function(ev) {
        ev.preventDefault();
        $(".stereo-player.playing").each(function() { $(this).removeClass("playing").find('audio')[0].pause(); } );
        $(this).parent().addClass("playing").find("audio")[0].play();
    }).on("click", ".stereo-stop", function(ev) {
        ev.preventDefault();
        $(this).parent().removeClass("playing").find("audio")[0].pause();
    });


    // Uploading files
    var file_frame;

    $("#stereo_local_import").on('click', function( event ){

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery( this ).data( 'uploader_title' ),
            library: {
                type: "audio"
            },
            button: {
                text: jQuery( this ).data( 'uploader_button_text' )
            },
            multiple: true,  // Set to true to allow multiple files to be selected
            frame: 'select'
        });

         // When an image is selected, run a callback.
         file_frame.on( 'select', function() {
             var selection = file_frame.state().get('selection');
             selection.map( function( attachment ) {
                 attachment = attachment.toJSON();
                 attachment.host = 'wp';
                 add_track(attachment);
             });
         });
        //Add class so we can hide images
        // Finally, open the modal
        file_frame.open();
    });
    
    $(document).on('click', '.stereo-track-actions-label', function(event) {
        $p = $(this).closest(".stereo-track").toggleClass("more");
    });

    $(document).on("click", ".stereo-replace-wp", function( event ){

        var file_frame;
        var $self = $(this).closest('.stereo-track');

        event.preventDefault();

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery( this ).data( 'uploader_title' ),
            library: {
                type: "audio"
            },
            button: {
                text: jQuery( this ).data( 'uploader_button_text' )
            },
            multiple: false,  // Set to true to allow multiple files to be selected
            frame: 'select'
        });

         // When an image is selected, run a callback.
         file_frame.on( 'select', function() {
             var selection = file_frame.state().get('selection');
             selection.map( function( attachment ) {
                 attachment = attachment.toJSON();
                 attachment.host = 'wp';
                 replace_track($self, attachment);
                 setTimeout(function(){$self.removeClass("more");}, 200);
             });
         });
        //Add class so we can hide images
        // Finally, open the modal
        file_frame.open();
        

    }).on("click", ".stereo-track-detach", function( event ){

        event.preventDefault();
        replace_track($(this).closest('.stereo-track').removeClass("more"), {
            host: "",
            id: ""
        });

    }).on("change", ".stereo-replace-sc", function( event) {
        var data = $(this).children(":selected").data("stereo_tracks");
        var $item = $(this).closest(".stereo-track").removeClass("more");
        $(this).val("");
        replace_sc_track($item, data);
    });

    $('.stereo-preview').each( bindAudioEvents );

    hide_delete_if_empty();


});
