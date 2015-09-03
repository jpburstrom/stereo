<?php
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Common admin functions
 */


/**
 * Get StereoCustomPost instance
 *
 * @return StereoCustomPost instance
 */
function stereo_cpt() {
    static $wp_stereo;
    if (!$wp_stereo) {
        $wp_stereo = new StereoCustomPost();
    }
    return $wp_stereo;
}
