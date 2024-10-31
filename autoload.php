<?php

$piano_classmap = [
    // Piano
    'Piano\\Piano'          => 'class-piano',
    'Piano\\Utils'          => 'class-piano-utils',
    'Piano\\SecurityUtils'  => 'class-piano-securityutils',
    'Piano\\Settings'       => 'class-piano-settings',
    'Piano\\API'            => 'class-piano-api',
    'Piano\\Dashboard'      => 'class-piano-dashboard',
    'Piano\\Content'        => 'class-piano-content',
    // WP
    'WP_Piano'              => 'class-wp-piano-plugin'
];

require_once PIANO_PLUGIN_DIR . '/include/constants.php';
require_once PIANO_PLUGIN_DIR . '/include/class-wp-piano.php';

WP_Piano::register_classmap($piano_classmap);