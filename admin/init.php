<?php
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Admin init
 */


if (stereo_option('local_support')) {
    define('STEREO_WP_SRC', true);
}

if (stereo_option('soundcloud_id')) {
    define('STEREO_SC_SRC', true);
}

if (true === STEREO_SC_SRC) {
    require('inc/soundcloud.php');
}
require("inc/options.php");
require("inc/attachments.php");
require("inc/custom-post.php");
require("inc/functions.php");

stereo_cpt();

