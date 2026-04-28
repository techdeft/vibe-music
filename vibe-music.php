<?php
/**
 * Plugin Name: VIBE Music
 * Plugin URI:  https://vibe.music
 * Description: A premium Spotify-like music streaming plugin for WordPress. Manage artists, albums, tracks & stream music with a beautiful, modern UI.
 * Version:     1.0.1
 * Author:      Immtech Global
 * Text Domain: vibe-music
 * License:     GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin Constants
define('VIBE_VERSION', '1.0.1');
define('VIBE_PLUGIN_FILE', __FILE__);
define('VIBE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VIBE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VIBE_DIST_DIR', VIBE_PLUGIN_DIR . 'vibe-app/dist/');
define('VIBE_DIST_URL', VIBE_PLUGIN_URL . 'vibe-app/dist/');

// Autoload classes
require_once VIBE_PLUGIN_DIR . 'includes/class-vibe-cpt.php';
require_once VIBE_PLUGIN_DIR . 'includes/class-vibe-admin.php';
require_once VIBE_PLUGIN_DIR . 'includes/class-vibe-api.php';
require_once VIBE_PLUGIN_DIR . 'includes/class-vibe-auth.php';
require_once VIBE_PLUGIN_DIR . 'includes/class-vibe-frontend.php';

/**
 * Main plugin initialization
 */
function vibe_music_init()
{
    // Register Custom Post Types & Taxonomies
    $cpt = new Vibe_CPT();
    $cpt->register();

    // Admin dashboard pages
    if (is_admin()) {
        $admin = new Vibe_Admin();
        $admin->register();
    }

    // REST API endpoints
    $api = new Vibe_API();
    $api->register();

    // Auth & Playlist API
    $auth = new Vibe_Auth();
    $auth->register();

    // Frontend virtual page handler
    $frontend = new Vibe_Frontend();
    $frontend->register();

    // Ensure subscriber caps are granted
    vibe_grant_subscriber_caps();
}
add_action('plugins_loaded', 'vibe_music_init');

/**
 * Plugin activation
 */
function vibe_music_activate()
{
    // Create CPTs so we can flush rewrite rules
    $cpt = new Vibe_CPT();
    $cpt->register();
    flush_rewrite_rules();

    // Set default options
    if (!get_option('vibe_slug')) {
        update_option('vibe_slug', 'vibe');
    }
    if (!get_option('vibe_player_name')) {
        update_option('vibe_player_name', 'VIBE');
    }
    if (get_option('vibe_allow_registration') === false) {
        update_option('vibe_allow_registration', '1');
    }

    // Grant capabilities
    vibe_grant_subscriber_caps();
}

/**
 * Grant playlist capabilities to subscribers
 */
function vibe_grant_subscriber_caps() {
    $role = get_role('subscriber');
    if ($role) {
        $role->add_cap('edit_vibe_playlists');
        $role->add_cap('publish_vibe_playlists');
        $role->add_cap('delete_vibe_playlists');
        $role->add_cap('read_vibe_playlist');
        $role->add_cap('edit_published_vibe_playlists');
        $role->add_cap('delete_published_vibe_playlists');
    }
}
register_activation_hook(__FILE__, 'vibe_music_activate');

/**
 * Plugin deactivation
 */
function vibe_music_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'vibe_music_deactivate');
