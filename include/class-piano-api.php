<?php

namespace Piano;

use Exception;

/**
 * Piano API
 * @package Piano
 */
class API {

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var Piano
     */
    private $piano;

    /**
     * @param $piano Piano
     */
    public function __construct( $piano ) {
        $this->piano = $piano;
        $this->baseURL = $piano->settings()->get_endpoint() . "/api/v3";
    }

    /**
     * Get Piano application information
     * @return mixed
     * @throws Exception
     */
    public function get_application() {
        $response = $this->call( '/publisher/app/get', 'GET' );

        $code = $response->code;
        if ( $code == 0 ) {
            return $response->app;
        }

        switch ( $code ) {
            case 400:
                throw new Exception( __( 'Invalid Application ID', 'piano' ) );
            case 401:
                throw new Exception( __( 'Invalid API Token', 'piano' ) );
            default:
                $message = isset( $response->message ) && ! empty( $response->message )
                    ? $response->message
                    : "Error $code";
                throw new Exception( $message );
        }
    }

    /**
     * Call Piano API
     * @param $path string
     * @param $method string
     * @return mixed
     * @throws Exception
     */
    private function call( $path, $method = 'POST' ) {
        $url = $this->baseURL . $path;
        $params = [
            'aid' => $this->piano->settings( Settings::AID ),
            'api_token' => $this->piano->settings(  Settings::API_TOKEN )
        ];

        $method = strtoupper( $method );

        $body = null;

        switch ( $method ) {
            case 'HEAD':
            case 'GET':
                $url = $url . '?' . http_build_query($params);
                break;
            default:
                $body = $params;
        }

        $request = wp_remote_request( $url, [
            'method' => $method,
            'body' => $body,
        ]);

        $response = wp_remote_retrieve_body( $request );

        if ( wp_remote_retrieve_response_code( $request ) != 200 ) {
            throw new Exception( __( 'API connection failed', 'piano' ) );
        }

        if ( $response === false ) {
            throw new Exception( __( 'API connection failed', 'piano' ) );
        }

        if ( empty( $response ) ) {
            throw new Exception( __( 'API returned empty result', 'piano' ) );
        }

        $json = json_decode( $response );

        if ( null === $json ) {
            throw new Exception( __( 'API returned ambiguous response', 'piano' ) );
        }

        return $json;
    }
}