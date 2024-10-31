<?php

namespace Piano;

/**
 * Piano content
 * @package Piano
 */
class Content {

    /**
     * @var Piano
     */
    private $piano;

    /**
     * @var bool
     */
    private $is_render = true;

    /**
     * @var bool
     */
    private $is_amp = false;

    /**
     * @var bool
     */
    private $is_show_my_account = false;

    /**
     * @var array
     */
    private $amp_actions = null;

    /**
     * @param $piano Piano
     */
    public function __construct( $piano ) {
        $this->piano = $piano;

        $this->amp_actions = [
            AMP_LOGIN   => [ $this, 'amp_login_action' ],
            AMP_LOGOUT  => [ $this, 'amp_logout_action' ]
        ];

        add_shortcode( 'piano_inline', [ $this, 'inline_shortcode_handler' ]);
        add_shortcode( 'piano_my_account', [ $this, 'my_account_shortcode_handler' ]);

        add_action( 'piano_id_login_button', [ $this, 'piano_id_login_button_handler' ] );
        add_action( 'pre_amp_render_post', [ $this, 'pre_amp_render_post_handler' ] );

        add_filter( 'wp', [ $this, 'amp_execute_action_handler'] );

        $this->is_render =
            $piano->settings()->is_configured()
            && ( ! $piano->is_privileged() || $piano->settings( Settings::PRIVILEGED_USERS ) );

        if ( $this->is_render ) {
            if ( $piano->settings( Settings::ADBLOCK_DETECTOR ) ) {
                add_action( 'wp_head', [ $this, 'head_handler' ] );
            }

            add_action( 'wp_footer', [ $this, 'footer_handler' ] );
            add_action( 'amp_post_template_head', [ $this, 'amp_post_template_head_handler' ] );
        }
    }

    /**
     * Get Piano user token
     * @return string
     */
    public function get_user_token() {
        $user = get_userdata( get_current_user_id() );

        $user_token_data = $user
            ? [ 'uid' => $user->data->ID, 'email' => $user->data->user_email ]
            : [ 'uid' => 'anon' ];

        $user_token_data[ 'timestamp' ] = time();

        return SecurityUtils::encrypt(
            $this->piano->settings( Settings::PRIVATE_KEY ),
            json_encode( $user_token_data )
        );
    }

    /**
     * Render Piano inline shortcode:
     * [piano_inline id="..."]
     * @param $attributes array
     * @return string|null
     */
    public function inline_shortcode_handler( $attributes ) {
        if ( $this->is_render && !$this->is_amp && ( is_single() || is_page() ) && isset( $attributes[ 'id' ] ) ) {
            return '<div id="' . esc_attr( $attributes[ 'id' ] ) . '"></div>';
        }
        return null;
    }

    /**
     * Render Piano "My account" shortcode:
     * [piano_my_account id="..."]
     * @return string|null
     */
    public function my_account_shortcode_handler() {
        if ( $this->is_render && !$this->is_amp && ( is_single() || is_page() ) ) {
            $this->is_show_my_account = true;
            return '<div class="' . MY_ACCOUNT_CLASS . '"></div>';
        }
        return null;
    }

    /**
     * Show Piano "My account"
     * @return bool
     */
    public function is_show_my_account() {
        return $this->is_show_my_account;
    }

    /**
     * Piano ID "Sign In" button
     * @param $text string
     * @param $classes string
     * @return string|array
     */
    public function piano_id_login_button_handler( $text = 'Sign In', $classes = null ) {
        if ( ! empty( $classes ) ) {
            if ( is_array( $classes ) ) {
                $classes = implode( ' ', $classes );
            }
            $classes .= ' ' . PIANO_ID_BUTTON_CLASS;
        } else {
            $classes = PIANO_ID_BUTTON_CLASS;
        }

        $classes .= ' hide';

        echo '<button class="' . esc_attr( $classes ) . '">' . $text . '</button>';
    }

    /**
     * Add header scripts
     */
    public function head_handler() {
        require PIANO_PLUGIN_DIR . '/views/adblock.php';
    }

    /**
     * Add footer scripts
     */
    public function footer_handler() {
        require PIANO_PLUGIN_DIR . '/views/script.php';
    }

    /**
     * AMP pre-render
     */
    public function pre_amp_render_post_handler() {
        $this->is_amp = true;
    }

    /**
     * Get AMP actions
     * @return array
     */
    public function amp_actions() {
        return apply_filters( 'piano_amp_actions', $this->amp_actions );
    }

    /**
     * Get AMP actions URL
     * @return array
     */
    public function amp_actions_url() {
        $actions = [];
        foreach ( $this->amp_actions() as $action_name => $action ) {
            if ( is_callable( $action ) ) {
                $action = home_url() . '?' . AMP_ACTION . '=' . $action_name . '&';
            } else {
                $action .= strpos( $action, '?' ) > -1 ? '&' : '?';
            }

            $action .= 'reader_id=READER_ID&url=SOURCE_URL&_=RANDOM';
            $actions[ $action_name ] = $action;
        }
        return $actions;
    }

    /**
     * AMP head
     */
    public function amp_post_template_head_handler() {
        require PIANO_PLUGIN_DIR . '/views/amp/script.php';
    }

    /**
     * AMP execute action
     */
    public function amp_execute_action_handler() {
        if ( is_home() && isset( $_GET[ AMP_ACTION ] )) {
            $action_name = esc_attr( $_GET[ AMP_ACTION ] );

            $amp_actions = apply_filters( 'piano_amp_actions', $this->amp_actions );

            if ( ! isset( $amp_actions[ $action_name ] ) ) {
                return;
            };

            $action = $amp_actions[ $action_name ];
            $action();

            status_header( 200 );
            die();
        }
    }

    private function amp_login_action() {
        require PIANO_PLUGIN_DIR . '/views/amp/login.php';
    }

    private function amp_logout_action() {
        require PIANO_PLUGIN_DIR . '/views/amp/logout.php';
    }
}