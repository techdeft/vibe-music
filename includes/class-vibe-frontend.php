<?php
/**
 * Frontend Handler for VIBE Music - Virtual Page & Asset Loading
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Frontend {

    public function register() {
        add_action( 'init', [ $this, 'add_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
        add_action( 'template_redirect', [ $this, 'handle_virtual_page' ] );
    }

    public function add_rewrite_rules() {
        $slug = get_option( 'vibe_slug', 'vibe' );

        // Match slug and any sub-paths (for SPA routing)
        add_rewrite_rule( '^' . preg_quote( $slug, '/' ) . '(/.*)?$', 'index.php?vibe_page=1', 'top' );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'vibe_page';
        return $vars;
    }

    public function handle_virtual_page() {
        if ( ! get_query_var( 'vibe_page' ) ) {
            return;
        }

        // Enqueue assets
        $this->enqueue_assets();

        // Render the app shell template
        include VIBE_PLUGIN_DIR . 'templates/app-container.php';
        exit;
    }

    private function enqueue_assets() {
        // Enqueue Vite-built assets
        $dist_path = VIBE_DIST_DIR;
        $dist_url  = VIBE_DIST_URL;

        // Try to read the Vite manifest to get hashed filenames
        $manifest_file = $dist_path . '.vite/manifest.json';
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

        // Localize config for the Vue app
        wp_localize_script( 'vibe-app', 'VibeConfig', [
            'apiBase'      => esc_url( rest_url( 'vibe-music/v1' ) ),
            'nonce'        => wp_create_nonce( 'wp_rest' ),
            'siteUrl'      => esc_url( site_url() ),
            'pluginUrl'    => esc_url( VIBE_PLUGIN_URL ),
            'playerName'   => get_option( 'vibe_player_name', 'VIBE' ),
            'tagline'      => get_option( 'vibe_tagline', 'Live the Sound' ),
            'primaryColor' => get_option( 'vibe_primary_color', '#FF0000' ),
            'slug'         => get_option( 'vibe_slug', 'vibe' ),
            'allowReg'     => get_option( 'vibe_allow_registration', '0' ) === '1',
        ] );
    }
}
