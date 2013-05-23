<?php
/**
 * Part of Stereo plugin
 */

/**
 * Get StereoSoundCloud instance
 *
 * @return StereoSoundCloud instance
 */

function stereo_sc() {
    static $sc;

    if (!$sc) {
        $sc = new StereoSoundCloud();
    }

    return $sc;
}

function stereo_cpt() {
    static $wp_stereo;
    if (!$wp_stereo) {
        $wp_stereo = new StereoCustomPost();
    }
    return $wp_stereo;
}
