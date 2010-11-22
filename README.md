# SoundCloud PHP API Wrapper

## Introduction

A wrapper for the SoundCloud API written in PHP with support for authentication using [OAuth 2.0](http://oauth.net/2/).

The wrapper got a real overhaul with version 2.0. The current version can easily be distributed as a PEAR package.

## Getting started

Check out the [getting started](https://github.com/mptre/php-soundcloud/wiki/OAuth-2) wiki entry for further reference on how to get started. Also make sure to check out the [demo application](https://github.com/mptre/ci-soundcloud) for some example code.


## Examples

The wrapper includes convenient methods used to perform HTTP requests on behalf of the authenticated user. Below you'll find a few quick examples.

### GET

```php
try {
    $response = json_decode($soundcloud->get('me'), true);
} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
    exit($e->getMessage());
}
```

### POST

```php
$comment = <<<EOH
<comment>
    <body>Yeah!</body>
</comment>
EOH;

try {
    $response = json_decode(
        $soundcloud->post(
            'tracks/1/comments',
            $comment,
            array(CURLOPT_HTTPHEADER => 'Content-Type: application/xml')
        ),
        true
    );
} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
    exit($e->getMessage());
}
```

### PUT

```php
$track = <<<EOH
<track>
    <downloadable>true</downloadable>
</track>
EOH;

try {
    $response = json_decode(
        $soundcloud->put(
            'tracks/1',
            $track,
            array(CURLOPT_HTTPHEADER => 'Content-Type: application/xml')
        ),
        true
    );
} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
    exit($e->getMessage());
}
```

### DELETE

```php
try {
    $response = json_decode($soundcloud->delete('tracks/1'), true);
} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
    exit($e->getMessage());
}
```

## Feedback and questions

Found a bug or missing a feature? Don't hesitate to create a new issue here on GitHub. Or contact me [directly](https://github.com/mptre).

Also make sure to check out the official [documentation](https://github.com/soundcloud/api/wiki/) and the join [Google Group](https://groups.google.com/group/soundcloudapi?pli=1) in order to stay updated.
