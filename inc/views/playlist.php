<ul class="playlist stereo" data-stereo_playlist="<?php echo $post->ID?>">
<?php do_action('stereo_playlist_pre', $post) ?>
<?php while ( $connected->have_posts() ) : $connected->the_post(); $meta = get_stereo_track_meta($post->ID); ?>
    <li class="stereo track" data-stereo_track="<?php echo $post->ID?>">
        <?php do_action('stereo_playlist_track_pre', $post, $meta) ?>
        <span class="stereo-track-number"><?php echo apply_filters("stereo_playlist_track_number", $post->menu_order, $post, $meta) ?></span>
        <span class="stereo-track-title"><?php echo apply_filters("stereo_playlist_track_title", get_the_title(), $post, $meta) ?></span>
        <?php do_action('stereo_playlist_track_post', $post, $meta) ?>
    </li>
<?php endwhile; ?>
<?php do_action('stereo_playlist_post', $post) ?>
</ul>
