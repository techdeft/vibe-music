<?php
/**
 * Admin Dashboard Pages for VIBE Music
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Admin {

    public function register() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        add_action( 'admin_post_vibe_save_settings', [ $this, 'save_settings' ] );
        add_action( 'admin_post_vibe_save_genre', [ $this, 'save_genre' ] );
        add_action( 'admin_post_vibe_delete_genre', [ $this, 'delete_genre' ] );
    }

    // -------------------------------------------------------------------------
    // ADMIN MENU
    // -------------------------------------------------------------------------

    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            'VIBE Music',
            'VIBE Music',
            'manage_options',
            'vibe-music',
            [ $this, 'dashboard_page' ],
            $this->get_svg_icon(),
            30
        );

        // Dashboard (same as main)
        add_submenu_page( 'vibe-music', 'Dashboard', 'Dashboard', 'manage_options', 'vibe-music', [ $this, 'dashboard_page' ] );

        // Artists
        add_submenu_page( 'vibe-music', 'Artists', 'Artists', 'manage_options', 'edit.php?post_type=vibe_artist' );

        // Albums
        add_submenu_page( 'vibe-music', 'Albums', 'Albums', 'manage_options', 'edit.php?post_type=vibe_album' );

        // Tracks
        add_submenu_page( 'vibe-music', 'Tracks', 'Tracks', 'manage_options', 'edit.php?post_type=vibe_track' );

        // Genres
        add_submenu_page( 'vibe-music', 'Genres', 'Genres', 'manage_options', 'vibe-genres', [ $this, 'genres_page' ] );

        // Settings
        add_submenu_page( 'vibe-music', 'Settings', 'Settings', 'manage_options', 'vibe-settings', [ $this, 'settings_page' ] );
    }

    // -------------------------------------------------------------------------
    // ENQUEUE SCRIPTS
    // -------------------------------------------------------------------------

    public function enqueue_admin_scripts( $hook ) {
        // Load on VIBE admin pages and CPT edit pages
        $vibe_hooks = [ 'toplevel_page_vibe-music', 'vibe-music_page_vibe-genres', 'vibe-music_page_vibe-settings' ];
        $cpt_hooks  = [ 'post.php', 'post-new.php' ];

        if ( in_array( $hook, $vibe_hooks ) || ( in_array( $hook, $cpt_hooks ) && in_array( get_post_type(), [ 'vibe_artist', 'vibe_album', 'vibe_track' ] ) ) ) {
            wp_enqueue_media();
        }

        if ( in_array( $hook, $vibe_hooks ) ) {
            wp_enqueue_style( 'vibe-admin', VIBE_PLUGIN_URL . 'assets/css/admin.css', [], VIBE_VERSION );
        }
    }

    // -------------------------------------------------------------------------
    // DASHBOARD PAGE
    // -------------------------------------------------------------------------

    public function dashboard_page() {
        $artist_count = wp_count_posts( 'vibe_artist' )->publish ?? 0;
        $album_count  = wp_count_posts( 'vibe_album' )->publish ?? 0;
        $track_count  = wp_count_posts( 'vibe_track' )->publish ?? 0;
        $slug         = get_option( 'vibe_slug', 'vibe' );
        $player_name  = get_option( 'vibe_player_name', 'VIBE' );
        $site_url     = site_url( '/' . $slug );

        $recent_tracks = get_posts( [
            'post_type'   => 'vibe_track',
            'numberposts' => 5,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ] );
        ?>
        <div class="wrap vibe-admin-wrap">
            <div class="vibe-admin-header">
                <div class="vibe-logo">
                    <span class="vibe-logo-text"><?php echo esc_html( $player_name ); ?></span>
                    <span class="vibe-version">v<?php echo VIBE_VERSION; ?></span>
                </div>
                <div class="vibe-header-actions">
                    <a href="<?php echo esc_url( $site_url ); ?>" target="_blank" class="vibe-btn vibe-btn-outline">
                        <span class="dashicons dashicons-external"></span> View Player
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=vibe_track' ) ); ?>" class="vibe-btn vibe-btn-primary">
                        <span class="dashicons dashicons-plus"></span> Add Track
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="vibe-stats-grid">
                <div class="vibe-stat-card">
                    <div class="vibe-stat-icon dashicons dashicons-microphone"></div>
                    <div class="vibe-stat-content">
                        <div class="vibe-stat-number"><?php echo esc_html( $artist_count ); ?></div>
                        <div class="vibe-stat-label">Artists</div>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vibe_artist' ) ); ?>" class="vibe-stat-link">Manage →</a>
                </div>
                <div class="vibe-stat-card">
                    <div class="vibe-stat-icon dashicons dashicons-album"></div>
                    <div class="vibe-stat-content">
                        <div class="vibe-stat-number"><?php echo esc_html( $album_count ); ?></div>
                        <div class="vibe-stat-label">Albums</div>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vibe_album' ) ); ?>" class="vibe-stat-link">Manage →</a>
                </div>
                <div class="vibe-stat-card">
                    <div class="vibe-stat-icon dashicons dashicons-format-audio"></div>
                    <div class="vibe-stat-content">
                        <div class="vibe-stat-number"><?php echo esc_html( $track_count ); ?></div>
                        <div class="vibe-stat-label">Tracks</div>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vibe_track' ) ); ?>" class="vibe-stat-link">Manage →</a>
                </div>
                <div class="vibe-stat-card vibe-stat-card--url">
                    <div class="vibe-stat-icon dashicons dashicons-admin-links"></div>
                    <div class="vibe-stat-content">
                        <div class="vibe-stat-url"><?php echo esc_html( $site_url ); ?></div>
                        <div class="vibe-stat-label">Player URL</div>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=vibe-settings' ) ); ?>" class="vibe-stat-link">Change →</a>
                </div>
            </div>

            <!-- Recent Tracks -->
            <div class="vibe-section">
                <div class="vibe-section-header">
                    <h2>Recent Tracks</h2>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vibe_track' ) ); ?>" class="vibe-link">View all →</a>
                </div>
                <table class="vibe-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Album</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $recent_tracks as $track ) :
                            $artist_id   = get_post_meta( $track->ID, '_vibe_track_artist', true );
                            $album_id    = get_post_meta( $track->ID, '_vibe_track_album', true );
                            $duration    = get_post_meta( $track->ID, '_vibe_track_duration', true );
                            $artist_name = $artist_id ? get_the_title( $artist_id ) : '—';
                            $album_name  = $album_id  ? get_the_title( $album_id )  : '—';
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html( $track->post_title ); ?></strong></td>
                            <td><?php echo esc_html( $artist_name ); ?></td>
                            <td><?php echo esc_html( $album_name ); ?></td>
                            <td><?php echo esc_html( $duration ?: '—' ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( get_edit_post_link( $track->ID ) ); ?>" class="vibe-action-link">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if ( empty( $recent_tracks ) ) : ?>
                        <tr><td colspan="5" class="vibe-empty-state">No tracks yet. <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=vibe_track' ) ); ?>">Add your first track →</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // GENRES PAGE
    // -------------------------------------------------------------------------

    public function genres_page() {
        $genres = get_terms( [ 'taxonomy' => 'vibe_genre', 'hide_empty' => false ] );
        ?>
        <div class="wrap vibe-admin-wrap">
            <div class="vibe-admin-header">
                <h1>Genres</h1>
            </div>

            <div class="vibe-two-col">
                <!-- Add Genre Form -->
                <div class="vibe-card">
                    <h2>Add New Genre</h2>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( 'vibe_save_genre', 'vibe_genre_nonce' ); ?>
                        <input type="hidden" name="action" value="vibe_save_genre" />
                        <div class="vibe-form-group">
                            <label for="genre_name">Genre Name</label>
                            <input type="text" id="genre_name" name="genre_name" required class="regular-text" placeholder="e.g. Hip-Hop, Jazz, Electronic..." />
                        </div>
                        <div class="vibe-form-group">
                            <label for="genre_slug">Slug (optional)</label>
                            <input type="text" id="genre_slug" name="genre_slug" class="regular-text" placeholder="auto-generated if left blank" />
                        </div>
                        <div class="vibe-form-group">
                            <label for="genre_description">Description</label>
                            <textarea id="genre_description" name="genre_description" rows="3" class="large-text"></textarea>
                        </div>
                        <button type="submit" class="vibe-btn vibe-btn-primary">Add Genre</button>
                    </form>
                </div>

                <!-- Genres List -->
                <div class="vibe-card">
                    <h2>All Genres</h2>
                    <table class="vibe-table">
                        <thead>
                            <tr><th>Name</th><th>Slug</th><th>Tracks</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $genres as $genre ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $genre->name ); ?></strong></td>
                                <td><code><?php echo esc_html( $genre->slug ); ?></code></td>
                                <td><?php echo esc_html( $genre->count ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'edit-tags.php?action=edit&taxonomy=vibe_genre&tag_ID=' . $genre->term_id ) ); ?>" class="vibe-action-link">Edit</a>
                                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
                                        <?php wp_nonce_field( 'vibe_delete_genre_' . $genre->term_id, 'vibe_delete_nonce' ); ?>
                                        <input type="hidden" name="action" value="vibe_delete_genre" />
                                        <input type="hidden" name="term_id" value="<?php echo esc_attr( $genre->term_id ); ?>" />
                                        <button type="submit" class="vibe-action-link vibe-action-link--danger" onclick="return confirm('Delete this genre?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ( empty( $genres ) ) : ?>
                            <tr><td colspan="4" class="vibe-empty-state">No genres yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // SETTINGS PAGE
    // -------------------------------------------------------------------------

    public function settings_page() {
        $slug        = get_option( 'vibe_slug', 'vibe' );
        $player_name = get_option( 'vibe_player_name', 'VIBE' );
        $tagline     = get_option( 'vibe_tagline', 'Live the Sound' );
        $primary_color = get_option( 'vibe_primary_color', '#FF0000' );
        $allow_reg   = get_option( 'vibe_allow_registration', '0' );
        $saved       = isset( $_GET['saved'] ) && $_GET['saved'] === '1';
        ?>
        <div class="wrap vibe-admin-wrap">
            <div class="vibe-admin-header">
                <h1>Settings</h1>
            </div>

            <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p>✅ Settings saved successfully!</p></div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'vibe_save_settings', 'vibe_settings_nonce' ); ?>
                <input type="hidden" name="action" value="vibe_save_settings" />

                <div class="vibe-settings-grid">
                    <!-- Frontend Settings -->
                    <div class="vibe-card">
                        <h2>🎵 Frontend Configuration</h2>
                        <div class="vibe-form-group">
                            <label for="vibe_slug"><strong>Player Slug</strong></label>
                            <p class="description">The URL path where the music player will appear. E.g., setting it to <code>music</code> makes it available at <code><?php echo esc_url( site_url( '/music' ) ); ?></code></p>
                            <div class="vibe-slug-input">
                                <span class="vibe-slug-prefix"><?php echo esc_html( site_url( '/' ) ); ?></span>
                                <input type="text" id="vibe_slug" name="vibe_slug" value="<?php echo esc_attr( $slug ); ?>" class="regular-text" placeholder="vibe" pattern="[a-z0-9\-]+" required />
                            </div>
                            <p class="description">Use lowercase letters, numbers, and hyphens only.</p>
                        </div>
                        <div class="vibe-form-group">
                            <label for="vibe_player_name"><strong>Player Name</strong></label>
                            <input type="text" id="vibe_player_name" name="vibe_player_name" value="<?php echo esc_attr( $player_name ); ?>" class="regular-text" placeholder="VIBE" />
                        </div>
                        <div class="vibe-form-group">
                            <label for="vibe_tagline"><strong>Tagline</strong></label>
                            <input type="text" id="vibe_tagline" name="vibe_tagline" value="<?php echo esc_attr( $tagline ); ?>" class="regular-text" placeholder="Live the Sound" />
                        </div>
                    </div>

                    <!-- Appearance Settings -->
                    <div class="vibe-card">
                        <h2>🎨 Appearance</h2>
                        <div class="vibe-form-group">
                            <label for="vibe_primary_color"><strong>Primary Accent Color</strong></label>
                            <p class="description">The main brand color used in the player UI.</p>
                            <input type="color" id="vibe_primary_color" name="vibe_primary_color" value="<?php echo esc_attr( $primary_color ); ?>" />
                        </div>
                        <div class="vibe-form-group">
                            <label><strong>Allow User Registration</strong></label>
                            <label>
                                <input type="checkbox" name="vibe_allow_registration" value="1" <?php checked( $allow_reg, '1' ); ?> />
                                Show Login / Register pages in the player
                            </label>
                        </div>
                    </div>
                </div>

                <p class="submit">
                    <button type="submit" class="vibe-btn vibe-btn-primary vibe-btn-lg">Save Settings</button>
                </p>
            </form>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // FORM HANDLERS
    // -------------------------------------------------------------------------

    public function save_settings() {
        if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['vibe_settings_nonce'], 'vibe_save_settings' ) ) {
            wp_die( 'Unauthorized' );
        }

        $slug = sanitize_title( $_POST['vibe_slug'] ?? 'vibe' );
        update_option( 'vibe_slug', $slug );
        update_option( 'vibe_player_name', sanitize_text_field( $_POST['vibe_player_name'] ?? 'VIBE' ) );
        update_option( 'vibe_tagline', sanitize_text_field( $_POST['vibe_tagline'] ?? 'Live the Sound' ) );
        update_option( 'vibe_primary_color', sanitize_hex_color( $_POST['vibe_primary_color'] ?? '#FF0000' ) );
        update_option( 'vibe_allow_registration', isset( $_POST['vibe_allow_registration'] ) ? '1' : '0' );

        flush_rewrite_rules();
        wp_redirect( admin_url( 'admin.php?page=vibe-settings&saved=1' ) );
        exit;
    }

    public function save_genre() {
        if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['vibe_genre_nonce'], 'vibe_save_genre' ) ) {
            wp_die( 'Unauthorized' );
        }

        $name = sanitize_text_field( $_POST['genre_name'] ?? '' );
        $slug = sanitize_title( $_POST['genre_slug'] ?? $name );
        $desc = sanitize_textarea_field( $_POST['genre_description'] ?? '' );

        if ( $name ) {
            wp_insert_term( $name, 'vibe_genre', [ 'slug' => $slug, 'description' => $desc ] );
        }

        wp_redirect( admin_url( 'admin.php?page=vibe-genres' ) );
        exit;
    }

    public function delete_genre() {
        $term_id = absint( $_POST['term_id'] ?? 0 );
        if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['vibe_delete_nonce'], 'vibe_delete_genre_' . $term_id ) ) {
            wp_die( 'Unauthorized' );
        }
        wp_delete_term( $term_id, 'vibe_genre' );
        wp_redirect( admin_url( 'admin.php?page=vibe-genres' ) );
        exit;
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function get_svg_icon() {
        return 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>' );
    }
}
