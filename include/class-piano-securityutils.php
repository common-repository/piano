<?php

namespace Piano;

/**
 * Piano security utils
 * @package Piano
 */
class SecurityUtils
{
    const DELIMITER             = '~~~';
    const OPENSSL_CIPHER_METHOD = 'AES-256-ECB';
    const HMAC_ALGORITHM        = 'SHA256';

    /**
     * Encrypt data by private key
     * @param $key string
     * @param $data string
     * @return string
     */
    public static function encrypt( $key, $data ) {
        $orig_key = $key;

        if ( strlen( $key ) > 32) {
            $key = substr( $key, 0, 32 );
        }

        if ( strlen( $key ) < 32 ) {
            $key = str_pad( $key, 32, 'X' );
        }

        $safe = SecurityUtils::encode(
            openssl_encrypt( $data, SecurityUtils::OPENSSL_CIPHER_METHOD, $key, OPENSSL_RAW_DATA )
        );

        return $safe
            . SecurityUtils::DELIMITER
            . SecurityUtils::encode( hash_hmac( SecurityUtils::HMAC_ALGORITHM, $safe, $orig_key, true ) );
    }

    /**
     * Encode data
     * @param $data string
     * @return string
     */
    private static function encode( $data ) {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    }
}
