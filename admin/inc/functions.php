<?php
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Common admin functions
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

