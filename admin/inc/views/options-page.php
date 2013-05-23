<?php 
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Admin template include
 */

if ( !defined( 'ABSPATH' ) )
    die( '-1' );
?>

    <div class="wrap">
        <div class="icon32" id="icon-options-general"></div>
        <h2> <?php _e( 'Stereo Options', 'stereo' ) ?> </h2>
        <form class="stereo-donate" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="QQTQLT2TZ59BA">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
		
		<form action="options.php" method="post">
	
            <?php settings_fields( 'stereo_options' ); ?>
            <div class="ui-tabs">
                <ul class="ui-tabs-nav">
            
                    <?php foreach ( $this->sections as $section_slug => $section ) : ?>
                    <li><a href="#<?php echo $section_slug ?>"><?php echo $section ?></a></li>
                    <?php endforeach; ?>
            
                </ul>
            <?php do_settings_sections( $_GET['page'] ); ?>
            
            </div>
                <p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php  _e( 'Save Changes', 'stereo' ) ?>" /></p>
		
        </form>
        
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var sections = [];
			
            <?php foreach ( $this->sections as $section_slug => $section )
				echo "sections['$section'] = '$section_slug';";
            ?>
			
			var wrapped = $(".wrap h3").wrap("<div class=\"ui-tabs-panel\">")
			wrapped.each(function() {
				$(this).parent().append($(this).parent().nextUntil("div.ui-tabs-panel"));
			});
			$(".ui-tabs-panel").each(function(index) {
				$(this).attr("id", sections[$(this).children("h3").text()]);
				if (index > 0)
					$(this).addClass("ui-tabs-hide");
			});
			$(".ui-tabs").tabs({
				fx: { opacity: "toggle", duration: "fast" }
			}).bind("tabsshow", function(event, ui) { 
                window.location.hash = ui.tab.hash;
            })
			
			$("input[type=text], textarea").each(function() {
				if ($(this).val() == $(this).attr("placeholder") || $(this).val() == "")
					$(this).css("color", "#999");
			});
			
			$("input[type=text], textarea").focus(function() {
				if ($(this).val() == $(this).attr("placeholder") || $(this).val() == "") {
					$(this).val("");
					$(this).css("color", "#000");
				}
			}).blur(function() {
				if ($(this).val() == "" || $(this).val() == $(this).attr("placeholder")) {
					$(this).val($(this).attr("placeholder"));
					$(this).css("color", "#999");
				}
			});
			
			$(".wrap h3, .wrap table").show();

			
			// This will make the "warning" checkbox class really stand out when checked.
			// I use it here for the Reset checkbox.
			$(".warning").change(function() {
				if ($(this).is(":checked"))
					$(this).parent().css("background", "#c00").css("color", "#fff").css("fontWeight", "bold");
				else
					$(this).parent().css("background", "none").css("color", "inherit").css("fontWeight", "normal");
			});
			
			// Browser compatibility
			if ($.browser.mozilla) 
			         $("form").attr("autocomplete", "off");

            $("#stereo_update_tracks").on("click", function(ev) {
                var $msg, $self = $(this);
                ev.preventDefault();
                if ($self.attr("disabled") == "disabled")
                    return;
                $self.attr("disabled", "disabled");
                $msg = $(" <span class='msg'></span>").appendTo($self.parent());
                $msg.text(" Updating...");
                window.onbeforeunload = function() { return "Please wait until tracks are updated"; }
                $.post(ajaxurl, { action: 'stereo_update_tracks' }, function(data) {
                    $self.siblings(".msg").text(" " + data);
                    $self.siblings('.msg').fadeOut(3000, function() {
                        $(this).remove();
                        $self.removeAttr("disabled");
                    });
                    window.onbeforeunload = null;
                });
            });
		});
	</script>
</div>
