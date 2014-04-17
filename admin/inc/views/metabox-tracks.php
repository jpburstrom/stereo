<?php 
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Metabox for playlist, showing all tracks
 */

if ( !defined( 'ABSPATH' ) )
    die( '-1' );
$meta = get_stereo_track_meta($post->ID);
?>

<div id="stereo_container">
        <ul>
            <li>Track number: <?php echo $post->menu_order ?></li>
            <li>Track host: <?php echo $meta["host"]?></li>
            <li>Track file id: <?php echo $meta["fileid"]?></li>
            <li>Link: <a href="<?php echo get_stereo_streaming_link() ?>"><?php echo get_stereo_streaming_link() ?></a></li>
        </ul>
</div>

<?php
    /*
    <table class="form-table">
    <tbody>
    </tr><td><label for="voider_gallery_subtitle">Subtitle</label>
    </td><td><input id="voider_gallery_subtitle" name="_gallery_subtitle" type="text" value="<?php echo $custom["_gallery_subtitle"][0]?>" />
    </td><tr><td><label for="voider_gallery_year">Year</label>
    </td><td><input id="voider_gallery_year" name="_gallery_year" type="text" value="<?php echo $custom["_gallery_year"][0]?>" />
    </td></tr>
    </tbody> </table>
*/
