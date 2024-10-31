<?php

 namespace Piano;

 const AMP_ACTION   = 'piano_amp_action';

 // Piano AMP actions
 const AMP_LOGIN   = 'sign-in';
 const AMP_LOGOUT  = 'sign-out';

 // Piano environments
 const ENVIRONMENT_PRODUCTION                = 'production';
 const ENVIRONMENT_PRODUCTION_EUROPE         = 'production-europe';
 const ENVIRONMENT_PRODUCTION_AUSTRALIA      = 'production-australia';
 const ENVIRONMENT_PRODUCTION_ASIA_PACIFIC   = 'production-asia-pacific';
 const ENVIRONMENT_SANDBOX                   = 'sandbox';
 const ENVIRONMENT_CUSTOM                    = 'custom';

 const ENVIRONMENT_ENDPOINT_PIANO       = 'piano';
 const ENVIRONMENT_ENDPOINT_EXPERIENCE  = 'experience';
 const ENVIRONMENT_ENDPOINT_PIANO_ID    = 'piano_id';

 const ENVIRONMENT_ENDPOINTS = [
     // Main
     ENVIRONMENT_PRODUCTION => [
         ENVIRONMENT_ENDPOINT_PIANO         => 'https://buy.tinypass.com',
         ENVIRONMENT_ENDPOINT_EXPERIENCE    => 'https://experience.tinypass.com'
     ],
     // Europe
     ENVIRONMENT_PRODUCTION_EUROPE => [
        ENVIRONMENT_ENDPOINT_PIANO         => 'https://buy-eu.piano.io',
        ENVIRONMENT_ENDPOINT_EXPERIENCE    => 'https://experience-eu.piano.io',
        ENVIRONMENT_ENDPOINT_PIANO_ID      => 'https://id-eu.piano.io'
    ],
     // Australia
     ENVIRONMENT_PRODUCTION_AUSTRALIA => [
         ENVIRONMENT_ENDPOINT_PIANO         => 'https://buy-au.piano.io',
         ENVIRONMENT_ENDPOINT_EXPERIENCE    => 'https://experience-au.piano.io',
         ENVIRONMENT_ENDPOINT_PIANO_ID      => 'https://id-au.piano.io'
     ],
     // Asia/Pacific
     ENVIRONMENT_PRODUCTION_ASIA_PACIFIC => [
         ENVIRONMENT_ENDPOINT_PIANO         => 'https://buy-ap.piano.io',
         ENVIRONMENT_ENDPOINT_EXPERIENCE    => 'https://experience-ap.piano.io',
         ENVIRONMENT_ENDPOINT_PIANO_ID      => 'https://id-ap.piano.io'
     ],
     // Sandbox
     ENVIRONMENT_SANDBOX => [
         ENVIRONMENT_ENDPOINT_PIANO         => 'https://sandbox.tinypass.com',
         ENVIRONMENT_ENDPOINT_EXPERIENCE    => 'https://sandbox.tinypass.com',
         ENVIRONMENT_ENDPOINT_PIANO_ID      => '/'
     ]
 ];

 // User provider
const USER_PROVIDER_USERREF     = 'publisher_user_ref';
const USER_PROVIDER_PIANO_ID    = 'piano_id';

const MY_ACCOUNT_CLASS          = 'wp_piano_my_account';
const PIANO_ID_BUTTON_CLASS     = 'wp_piano_id_button';