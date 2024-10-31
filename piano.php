<?php

/*
Plugin Name: Piano
Plugin URI: https://piano.io
Description: Plugin for integration with Piano platform
Version: 1.0.0
Author: Piano
Author URI: http://piano.io
License: GPLv2
*/

define( 'PIANO_PLUGIN_VERSION', '1.0.0' );

define( 'PIANO_PLUGIN_FILE', __FILE__ );
define( 'PIANO_PLUGIN_DIR', __DIR__ ); //plugin_dir_path( PIANO_PLUGIN_FILE ) );
define( 'PIANO_PLUGIN_NAME', plugin_basename( PIANO_PLUGIN_FILE ) );

require_once PIANO_PLUGIN_DIR . '/autoload.php';
WP_Piano::register();