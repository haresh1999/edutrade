<?php

namespace App\Classes;

use Exception;

class SabpaisaAuthSandbox
{
    private const IV_SIZE = 12; // 96 bits for GCM

    private const TAG_SIZE = 16; // 128 bits = 16 bytes

    private const HMAC_LENGTH = 48; // SHA-384 = 48 bytes

    // Convert base64 to binary key

    private static function base64ToKey(string $base64Key): string
    {

        return base64_decode($base64Key, true);
    }

    // Convert bytes to HEX

    private static function bytesToHex(string $bytes): string
    {

        return strtoupper(bin2hex($bytes));
    }

    // Convert HEX to bytes

    private static function hexToBytes(string $hex): string
    {

        return hex2bin($hex);
    }

    // Encrypt function

    public static function encrypt(string $aesKeyBase64, string $hmacKeyBase64, string $plaintext): string
    {

        $aesKey = self::base64ToKey($aesKeyBase64);

        $hmacKey = self::base64ToKey($hmacKeyBase64);

        $iv = random_bytes(self::IV_SIZE);

        $tag = '';

        $cipherText = openssl_encrypt(

            $plaintext,

            'aes-256-gcm',

            $aesKey,

            OPENSSL_RAW_DATA,

            $iv,

            $tag,

            '',

            self::TAG_SIZE

        );

        if ($cipherText === false) {

            throw new Exception("Encryption failed");
        }

        $encryptedMessage = $iv . $cipherText . $tag;

        // Generate HMAC

        $hmac = hash_hmac('sha384', $encryptedMessage, $hmacKey, true);

        // Final message: [HMAC || IV || CipherText || Tag]

        $finalOutput = $hmac . $encryptedMessage;

        return self::bytesToHex($finalOutput);
    }

    // Decrypt function

    public static function decrypt(string $aesKeyBase64, string $hmacKeyBase64, string $hexCipherText): string
    {

        $aesKey = self::base64ToKey($aesKeyBase64);

        $hmacKey = self::base64ToKey($hmacKeyBase64);

        $fullMessage = self::hexToBytes($hexCipherText);

        $hmacReceived = substr($fullMessage, 0, self::HMAC_LENGTH);

        $encryptedData = substr($fullMessage, self::HMAC_LENGTH);

        $computedHmac = hash_hmac('sha384', $encryptedData, $hmacKey, true);

        if (!hash_equals($hmacReceived, $computedHmac)) {

            throw new Exception("HMAC validation failed. Data may be tampered!");
        }

        $iv = substr($encryptedData, 0, self::IV_SIZE);

        $cipherTextWithTag = substr($encryptedData, self::IV_SIZE);

        $cipherText = substr($cipherTextWithTag, 0, -self::TAG_SIZE);

        $tag = substr($cipherTextWithTag, -self::TAG_SIZE);

        $plainText = openssl_decrypt(

            $cipherText,

            'aes-256-gcm',

            $aesKey,

            OPENSSL_RAW_DATA,

            $iv,

            $tag

        );

        if ($plainText === false) {

            throw new Exception("Decryption failed");
        }

        return $plainText;
    }
}

class SabpaisaAuth {

    private const OPENSSL_CIPHER_NAME = "aes-128-cbc";
    private const CIPHER_KEY_LEN = "16";

    private static function fixKey($key)
    {

        if (strlen($key) < SabpaisaAuth::CIPHER_KEY_LEN) {

            return str_pad("$key", SabpaisaAuth::CIPHER_KEY_LEN, "0");
        }

        if (strlen($key) > SabpaisaAuth::CIPHER_KEY_LEN) {

            return substr($key, 0, SabpaisaAuth::CIPHER_KEY_LEN);
        }
        return $key;
    }

    static function encrypt($key, $iv, $data)
    {
        $encodedEncryptedData = base64_encode(openssl_encrypt($data, SabpaisaAuth::OPENSSL_CIPHER_NAME, SabpaisaAuth::fixKey($key), OPENSSL_RAW_DATA, $iv));
        $encodedIV = base64_encode($iv);
        $encryptedPayload = $encodedEncryptedData . ":" . $encodedIV;
        return $encryptedPayload;
    }

    static function decrypt($key, $iv, $data)
    {
        $parts = explode(':', $data);
        $encrypted = $parts[0];
        $iv = $parts[1];

        $decryptedData = openssl_decrypt(base64_decode($encrypted), SabpaisaAuth::OPENSSL_CIPHER_NAME, SabpaisaAuth::fixKey($key), OPENSSL_RAW_DATA, $iv);
        return $decryptedData;
    }
}
