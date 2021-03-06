<?php
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Admin init
 */


//Support for local files
define('STEREO_WP_SRC', true);

if (stereo_option('soundcloud_id')) {
    define('STEREO_SC_SRC', true);
} else {
    define('STEREO_SC_SRC', false);
}

require("inc/functions.php");
require("inc/options.php");
require("inc/stereo_custom_post.php");
require("inc/attachments.php");
require("inc/updater.php");
require("inc/docs.php");

new WP_GitHub_Updater(array(
    'slug' => "stereo/stereo.php", // this is the slug of your plugin
    'proper_folder_name' => 'stereo', // this is the name of the folder your plugin lives in
    'api_url' => 'https://api.github.com/repos/jpburstrom/stereo', // the github API url of your github repo
    'raw_url' => 'https://raw.github.com/jpburstrom/stereo/master', // the github raw url of your github repo
    'github_url' => 'https://github.com/jpburstrom/stereo', // the github url of your github repo
    'zip_url' => 'https://github.com/jpburstrom/stereo/archive/master.zip', // the zip url of the github repo
    'sslverify' => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
    'requires' => '3.9', // which version of WordPress does your plugin require?
    'tested' => '3.9', // which version of WordPress is your plugin tested up to?
    'readme' => 'readme.txt'
));

//Init custom post type
stereo_cpt();

