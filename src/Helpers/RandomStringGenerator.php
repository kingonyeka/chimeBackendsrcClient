<?php

namespace App\Helpers;

use Exception;

/**
 * RandomStringGenerator
 * 
 * Rights of NFORSHIFU234 Dev
 */
class RandomStringGenerator {

    private static $characterSets = [
        'alphanumeric' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'alphabetic' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'numeric' => '0123456789'
    ];

    /**
     * Generate a random string.
     *
     * @param int $length The length of the random string.
     * @param string $characterSet The character set to use (alphanumeric, alphabetic, numeric).
     * @return string The generated random string.
     * @throws Exception If the length is not a positive integer or character set is invalid.
     */
    public static function generate($length = 16, $characterSet = 'alphanumeric') {
        if (!is_int($length) || $length <= 0) {
            throw new Exception('Length must be a positive integer.');
        }

        if (!array_key_exists($characterSet, self::$characterSets)) {
            throw new Exception('Invalid character set.');
        }

        $characters = self::$characterSets[$characterSet];
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Generate a random alphanumeric string.
     *
     * @param int $length The length of the random string.
     * @return string The generated random string.
     */
    public static function generateAlphanumeric($length = 16) {
        return self::generate($length, 'alphanumeric');
    }

    /**
     * Generate a random alphabetic string.
     *
     * @param int $length The length of the random string.
     * @return string The generated random string.
     */
    public static function generateAlphabetic($length = 16) {
        return self::generate($length, 'alphabetic');
    }

    /**
     * Generate a random numeric string.
     *
     * @param int $length The length of the random string.
     * @return string The generated random string.
     */
    public static function generateNumeric($length = 16) {
        return self::generate($length, 'numeric');
    }
}

// // Usage example
// try {
//     $randomStringGenerator = new RandomStringGenerator();

//     echo "Alphanumeric: " . $randomStringGenerator->generateAlphanumeric(16) . "\n";
//     echo "Alphabetic: " . $randomStringGenerator->generateAlphabetic(16) . "\n";
//     echo "Numeric: " . $randomStringGenerator->generateNumeric(16) . "\n";
//     echo "Custom Length (10) Alphanumeric: " . $randomStringGenerator->generateAlphanumeric(10) . "\n";
// } catch (Exception $e) {
//     echo 'Error: ' . $e->getMessage();
// }

