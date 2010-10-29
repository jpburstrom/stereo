<?php
class Soundcloud_Version {

    const MAJOR = 2;
    const MINOR = 0;
    const PATCH = '0beta1';

    public static function get() {
        return implode('.', array(self::MAJOR, self::MINOR, self::PATCH));
    }

}
