<?php 
/**
 * Template name: Pods streaming page
 */

$tracks = new Pod("tracks");
$tracks->findRecords(array("limit" => -1, "select" => "t.id, t.file, t.name", "where" => "t.id = '" . void_url_variable(2) . "'"));
if ($tracks->getTotalRows() === 0) {
    void_404();
}
//TODO: Check for cookie

if (!voider_check_expiring_link(void_url_variable(2), void_url_variable(3), void_url_variable(4))) {
    void_404();
}


if (!voider_check_streaming_timestamp(void_url_variable(3))) {
    header("HTTP/1.0 410 Gone");
    die("The link has expired.");
}

$tracks->fetchRecord();

$file = $tracks->get_field("file");
$file = str_replace(trailingslashit(site_url()), ABSPATH, $file);

//$mime_type = "audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
$mime_type = "audio/mpeg";

if(file_exists($file)){
    header("Content-type: {$mime_type}");
    header('Content-length: ' . filesize($file));
    header('Content-Disposition: filename="' . $tracks->get_field("name"));
    header('X-Pad: avoid browser bug');
    header('Cache-Control: no-cache');
    header("Expires: 0");
    readfile($file);
}else{
    void_404();
}

exit(0);
