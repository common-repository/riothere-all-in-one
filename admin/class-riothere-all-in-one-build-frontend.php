<?php

/**
 * The admin-specific ability to build the Frontend.
 */
class Riothere_All_In_One_Build_Frontend
{
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Initial hooks for loading the plugin admin UI
        add_action('admin_enqueue_scripts', array($this, 'riothere_enqueue_scripts'));

        // ajax call for the admin UI
        add_action('wp_ajax_build_frontend', array($this, 'riothere_build_frontend_ajax'));
    }

    public function riothere_enqueue_scripts()
    {
        wp_enqueue_script(
            $this->plugin_name . 'build-frontend',
            plugin_dir_url(__FILE__) . 'js/riothere-all-in-one-build-frontend.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name . 'build-frontend',
            'riothere_admin_build_frontend_global',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'build_frontend_nonce' => wp_create_nonce('build-frontend'),
            )
        );
    }

    // Invoked from Admin Panel via ajax in riothere-all-in-one-build-frontend.js
    public function riothere_build_frontend_ajax()
    {
        check_ajax_referer('build-frontend', 'ajax_nonce');

        $result = [
            'success' => true,
            'data' => [],
        ];

        // Disabled for security reasons. Build manually instead
        // $result['data'] = shell_exec("cd /var/www/frontend && sudo yarn deploy 2>&1");

        echo json_encode($result);
        die();
    }
}
