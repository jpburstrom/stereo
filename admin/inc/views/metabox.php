<div id="stereo_container">
<?php $this->import_button() ?>
    <a id="stereo_add_track" class="button button-large stereo-add-track">Add track</a>
    <input type="hidden" id="stereo_track_count" name="stereo_track_count">
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
    <script id="stereo_track_template" type="text/html">
        <li class="stereo-track postarea">
            <span class="handle"> </span>
            <span class="stereo-track-number"></span><input class="stereo-track-number-input" name="stereo_track_number[]" type="hidden" value=""/>
            <input type="text" placeholder="Track name" class="stereo-track-name" name="stereo_track_name[]"/>
            <input type="hidden" class="stereo-track-host" name="stereo_track_host[]" />
            <input type="hidden" class="stereo-track-fileid" name="stereo_track_fileid[]"/>
            <ul class="stereo-metadata"> 
                <li class="metadata">
                </li>
                <li class="actions">
                    <a class="stereo-delete-track" href="#">Delete track</a>
    <?php //<a class="stereo-replace-file" href="#">Replace file</a>?>
                </li>
            </ul>
            <a class="button button-large stereo-add-file">Add file...</a>
            <span class="handle right"> </span>
        </li>
    </script>
    <ul id="stereo_tracks">
    <?php while ( $connected->have_posts() ) : $connected->the_post(); $meta = get_post_meta($post->ID, '_stereo', true); ?>
        <li class="stereo-track postarea">
            <span class="handle"> </span>
            <span class="stereo-track-number"><?php echo $post->menu_order ?> </span><input class="stereo-track-number-input" name="stereo_track_number[]" type="hidden" value="<?php echo $post->menu_order ?>"/>
            <input type="text" placeholder="Track name" value="<?php the_title(); ?>" class="stereo-track-name" name="stereo_track_name[]"/>
            <input type="hidden" class="stereo-track-id" value="<?php the_ID(); ?>" name="stereo_track_ID[]"/>
            <input type="hidden" class="stereo-track-host" name="stereo_track_host[]" value="<?php echo $meta["host"] ?>"/>
            <input type="hidden" class="stereo-track-fileid" name="stereo_track_fileid[]" value="<?php echo $meta["fileid"] ?>"/>
            <ul class="stereo-metadata"> 
                <li data-stereo_track="<?php the_ID() ?>" data-stereo_data="<?php echo $this->track_data_json() ?>" class="metadata"></li>
                <li class="actions">
                    <a class="stereo-delete-track" href="#">Delete track</a>
    <?php //<a class="stereo-replace-file" href="#">Replace file</a>?>
                </li>
            </ul>
            <a class="button button-large stereo-add-file">Add file...</a>
            <span class="handle right"> </span>
        </li>
    <?php endwhile; ?>
    </ul>
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
