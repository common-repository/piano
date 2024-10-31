<?php

namespace Piano;

use ArrayAccess;
use Exception;

/**
 * Piano settings
 * @package Piano
 */
class Settings implements ArrayAccess {

    // Main settings
    const ENVIRONMENT       = 'environment';
    const ENVIRONMENT_URL   = 'environment_url';
    const AID               = 'aid';
    const API_TOKEN         = 'api_token';
    const NAME              = 'name';
    const LOGO_URL          = 'logo_url';
    const USER_PROVIDER     = 'user_provider';
    const PRIVATE_KEY       = 'private_key';
    const PRIVILEGED_USERS  = 'privileged_users';
    const ADBLOCK_DETECTOR  = 'adblock_detector';
    const CXENSE_SITE_ID    = 'cxense_site_id';

    const USER_PROVIDER_INTEGRATION_ENABLED    = 'user_provider_integration_enabled';

    const SETTINGS  = 'PIANO_SETTINGS';

    // Default settings
    const DEFAULT_ADDITIONAL = [
        Settings::PRIVILEGED_USERS => true,
        Settings::ADBLOCK_DETECTOR => true,
        Settings::USER_PROVIDER_INTEGRATION_ENABLED => true,
        Settings::CXENSE_SITE_ID => ''
    ];

    /**
     * @var array
     */
    private $options;

    public function __construct() {
        $this->options = get_option( Settings::SETTINGS, null );
    }

    /**
     * Implement ArrayAccess::offsetExists
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists( $offset )
    {
        return isset( $this->options[ $offset ] );
    }

    /**
     * Implement ArrayAccess::offsetGet
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet( $offset )
    {
        return $this->options[ $offset ];
    }

    /**
     * Implement ArrayAccess::offsetSet
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        $this->options[ $offset ] = $offset;
    }

    /**
     * Implement ArrayAccess::offsetUnset
     * @param mixed $offset
     */
    public function offsetUnset( $offset )
    {
        unset( $this->options[ $offset ] );
    }

    /**
     * Is Piano configured
     * @return bool
     */
    public function is_configured() {
        return $this->options != null;
    }

    /**
     * Get Piano endpoint
     * @param $endpoint_type string
     * @return string|null
     */
    public function get_endpoint( $endpoint_type = ENVIRONMENT_ENDPOINT_PIANO ) {
        $environment = $this->options[ Settings::ENVIRONMENT ];
        if ( $environment == ENVIRONMENT_CUSTOM ) {
            if ( $endpoint_type == ENVIRONMENT_ENDPOINT_PIANO_ID ) {
                return '/';
            }
            return $this->options[ Settings::ENVIRONMENT_URL ];
        }

        $endpoints = ENVIRONMENT_ENDPOINTS[ $environment ];
        if ( isset( $endpoints[ $endpoint_type ] ) ) {
            return $endpoints[ $endpoint_type ];
        }

        return null;
    }

    /**
     * Get all options
     * @param $filter_keys array
     * @return bool|integer|string|array
     */
    public function all( $filter_keys = null ) {
        if ( $filter_keys != null ) {
            $options = [];
            foreach ( $filter_keys as $filter_key ) {
                $options[ $filter_key ] = $this->options[ $filter_key ];
            }
            return $options;
        }
        return $this->options;
    }

    /**
     * Set Piano main settings
     * @param $settings array
     * @throws Exception
     */
    public function set_main( & $settings ) {
        $main = [];

        Settings::option( $settings, $main, Settings::ENVIRONMENT );
        if ( $main[ Settings::ENVIRONMENT ] == ENVIRONMENT_CUSTOM ) {
            Settings::option( $settings, $main, Settings::ENVIRONMENT_URL, function ( $_, $url ) {
                return rtrim( $url, '/' );
            });
        }

        Settings::option( $settings, $main, Settings::AID );
        Settings::option( $settings, $main, Settings::API_TOKEN );

        $this->options = $main;
    }

    /**
     * Set Piano application settings
     * @param $application object
     * @throws Exception
     */
    public function set_application( $application ) {
        $this->options[ Settings::NAME ] = $application->name;

        if ( ! empty( $application->logo1 ) ) {
            $this->options[ Settings::LOGO_URL ] = $this->get_endpoint() . $application->logo1;
        }

        $this->options[ Settings::USER_PROVIDER ] = $application->user_provider;
        $this->options[ Settings::PRIVATE_KEY ] = $application->private_key;
    }

    /**
     * Set Piano additional settings
     * @param $settings array
     * @throws Exception
     */
    public function set_additional( & $settings ) {
        $additional = [];

        Settings::option( $settings, $additional, Settings::PRIVILEGED_USERS, [ __CLASS__, 'boolean_option' ] );
        Settings::option( $settings, $additional, Settings::ADBLOCK_DETECTOR, [ __CLASS__, 'boolean_option' ] );

        Settings::option( $settings, $additional, Settings::USER_PROVIDER_INTEGRATION_ENABLED, [ __CLASS__, 'boolean_option' ] );

        Settings::option( $settings, $additional, Settings::CXENSE_SITE_ID );

        $this->options = array_merge( $this->options, $additional );
    }

    /**
     * Save Piano settings
     */
    public function save() {
        if ( ! get_option( Settings::SETTINGS ) ) {
            add_option( Settings::SETTINGS, $this->options );
        } else {
            update_option( Settings::SETTINGS, $this->options );
        }
    }

    /**
     * Reset piano settings
     */
    public function reset() {
        delete_option( Settings::SETTINGS );
        $this->options = null;
    }

    /**
     * Get option
     * @param $settings array
     * @param $name string
     * @param $sanitize_handler callable
     * @return bool|integer|string
     * @throws Exception
     */
    private static function get_option( & $settings, $name, $sanitize_handler = null ) {
        if ( ! isset( $settings[ $name ] ) ) {
            error_log( var_export( $settings, true ) );
            throw new Exception( sprintf( __( 'Option %s required', 'piano'), $name ) );
        }
        return $sanitize_handler != null ? $sanitize_handler( $name, $settings[ $name ] ) : $settings [ $name ];
    }

    /**
     * Copy option
     * @param $src array
     * @param $dest array
     * @param $name string
     * @param $sanitize_handler callable
     * @throws Exception
     */
    private static function option( & $src, & $dest, $name, $sanitize_handler = null ) {
        $dest[ $name ] = Settings::get_option( $src, $name, $sanitize_handler );
    }

    /**
     * Convert option value to boolean
     * @param $name string
     * @param $value string
     * @return bool
     * @throws Exception
     */
    private static function boolean_option( $name, $value ) {
        if ( gettype( $value ) == 'boolean' ) {
            return $value;
        }

        switch ( strtolower( $value ) ) {
            case "true":
                return true;
            case "false":
                return false;
            default:
                throw new Exception( sprintf( __( 'Expected bool value for parameter %s', 'piano' ), $name ) );
        }

    }
}