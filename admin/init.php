<?php
/**
 * Stereo admin init
 * Load everything else
 *
 * (c) Johannes Burström 2013
 */


require('inc/soundcloud.php');
require("inc/options.php");
require("inc/attachments.php");
require("inc/custom-post.php");
require("inc/functions.php");

stereo_cpt();
