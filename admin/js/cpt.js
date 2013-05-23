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
     add_sc_track = function(data) {
        var $item = add_track_gui(),
            url = data.stream_url + "?client_id=" + stereo_sc_id;
        $item.find(".stereo-track-name").val(data.title);
        $item.find(".stereo-track-host").val("sc");
        $item.find(".stereo-track-fileid").val(data.id);
        bindAudioEvents.apply($item.find(".stereo-preview").attr("src", url ));
        return $item;
     },
     bindAudioEvents = function() {
        $(this).on("loadeddata", function() {
            var min = Math.floor(this.duration/60);
            var sec = Math.floor(this.duration % 60);
            $(this).parent().append( "<span class='duration'>" + min + ":" + sec + "</span>").find(".stereo-play").show();

        });
        $(this).on("error", function() {
            $(this).parent().append( "<span class='error'>Error loading file</span");
        });
     },
     basename = function(path) {
         return path.split('/').reverse()[0];
     }
     ;

    jQuery("#stereo_tracks").sortable({
        //connectWith: '.metabox-holder',
        update: recountAll
    });

    $("#stereo_add_track").click(function(ev) {
        add_track_gui();
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
        add_sc_track($(this).children(":selected").data("stereo_tracks"));
    });

    $(".stereo-cancel").click(function(ev) {
        $(this).parent().hide(200);
        ev.preventDefault();
    });

    $(document).on("click", ".stereo-delete-track", function(ev) {
        var $track = $(this).parents(".stereo-track");
        var id = $track.find(".stereo-track-id").val();
        ev.preventDefault();
        $track.hide(200, function() { 
            (this).remove();
        
            recountAll.apply($("#stereo_tracks")[0]);
            $("<input type='hidden' name='stereo_delete_track[]'>").val(id).appendTo("#stereo_container");
        
        });
    });

    $(document).on("click", ".stereo-play", function(ev) {
        ev.preventDefault();
        $(this).parent().find("audio")[0].play();
        $(this).hide();
        $(this).siblings(".stereo-stop").show();
    }).on("click", ".stereo-stop", function(ev) {
        ev.preventDefault();
        $(this).parent().find("audio")[0].pause();
        $(this).hide();
        $(this).siblings(".stereo-play").show();
    });


    // Uploading files
    var file_frame;

    $(document).on('click', '.stereo-add-file', function( event ){

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery( this ).data( 'uploader_title' ),
            button: {
                text: jQuery( this ).data( 'uploader_button_text' )
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            console.log(attachment);

            // Do something with attachment.id and/or attachment.url here
        });

        // Finally, open the modal
        file_frame.open();
    });

    $('.stereo-preview').each( bindAudioEvents );

});
