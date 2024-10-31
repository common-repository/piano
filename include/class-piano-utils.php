<?php

namespace Piano;

/**
 * Piano utils
 * @package Piano
 */
class Utils {

    /**
     * Get body in JSON format
     * @return array
     */
    public static function get_json_body() {
        return json_decode( file_get_contents('php://input'), true );
    }

    /**
     * Convert string to camel-case format
     * @param $str string
     * @return string
     */
    public static function camel_case( $str ) {
        $result = '';
        $end = strlen( $str ) - 1;

        for ( $i = 0; $i <= $end; $i++ ) {
            $char = $str[ $i ];
            if ( $char == '_' && $i < $end ) {
                $char = $str[ ++$i ];
                $ord = ord( $char );
                $result .= $ord >= 97 && $ord <= 122
                    ? chr( ord( $char ) - 32 )
                    : $char;
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * Copy array with camel-case keys
     * @param $src array
     * @param $dest array
     * @param $exclude_keys array
     */
    public static function copy_camel_case_array( $src, & $dest, $exclude_keys = null ) {
        if ( $exclude_keys != null ) {
            foreach ( $exclude_keys as $exclude_key ) {
                if ( isset( $src[ $exclude_key ] ) ) {
                    unset( $src[ $exclude_key ] );
                }
            }
        }

        foreach ($src as $key => $value ) {
            $dest[ Utils::camel_case( $key ) ] = $value;
        }
    }

    /**
     * Convert array with camel-case keys
     * @param $src array
     * @param $exclude_keys array
     * @return array
     */
    public static function camel_case_array( $src, $exclude_keys = null ) {
        $dest = [];
        Utils::copy_camel_case_array( $src, $dest, $exclude_keys );
        return $dest;
    }
}