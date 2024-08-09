<?php

namespace App\Helpers;

class URLEncode {
    static function encode(string $value): string {
        return urlencode($value);
    }

    static function decode(string $encodedURL): string {
        return urldecode($encodedURL);
    }
}
