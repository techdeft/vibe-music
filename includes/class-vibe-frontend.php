<?php
/**
 * Frontend Handler for VIBE Music - Virtual Page & Asset Loading
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Frontend {

    public function register() {
        add_action( 'init', [ $this, 'add_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
        add_action( 'template_redirect', [ $this, 'handle_virtual_page' ], 5 ); // Earlier priority
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_filter( 'script_loader_tag', [ $this, 'add_module_type' ], 10, 3 );
    }

    public function add_module_type( $tag, $handle, $src ) {
        if ( 'vibe-app' !== $handle ) {
            return $tag;
        }
        $tag = '<script type="module" src="' . esc_url( $src ) . '" id="vibe-app-js"></script>';
        return $tag;
    }

    public function add_rewrite_rules() {
        $slug = get_option( 'vibe_slug', 'vibe' );
        if ( ! $slug ) $slug = 'vibe';

        // More robust rules
        add_rewrite_rule( $slug . '/?$', 'index.php?vibe_page=1', 'top' );
        add_rewrite_rule( $slug . '/(.*)/?$', 'index.php?vibe_page=1', 'top' );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'vibe_page';
        return $vars;
    }

    public function handle_virtual_page() {
        if ( ! $this->is_vibe_request() ) {
            return;
        }

        // Force 200 status
        status_header( 200 );

        // Render the app shell template
        include VIBE_PLUGIN_DIR . 'templates/app-container.php';
        exit;
    }

    private function is_vibe_request() {
        $slug = get_option( 'vibe_slug', 'vibe' );
        $is_home_opt = get_option( 'vibe_is_home', '0' ) === '1';

        // Check query var
        if ( get_query_var( 'vibe_page' ) ) return true;

        // Check if home page override is enabled
        if ( $is_home_opt && ( is_front_page() || is_home() ) ) {
             // Ensure it's not a REST request or something else
             if ( strpos( $_SERVER['REQUEST_URI'] ?? '', 'wp-json' ) === false ) {
                 return true;
             }
        }

        // Check slug in URI
        $request_uri = untrailingslashit( $_SERVER['REQUEST_URI'] ?? '' );
        $base_path = untrailingslashit( (string) parse_url( site_url(), PHP_URL_PATH ) );
        $vibe_path = $base_path . '/' . $slug;
        
        if ( $slug && strpos( $request_uri, $vibe_path ) === 0 ) {
            return true;
        }

        return false;
    }

    public function enqueue_assets() {
        if ( ! $this->is_vibe_request() ) {
            return;
        }

        // Enqueue Vite-built assets
        $dist_url = VIBE_DIST_URL;
        $manifest_file = VIBE_DIST_DIR . '.vite/manifest.json';
        
        $css_file = 'assets/index.css';
        $js_file  = 'assets/index.js';

        if ( file_exists( $manifest_file ) ) {
            $manifest = json_decode( file_get_contents( $manifest_file ), true );
            if ( isset( $manifest['index.html'] ) ) {
                $entry = $manifest['index.html'];
                if ( isset( $entry['file'] ) )  $js_file  = $entry['file'];
                if ( isset( $entry['css'][0] ) ) $css_file = $entry['css'][0];
            }
        }

        wp_enqueue_style( 'vibe-app', $dist_url . $css_file, [], VIBE_VERSION );
        wp_enqueue_script( 'vibe-app', $dist_url . $js_file, [], VIBE_VERSION, true );

        $slug = get_option( 'vibe_slug', 'vibe' );

        // Localize config for the Vue app
        wp_localize_script( 'vibe-app', 'VibeConfig', [
            'apiBase'      => esc_url( rest_url( 'vibe-music/v1' ) ),
            'nonce'        => wp_create_nonce( 'wp_rest' ),
            'siteUrl'      => esc_url( site_url() ),
            'pluginUrl'    => esc_url( VIBE_PLUGIN_URL ),
            'playerName'   => get_option( 'vibe_player_name', 'VIBE' ),
            'tagline'      => get_option( 'vibe_tagline', 'Live the Sound' ),
            'primaryColor' => get_option( 'vibe_primary_color', '#FF0000' ),
            'slug'         => $slug,
            'allowReg'     => get_option( 'vibe_allow_registration', '0' ) === '1',
            'donationLink' => get_option( 'vibe_donation_link', '' ),
        ] );
    }
}
