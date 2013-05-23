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

<div id="stereo_container">
    <?php $this->metabox_toolbar() ?>
    <input type="hidden" id="stereo_track_count" name="stereo_track_count">
    <?php if (true === STEREO_SC_SRC): ?>
    <div class="hide-if-js" id="stereo_soundcloud_import_container">
        <h4><?php _e("Import from SoundCloud") ?></h4>
        <label for="stereo_sc_sets">Sets</label>
        <select id="stereo_sc_sets">
            <option></option>
            <?php foreach (stereo_sc()->get_sets() as $id => $set): ?>
                <option value="<?php echo $id ?>" data-stereo_tracks='<?php echo $set[1]?>'><?php echo $set[0] ?> </option>
            <?php endforeach; ?>
        </select>
        <label for="stereo_sc_tracks">Tracks</label>
        <select id="stereo_sc_tracks">
            <option></option>
            <?php foreach (stereo_sc()->get_tracks() as $id => $set): ?>
                <option value="<?php echo $id ?>" data-stereo_tracks='<?php echo $set[1]?>'><?php echo $set[0] ?> </option>
            <?php endforeach; ?>
        </select>
        <a href="#" class="stereo-cancel">Cancel</a>
    </div>
    <?php endif; ?>
    <script id="stereo_track_template" type="text/html">
        <li class="stereo-track postarea">
            <span class="handle"> </span>
            <span class="stereo-track-number"></span><input class="stereo-track-number-input" name="stereo_track_number[]" type="hidden" value=""/>
            <input type="text" placeholder="Track name" class="stereo-track-name" name="stereo_track_name[]"/>
            <input type="hidden" class="stereo-track-host" name="stereo_track_host[]" />
            <input type="hidden" class="stereo-track-fileid" name="stereo_track_fileid[]"/>
            <ul class="stereo-metadata"> 
                <li class="metadata">
                    <span class="stereo-player">
                        <audio preload="none" class="stereo-preview"></audio>
                        <a href="#" class="stereo-play icon-play-1" title="Play"></a>
                        <a href="#" class="stereo-stop icon-pause-1" title="Stop"></a>
                    </span>
                </li>
                <li class="actions">
                    <span class="submitbox"><a class="stereo-delete-track submitdelete" href="#">Delete track</a></span>
    <?php //<a class="stereo-replace-file" href="#">Replace file</a>?>
                </li>
            </ul>
            <span class="handle right"> </span>
        </li>
    </script>
    <ul id="stereo_tracks">
    <?php while ( $connected->have_posts() ) : $connected->the_post(); $meta = get_stereo_track_meta($post->ID); ?>
        <li class="stereo-track postarea <?php if (!$meta['fileid']) echo 'nofile'?> ">
            <span class="handle"> </span>
            <span class="stereo-track-number"><?php echo $post->menu_order ?> </span><input class="stereo-track-number-input" name="stereo_track_number[]" type="hidden" value="<?php echo $post->menu_order ?>"/>
            <input type="text" placeholder="Track name" value="<?php the_title(); ?>" class="stereo-track-name" name="stereo_track_name[]"/>
            <input type="hidden" class="stereo-track-id" value="<?php the_ID(); ?>" name="stereo_track_ID[]"/>
            <input type="hidden" class="stereo-track-host" name="stereo_track_host[]" value="<?php echo $meta["host"] ?>"/>
            <input type="hidden" class="stereo-track-fileid" name="stereo_track_fileid[]" value="<?php echo $meta["fileid"] ?>"/>
            <ul class="stereo-metadata"> 
                <li class="metadata">
                    <?php $this->_the_icon($meta); ?>
                    <span class="stereo-player">
                        <?php $this->_the_audio(); ?>
                        <a href="#" class="stereo-play icon-play-1" title="Play"></a>
                        <a href="#" class="stereo-stop icon-pause-1" title="Stop"></a>
                    </span>
                </li>
                <li class="actions">
                    <span class="submitbox"><a class="stereo-delete-track submitdelete" href="#">Delete track</a></span>
    <?php //<a class="stereo-replace-file" href="#">Replace file</a>?>
                </li>
            </ul>
            <span class="handle right"> </span>
        </li>
    <?php endwhile; ?>
    </ul>
    <a class="stereo-delete-tracks submitdelete hide-if-js" id="stereo_delete_tracks" href="#">Delete all tracks</a>
    <?php $this->nonce_field() ?>
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
