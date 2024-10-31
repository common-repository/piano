<?php

namespace Piano;

/**
 * Piano main class
 * @package Piano
 */
class Piano {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Content
     */
    private $content;

    /**
     * @var bool
     */
    private $is_privileged;

    public function __construct() {
        $this->settings = new Settings();

        $this->is_privileged =
            current_user_can('editor')
            || current_user_can('administrator');

        if ( ! is_admin() ) {
            $this->content = new Content( $this );
        } else {
            new Dashboard( $this );
        }
    }

    /**
     * Get settings
     * @param $option_name string
     * @return string|Settings
     */
    public function settings( $option_name = null ) {
        if ( $option_name != null ) {
            return $this->settings[ $option_name ];
        }
        return $this->settings;
    }

    /**
     * Get content
     * @return Content
     */
    public function content() {
        return $this->content;
    }

    /**
     * User has privileged permissions
     * @return bool
     */
    public function is_privileged() {
        return $this->is_privileged;
    }
}