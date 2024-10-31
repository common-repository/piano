<?php

use Piano\Piano;
use Piano\Settings;

/**
 * Piano plugin main class
 */
class WP_Piano {

    /**
     * Map of classname to file name without extension into "include" directory
     * @var string[]
     */
    private static $class_map;

    /**
     * Piano instance
     * @var Piano
     */
    private static $piano;

    /**
     * Register piano plugin
     */
    public static function register() {
        add_action( 'init', [ __CLASS__, 'init' ] );

        register_activation_hook( PIANO_PLUGIN_FILE, [ __CLASS__, 'activate' ] );
        register_deactivation_hook( PIANO_PLUGIN_FILE, [ __CLASS__, 'deactivate' ] );
    }

    /**
     * Register map of classname to file name
     * autoload.php:
     * <code>
     * WP_Piano::register_classmap(
     *     // map class PianoExample to .../piano/include/class-piano-example.php
     *     'PianoExample' => 'class-piano-example'
     *
     *     // map class PianoExtExample to .../piano/include/extensions/class-piano-example.php
     *     'PianoExtExample' => 'extensions/class-piano-example'
     * );
     * </code>
     * @param $class_map string[] Map of classname to file name without extension into "include" directory
     * @throws Exception
     */
    public static function register_classmap( $class_map ) {
        self::$class_map = $class_map;
        spl_autoload_register( [ __CLASS__, 'autoload_handler' ] );
    }

    /**
     * Classes autoload handler
     * @param $class_name string Class name to load
     */
    public static function autoload_handler( $class_name ) {
        if ( ! isset( self::$class_map[ $class_name ] ) ) {
            return;
        }

        $class_path = self::$class_map[ $class_name ];
        require_once PIANO_PLUGIN_DIR . "/include/$class_path.php";
    }

    /**
     * Initialize plugin
     */
    public static function init() {
        self::$piano = new Piano;
    }

    /**
     * Get Piano instance
     * @return Piano
     */
    public static function piano() {
        return self::$piano;
    }

    /**
     * Activate Piano plugin
     */
    public static function activate() {
        $error = '';
        if ( version_compare( PHP_VERSION, '5.6.0' ) < 0 ) {
            $error .= '<li>Requires PHP 5.6+</li>';
        }

        if ( ! extension_loaded( 'curl' ) ) {
            $error .= '<li><a href="https://www.php.net/manual/en/book.curl.php">curl php module</a> is required</li>';
        }

        if ( ! extension_loaded( 'openssl' ) ) {
            $error .= '<li><a href="https://www.php.net/manual/en/book.openssl.php">openssl php module</a> is required</li>';
        }

        if ( $error ) {
            die( '<div><strong>Piano could not be enabled:</strong></div><ul>' . $error . '</ul>');
        }
    }

    /**
     * Deactivate Piano plugin
     */
    public static function deactivate() {
        (new Settings())->reset();
    }
}