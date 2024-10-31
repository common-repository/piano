<?php

namespace Piano;

/**
 * Piano dashboard extensions
 * @package Piano
 */
class Dashboard {

    const MENU_SLUG = 'piano_dashboard_menu';
    const MENU_ICON = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNS4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iNDAwcHgiIGhlaWdodD0iNDAwcHgiIHZpZXdCb3g9IjAgMCA0MDAgNDAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCA0MDAgNDAwIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxwYXRoIGZpbGw9IiMzQjhFREUiIGQ9Ik0yMTAuOTMxLDEyOC41NEgxNjkuNDJ2MTAxLjYyOGg0MS41MTFjMjUuNzYzLDAsNDEuMjI1LTE5LjE4LDQxLjIyNS00OS44MTF2LTIuMDA3DQoJCUMyNTIuMTU1LDE0Ny40MzMsMjM2LjY5MywxMjguNTQsMjEwLjkzMSwxMjguNTR6Ii8+DQoJPHBhdGggZmlsbD0iIzNCOEVERSIgZD0iTTIwMC4yNzgsNTBDMTE1LjM1LDUwLDUxLjkzLDEwOS41NTcsNTEuOTMsMTk4Ljg5N3Y0LjQwOUM1MS45MywyOTAuOTk0LDExNC43OTgsMzUwLDE5OS43MjUsMzUwDQoJCWM4NC45MjYsMCwxNDguMzQ0LTU4LjQ1NiwxNDguMzQ0LTE0OC4zNDN2LTQuNDEzQzM0OC4wNjksMTA5LjAwNiwyODQuNjUxLDUwLDIwMC4yNzgsNTB6IE0yODcuNjUxLDE4MC4wNzENCgkJYzAsNDYuNjYxLTMyLjkyMSw3Ny4wMDYtNzcuMDA4LDc3LjAwNkgxNjkuNDJ2NTMuMjQ5aC0zNS40OTlWMTAxLjM0M2g3Ny4wMDljNDMuOCwwLDc2LjcyMSwzMC42MzEsNzYuNzIxLDc2LjQzNlYxODAuMDcxeiIvPg0KPC9nPg0KPC9zdmc+DQo=';

    /**
     * @var Piano
     */
    private $piano;

    /**
     * @param $piano Piano
     */
    public function __construct( $piano ) {
        $this->piano = $piano;

        add_action( 'admin_menu', [ $this, 'menu_handler' ] );
        add_filter( 'plugin_action_links_' . PIANO_PLUGIN_NAME, [ $this, 'settings_link_handler' ] );

        if ( ! $piano->settings()->is_configured() ) {
            add_action( 'wp_ajax_piano_settings', [ $this, 'main_settings_handler' ] );
        } else {
            add_action( 'wp_ajax_piano_settings_additional', [ $this, 'additional_settings_handler' ] );
            add_action( 'wp_ajax_piano_reset_settings', [ $this, 'reset_settings_handler' ] );
        }
    }

    /**
     * Add Piano menu item to dashboard
     */
    public function menu_handler() {
        add_menu_page(
            __( 'Piano Settings', 'piano' ),
            __( 'Piano', 'piano' ),
            'manage_options',
            Dashboard::MENU_SLUG,
            [ $this, 'settings_handler' ],
            Dashboard::MENU_ICON
        );
    }

    /**
     * Render Piano settings page
     */
    public function settings_handler() {
        wp_enqueue_style( 'piano_dashboard_settings_style', plugins_url( '/static/dashboard/settings/index.css', PIANO_PLUGIN_FILE ) );

        wp_enqueue_script(
            'piano_dashboard_settings_script',
            plugins_url( '/static/dashboard/settings/index.js', PIANO_PLUGIN_FILE ),
            [],
            false,
            true
        );

        $script = '';
        if ( $this->piano->settings()->is_configured() ) {
            $options = Utils::camel_case_array( $this->piano->settings()->all(), [
                Settings::API_TOKEN,
                Settings::PRIVATE_KEY
            ]);

            $script = 'window.wp_piano = ' . json_encode( $options ) . ';';
        }

        wp_add_inline_script(
            'piano_dashboard_settings_script',
            'window.wp_piano_settings_prefix_url = "' . plugins_url( '/static/dashboard/settings', PIANO_PLUGIN_FILE ) . '";' . $script,
            'before'
        );

        echo '<div id="piano_settings"></div>';
    }

    /**
     * Set Piano main settings
     */
    public function main_settings_handler() {
        try {
            $this->piano->settings()->set_main( Utils::get_json_body() );

            $api = new API( $this->piano );
            $this->piano->settings()->set_application( $api->get_application() );

            $additional = Settings::DEFAULT_ADDITIONAL;
            $this->piano->settings()->set_additional( $additional );
            $this->piano->settings()->save();

            $result = Utils::camel_case_array( $this->piano->settings()->all(), [
                Settings::ENVIRONMENT,
                Settings::ENVIRONMENT_URL,
                Settings::AID,
                Settings::API_TOKEN,
                Settings::PRIVATE_KEY
            ]);

            $result[ 'status' ] = 200;

            error_log(var_export($result, true));

            wp_send_json_success( $result );
        } catch ( \Exception $exception ) {
            wp_send_json_error([
                "status" => 400,
                "error" => $exception->getMessage()
            ]);
        }
    }

    /**
     * Set Piano additional settings
     */
    public function additional_settings_handler() {
        try {
            $api = new API( $this->piano );
            $this->piano->settings()->set_application( $api->get_application() );

            $this->piano->settings()->set_additional( Utils::get_json_body() );
            $this->piano->settings()->save();

            $result = Utils::camel_case_array(
                    $this->piano->settings()->all([
                    Settings::NAME,
                    Settings::LOGO_URL
                ])
            );

            $result[ 'status' ] = 200;

            wp_send_json_success($result);
        } catch ( \Exception $exception ) {
            wp_send_json_error([
                "status" => 400,
                "error" => $exception->getMessage()
            ]);
        }
    }

    /**
     * Reset Piano settings
     */
    public function reset_settings_handler() {
        $this->piano->settings()->reset();
        wp_send_json_success([
            'status' => 200
        ]);
    }

    /**
     * Get Piano settings links
     * @param $links
     * @return mixed
     */
    public function settings_link_handler( $links ) {
        $url = add_query_arg( [ 'page' => Dashboard::MENU_SLUG ], admin_url( 'admin.php' ) );
        $settings_link = '<a href="' . $url . '">' . __( 'Settings', 'piano' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
}