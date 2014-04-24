<?php
/*
 * API Demo
 * Implementing the following 
 * /tracks     ?limit (not implemented)
 *     -> array of tracks
 * 
 * /tracks/id
 *     -> a single track
 * 
 * /playlists      ?limit
 *     -> array of playlists (with tracks)
 * 
 * /playlists/id
 *     -> single playlist
 */

$tracks = array(
    1 => array(
        "title" => "Test track 1"
    ),
    2 => array(
        "title" => "Test track 2"
    ),
    3 => array(
        "title" => "Test track 3"
    )
);

$playlists = array(
    1 => array(
        "title" => "Test playlist 1",
        "tracks" => array(
            $tracks[1], $tracks[2]
        )

    ),
    2 => array(
        "title" => "Test playlist 2",
        "tracks" => array(
            $tracks[3], $tracks[1]
        )
    )
);



$out = "";
$json = "";
$path = ltrim(str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']), "/");

$elements = explode('/', $path);                // Split path on slashes


switch ($foo = array_shift($elements)):

case "tracks":
    if ($elements[0]) {
        $json = $tracks[$elements[0]];
    } else {
        $json = $tracks;
    }
    break;

case "playlists":
    if ($elements[0]) {
        $json = $playlists[$elements[0]];
    } else {
        $json = $playlists;
    }
    break;

default:
    header('HTTP/1.0 404 Not Found');
    $out = "404 Not Found";
    break;

endswitch;


if ($json) {
    header('Content-type: application/json');
    echo json_encode($json);
} else if ($out) {
    echo $out;
} else {
    header('HTTP/1.0 404 Not Found');
    echo "404 Not Found";
}

die();

