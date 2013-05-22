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

