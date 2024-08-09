<?php


namespace App\Helpers;


class Base64 {

    static function encode(string $value): string {
        return base64_encode($value);
    }

    static function decode(string $encodedString) : bool  {
        
        return base64_encode( base64_decode( $encodedString ) ) === $encodedString;

    }

}