<?php
//Part of the Stereo test
//

echo json_encode(
    array(
        "title" => "Hello World",
        "album" => "An Album",
        "year" => "2013",
        "file" => $_GET["f"],
        "foo" => ((int) $_GET["foo"]) + 1
    )
);
