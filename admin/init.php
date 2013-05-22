<?php
/**
 * Stereo admin init
 * Load everything else
 *
 * (c) Johannes Burström 2013
 */

require_once( STEREO_PLUGIN_DIR . "/lib/php-soundcloud/Services/Soundcloud.php" );

require_once( STEREO_PLUGIN_DIR . "/inc/class-stereo-soundcloud.php");

require_once("stereo-options.php");
require_once("stereo-attachments.php");
require_once("stereo-custom-post.php");
require_once("functions.php");


