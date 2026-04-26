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
        add_action( 'admin_post_vibe_save_artist', [ $this, 'save_artist' ] );
        add_action( 'admin_post_vibe_save_album', [ $this, 'save_album' ] );
        add_action( 'admin_post_vibe_save_track', [ $this, 'save_track' ] );
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

        // Studio (Unified Creator)
        add_submenu_page( 'vibe-music', 'VIBE Studio', 'VIBE Studio', 'manage_options', 'vibe-studio', [ $this, 'studio_page' ] );


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
        $vibe_hooks = [ 
            'toplevel_page_vibe-music', 
            'vibe-music_page_vibe-studio', 
            'vibe-music_page_vibe-genres', 
            'vibe-music_page_vibe-settings' 
        ];
        $cpt_hooks  = [ 'post.php', 'post-new.php' ];

        if ( in_array( $hook, $vibe_hooks ) || ( in_array( $hook, $cpt_hooks ) && in_array( get_post_type(), [ 'vibe_artist', 'vibe_album', 'vibe_track' ] ) ) ) {
            wp_enqueue_media();
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
        <div class="vibe-admin-wrap">
            <div class="vibe-hero">
                <div class="vibe-hero-content">
                    <h1><?php echo esc_html( $player_name ); ?> Dashboard</h1>
                    <p>Welcome back! Here's what's happening with your music.</p>
                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vibe-studio&tab=tracks' ) ); ?>" class="vibe-btn vibe-btn-primary">Add New Track</a>
                        <a href="<?php echo esc_url( $site_url ); ?>" target="_blank" class="vibe-btn vibe-btn-outline">View Player</a>
                    </div>
                </div>
            </div>

            <div class="vibe-canvas">
                <?php if ( ! get_option( 'permalink_structure' ) ) : ?>
                    <div class="notice notice-warning inline" style="margin-bottom: 30px;"><p>⚠️ <strong>Important:</strong> Pretty permalinks required. Update in <a href="<?php echo admin_url('options-permalink.php'); ?>">Settings</a>.</p></div>
                <?php endif; ?>

                <div class="vibe-stats-grid">
                    <div class="vibe-stat-card">
                        <div class="vibe-stat-icon dashicons dashicons-microphone"></div>
                        <div class="vibe-stat-content">
                            <div class="vibe-stat-number"><?php echo esc_html( $artist_count ); ?></div>
                            <div class="vibe-stat-label">Artists</div>
                        </div>
                    </div>
                    <div class="vibe-stat-card">
                        <div class="vibe-stat-icon dashicons dashicons-album"></div>
                        <div class="vibe-stat-content">
                            <div class="vibe-stat-number"><?php echo esc_html( $album_count ); ?></div>
                            <div class="vibe-stat-label">Albums</div>
                        </div>
                    </div>
                    <div class="vibe-stat-card">
                        <div class="vibe-stat-icon dashicons dashicons-format-audio"></div>
                        <div class="vibe-stat-content">
                            <div class="vibe-stat-number"><?php echo esc_html( $track_count ); ?></div>
                            <div class="vibe-stat-label">Tracks</div>
                        </div>
                    </div>
                    <div class="vibe-stat-card">
                        <div class="vibe-stat-icon dashicons dashicons-admin-links"></div>
                        <div class="vibe-stat-content">
                            <div class="vibe-stat-number" style="font-size:12px; word-break:break-all;"><?php echo esc_html( $slug ); ?></div>
                            <div class="vibe-stat-label">Player Slug</div>
                        </div>
                    </div>
                </div>

                <div class="vibe-card">
                    <h2><span class="dashicons dashicons-backup"></span> Recent Activity</h2>
                    <table class="vibe-table">
                        <thead>
                            <tr><th>Title</th><th>Type</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_tracks as $track ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $track->post_title ); ?></strong></td>
                                <td>Track</td>
                                <td><?php echo get_the_date( '', $track->ID ); ?></td>
                                <td><a href="<?php echo get_edit_post_link($track->ID); ?>" class="vibe-action-link">Edit</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // STUDIO PAGE (Custom Creator)
    // -------------------------------------------------------------------------

    public function studio_page() {
        $active_tab = $_GET['tab'] ?? 'artists';
        $edit_id    = absint( $_GET['edit'] ?? 0 );
        $edit_post  = $edit_id ? get_post( $edit_id ) : null;

        $tabs = [
            'artists' => [ 'label' => 'Artists', 'icon' => 'dashicons-microphone', 'desc' => 'Manage your music creators and their profiles.' ],
            'albums'  => [ 'label' => 'Albums',  'icon' => 'dashicons-album', 'desc' => 'Group tracks into professional albums and EPs.' ],
            'tracks'  => [ 'label' => 'Tracks',  'icon' => 'dashicons-format-audio', 'desc' => 'Upload and manage your high-quality audio files.' ],
        ];
        ?>
        <div class="vibe-admin-wrap">
            <!-- Hero Canvas Header -->
            <div class="vibe-hero">
                <div class="vibe-hero-content">
                    <h1>VIBE Studio</h1>
                    <p><?php echo esc_html( $tabs[$active_tab]['desc'] ); ?></p>
                </div>
            </div>

            <!-- Floating Tabs -->
            <nav class="vibe-tabs-floating">
                <?php foreach ( $tabs as $id => $data ) : ?>
                    <a href="?page=vibe-studio&tab=<?php echo $id; ?>" class="<?php echo $active_tab === $id ? 'active' : ''; ?>">
                        <span class="dashicons <?php echo $data['icon']; ?>"></span>
                        <?php echo esc_html( $data['label'] ); ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=vibe-music' ) ); ?>" style="margin-left:auto; opacity:0.7;">
                    <span class="dashicons dashicons-dashboard"></span> Dashboard
                </a>
            </nav>

            <div class="vibe-canvas">
                <?php if ( isset( $_GET['saved'] ) ) : ?>
                    <div class="notice notice-success is-dismissible" style="margin-bottom: 40px;"><p>✅ Change saved successfully to the database.</p></div>
                <?php endif; ?>

                <div class="vibe-studio-content">
                    <?php
                    switch ( $active_tab ) {
                        case 'artists': $this->render_studio_artists( $edit_post ); break;
                        case 'albums':  $this->render_studio_albums( $edit_post ); break;
                        case 'tracks':  $this->render_studio_tracks( $edit_post ); break;
                    }
                    ?>
                </div>
            </div>
        </div>
        <script>
        function vibeSelectMedia(title, type, callback) {
            var frame = wp.media({
                title: title,
                button: { text: 'Select' },
                library: { type: type },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                callback(attachment);
            });
            frame.open();
        }
        </script>
        <?php
    }

    private function render_studio_artists( $edit_post = null ) {
        $artists = get_posts( [ 'post_type' => 'vibe_artist', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        $is_edit = $edit_post && $edit_post->post_type === 'vibe_artist';
        
        $image_id  = $is_edit ? get_post_thumbnail_id( $edit_post->ID ) : '';
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
        $listeners = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_artist_monthly_listeners', true ) : '';
        $bio       = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_artist_bio', true ) : '';
        $spotify   = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_artist_spotify', true ) : '';
        $featured  = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_artist_featured', true ) : '0';
        ?>
        <div class="vibe-two-col">
            <div class="vibe-card">
                <h2><?php echo $is_edit ? 'Edit Artist' : 'Add New Artist'; ?></h2>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'vibe_save_artist', 'vibe_artist_nonce' ); ?>
                    <input type="hidden" name="action" value="vibe_save_artist" />
                    <?php if ( $is_edit ) : ?><input type="hidden" name="artist_id" value="<?php echo $edit_post->ID; ?>" /><?php endif; ?>
                    
                    <div class="vibe-form-group">
                        <label>Artist Name</label>
                        <input type="text" name="artist_title" required class="large-text" placeholder="e.g. The Weeknd" value="<?php echo $is_edit ? esc_attr( $edit_post->post_title ) : ''; ?>" />
                    </div>
                    <div class="vibe-form-group">
                        <label>Profile Image</label>
                        <div class="vibe-media-uploader">
                            <div class="vibe-media-preview" id="artist_image_preview">
                                <?php if ( $image_url ) : ?><img src="<?php echo $image_url; ?>" style="width:100%;height:100%;object-fit:cover;" /><?php endif; ?>
                            </div>
                            <div class="vibe-media-actions">
                                <input type="hidden" name="artist_image_id" id="artist_image_id" value="<?php echo $image_id; ?>" />
                                <button type="button" class="vibe-btn vibe-btn-outline" onclick="vibeSelectMedia('Select Artist Image', 'image', function(a){ 
                                    document.getElementById('artist_image_id').value = a.id;
                                    document.getElementById('artist_image_preview').innerHTML = '<img src=\''+a.url+'\' style=\'width:100%;height:100%;object-fit:cover;\' />';
                                })">Choose Image</button>
                            </div>
                        </div>
                    </div>
                    <div class="vibe-form-group">
                        <label>Monthly Listeners</label>
                        <input type="number" name="artist_listeners" class="regular-text" placeholder="e.g. 5000000" value="<?php echo esc_attr( $listeners ); ?>" />
                    </div>
                    <div class="vibe-form-group">
                        <label>Bio</label>
                        <textarea name="artist_bio" rows="4" class="large-text" placeholder="Short biography..."><?php echo esc_textarea( $bio ); ?></textarea>
                    </div>
                    <div class="vibe-form-group">
                        <label>Spotify URL</label>
                        <input type="url" name="artist_spotify" class="large-text" value="<?php echo esc_attr( $spotify ); ?>" />
                    </div>
                    <div class="vibe-form-group">
                        <label><input type="checkbox" name="artist_featured" value="1" <?php checked( $featured, '1' ); ?> /> <strong>Featured Artist</strong> (Show on Discovery)</label>
                    </div>
                    <button type="submit" class="vibe-btn vibe-btn-primary"><?php echo $is_edit ? 'Update Artist' : 'Create Artist'; ?></button>
                    <?php if ( $is_edit ) : ?><a href="?page=vibe-studio&tab=artists" class="vibe-btn vibe-btn-outline" style="margin-left:10px;">Cancel</a><?php endif; ?>
                </form>
            </div>
            <div class="vibe-card">
                <h2>Existing Artists</h2>
                <table class="vibe-table">
                    <thead><tr><th>Image</th><th>Name</th><th>Listeners</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ( $artists as $artist ) : 
                            $img = get_the_post_thumbnail_url( $artist->ID, 'thumbnail' );
                            $listeners = get_post_meta( $artist->ID, '_vibe_artist_monthly_listeners', true );
                        ?>
                        <tr>
                            <td width="50"><img src="<?php echo $img ?: 'https://via.placeholder.com/40'; ?>" width="40" height="40" style="border-radius:4px;object-fit:cover;" /></td>
                            <td><strong><?php echo esc_html( $artist->post_title ); ?></strong></td>
                            <td><?php echo number_format( $listeners ?: 0 ); ?></td>
                            <td><a href="?page=vibe-studio&tab=artists&edit=<?php echo $artist->ID; ?>" class="vibe-action-link">Edit</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function render_studio_albums( $edit_post = null ) {
        $artists = get_posts( [ 'post_type' => 'vibe_artist', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        $albums  = get_posts( [ 'post_type' => 'vibe_album', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC' ] );
        $is_edit = $edit_post && $edit_post->post_type === 'vibe_album';

        $artist_id    = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_album_artist', true ) : '';
        $release_date = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_album_release_date', true ) : '';
        $featured     = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_album_featured', true ) : '0';
        $image_id     = $is_edit ? get_post_thumbnail_id( $edit_post->ID ) : '';
        $image_url    = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
        ?>
        <div class="vibe-two-col">
            <div class="vibe-card">
                <h2><?php echo $is_edit ? 'Edit Album' : 'Add New Album'; ?></h2>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'vibe_save_album', 'vibe_album_nonce' ); ?>
                    <input type="hidden" name="action" value="vibe_save_album" />
                    <?php if ( $is_edit ) : ?><input type="hidden" name="album_id" value="<?php echo $edit_post->ID; ?>" /><?php endif; ?>

                    <div class="vibe-form-group">
                        <label>Album Title</label>
                        <input type="text" name="album_title" required class="large-text" placeholder="e.g. After Hours" value="<?php echo $is_edit ? esc_attr( $edit_post->post_title ) : ''; ?>" />
                    </div>
                    <div class="vibe-form-group">
                        <label>Artist</label>
                        <select name="album_artist" required class="large-text">
                            <option value="">— Select Artist —</option>
                            <?php foreach ( $artists as $a ) : ?>
                                <option value="<?php echo $a->ID; ?>" <?php selected( $artist_id, $a->ID ); ?>><?php echo esc_html( $a->post_title ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="vibe-form-group">
                        <label>Cover Art</label>
                        <div class="vibe-media-uploader">
                            <div class="vibe-media-preview" id="album_image_preview">
                                <?php if ( $image_url ) : ?><img src="<?php echo $image_url; ?>" style="width:100%;height:100%;object-fit:cover;" /><?php endif; ?>
                            </div>
                            <div class="vibe-media-actions">
                                <input type="hidden" name="album_image_id" id="album_image_id" value="<?php echo $image_id; ?>" />
                                <button type="button" class="vibe-btn vibe-btn-outline" onclick="vibeSelectMedia('Select Album Cover', 'image', function(a){ 
                                    document.getElementById('album_image_id').value = a.id;
                                    document.getElementById('album_image_preview').innerHTML = '<img src=\''+a.url+'\' style=\'width:100%;height:100%;object-fit:cover;\' />';
                                })">Choose Cover</button>
                            </div>
                        </div>
                    </div>
                    <div class="vibe-form-group">
                        <label>Release Date</label>
                        <input type="date" name="album_date" class="regular-text" value="<?php echo esc_attr( $release_date ); ?>" />
                    </div>
                    <div class="vibe-form-group">
                        <label><input type="checkbox" name="album_featured" value="1" <?php checked( $featured, '1' ); ?> /> <strong>Featured Album</strong> (Show on Discovery)</label>
                    </div>
                    <button type="submit" class="vibe-btn vibe-btn-primary"><?php echo $is_edit ? 'Update Album' : 'Create Album'; ?></button>
                    <?php if ( $is_edit ) : ?><a href="?page=vibe-studio&tab=albums" class="vibe-btn vibe-btn-outline" style="margin-left:10px;">Cancel</a><?php endif; ?>
                </form>
            </div>
            <div class="vibe-card">
                <h2>Existing Albums</h2>
                <table class="vibe-table">
                    <thead><tr><th>Cover</th><th>Title</th><th>Artist</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ( $albums as $album ) : 
                            $img = get_the_post_thumbnail_url( $album->ID, 'thumbnail' );
                            $artist_id = get_post_meta( $album->ID, '_vibe_album_artist', true );
                            $artist_name = $artist_id ? get_the_title( $artist_id ) : '—';
                        ?>
                        <tr>
                            <td width="50"><img src="<?php echo $img ?: 'https://via.placeholder.com/40'; ?>" width="40" height="40" style="border-radius:4px;object-fit:cover;" /></td>
                            <td><strong><?php echo esc_html( $album->post_title ); ?></strong></td>
                            <td><?php echo esc_html( $artist_name ); ?></td>
                            <td><a href="?page=vibe-studio&tab=albums&edit=<?php echo $album->ID; ?>" class="vibe-action-link">Edit</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function render_studio_tracks( $edit_post = null ) {
        $artists = get_posts( [ 'post_type' => 'vibe_artist', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        $albums  = get_posts( [ 'post_type' => 'vibe_album', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        $genres  = get_terms( [ 'taxonomy' => 'vibe_genre', 'hide_empty' => false ] );
        $tracks  = get_posts( [ 'post_type' => 'vibe_track', 'numberposts' => 15, 'orderby' => 'date', 'order' => 'DESC' ] );
        $is_edit = $edit_post && $edit_post->post_type === 'vibe_track';

        $audio_url = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_track_audio_url', true ) : '';
        $artist_ids = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_track_artist' ) : [];
        $album_id  = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_track_album', true ) : '';
        $duration  = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_track_duration', true ) : '';
        $lyrics    = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_track_lyrics', true ) : '';
        $featured  = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_track_featured', true ) : '0';
        $image_id  = $is_edit ? get_post_thumbnail_id( $edit_post->ID ) : '';
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
        $current_genres = $is_edit ? wp_get_post_terms( $edit_post->ID, 'vibe_genre', [ 'fields' => 'ids' ] ) : [];
        $genre_id  = ! empty( $current_genres ) ? $current_genres[0] : '';
        ?>
        <div class="vibe-two-col">
            <div class="vibe-card">
                <h2><?php echo $is_edit ? 'Edit Track' : 'Add New Track'; ?></h2>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'vibe_save_track', 'vibe_track_nonce' ); ?>
                    <input type="hidden" name="action" value="vibe_save_track" />
                    <?php if ( $is_edit ) : ?><input type="hidden" name="track_id" value="<?php echo $edit_post->ID; ?>" /><?php endif; ?>

                    <div class="vibe-form-group">
                        <label>Track Title</label>
                        <input type="text" name="track_title" required class="large-text" placeholder="e.g. Blinding Lights" value="<?php echo $is_edit ? esc_attr( $edit_post->post_title ) : ''; ?>" />
                    </div>
                    <div class="vibe-form-group">
                        <label>Audio File</label>
                        <div class="vibe-media-uploader">
                            <div class="vibe-media-preview" id="track_audio_preview"><span class="dashicons dashicons-format-audio" style="font-size:30px;color:#ccc;"></span></div>
                            <div class="vibe-media-actions" style="flex:1;">
                                <input type="text" name="track_audio_url" id="track_audio_url" class="large-text" placeholder="URL or select file" required style="margin-bottom:10px;" value="<?php echo esc_attr( $audio_url ); ?>" />
                                <button type="button" class="vibe-btn vibe-btn-outline" onclick="vibeSelectMedia('Select Audio File', 'audio', function(a){ 
                                    document.getElementById('track_audio_url').value = a.url;
                                })">Choose Audio</button>
                            </div>
                        </div>
                    </div>
                    <div class="vibe-form-group">
                        <label>Video URL (YouTube or File)</label>
                        <div class="vibe-media-uploader">
                            <div class="vibe-media-preview" id="track_video_preview"><span class="dashicons dashicons-video-alt3" style="font-size:30px;color:#ccc;"></span></div>
                            <div class="vibe-media-actions" style="flex:1;">
                                <?php $video_url = $is_edit ? get_post_meta( $edit_post->ID, '_vibe_track_video_url', true ) : ''; ?>
                                <input type="text" name="track_video_url" id="track_video_url" class="large-text" placeholder="YouTube URL or select video file" style="margin-bottom:10px;" value="<?php echo esc_attr( $video_url ); ?>" />
                                <button type="button" class="vibe-btn vibe-btn-outline" onclick="vibeSelectMedia('Select Video File', 'video', function(a){ 
                                    document.getElementById('track_video_url').value = a.url;
                                })">Choose Video</button>
                            </div>
                        </div>
                    </div>
                    <div class="vibe-form-group">
                        <label>Track Cover (Optional)</label>
                        <p class="description" style="margin-bottom:10px;">Overrides album cover for this track only.</p>
                        <div class="vibe-media-uploader">
                            <div class="vibe-media-preview" id="track_image_preview">
                                <?php if ( $image_url ) : ?><img src="<?php echo $image_url; ?>" style="width:100%;height:100%;object-fit:cover;" /><?php endif; ?>
                            </div>
                            <div class="vibe-media-actions">
                                <input type="hidden" name="track_image_id" id="track_image_id" value="<?php echo $image_id; ?>" />
                                <button type="button" class="vibe-btn vibe-btn-outline" onclick="vibeSelectMedia('Select Track Cover', 'image', function(a){ 
                                    document.getElementById('track_image_id').value = a.id;
                                    document.getElementById('track_image_preview').innerHTML = '<img src=\''+a.url+'\' style=\'width:100%;height:100%;object-fit:cover;\' />';
                                })">Choose Cover</button>
                            </div>
                        </div>
                    </div>
                    <div class="vibe-form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="grid-column: span 2;">
                            <label>Artist(s)</label>
                            <div class="vibe-checkbox-list" style="max-height: 200px; overflow-y: auto; border: 1.5px solid var(--vibe-border); padding: 15px; background: #fcfcfc; border-radius: 16px; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                                <?php foreach ( $artists as $a ) : ?>
                                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                                        <input type="checkbox" name="track_artist[]" value="<?php echo $a->ID; ?>" <?php echo in_array( $a->ID, $artist_ids ) ? 'checked' : ''; ?> style="width: auto !important; margin: 0;" />
                                        <?php echo esc_html( $a->post_title ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="description" style="margin-top: 8px;">Select all contributors for this track.</p>
                        </div>
                        <div>
                            <label>Album (optional)</label>
                            <select name="track_album" class="large-text">
                                <option value="">— Single / No Album —</option>
                                <?php foreach ( $albums as $al ) : ?>
                                    <option value="<?php echo $al->ID; ?>" <?php selected( $album_id, $al->ID ); ?>><?php echo esc_html( $al->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="vibe-form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label>Genre</label>
                            <select name="track_genre" class="large-text">
                                <option value="">— Select Genre —</option>
                                <?php foreach ( $genres as $g ) : ?>
                                    <option value="<?php echo $g->term_id; ?>" <?php selected( $genre_id, $g->term_id ); ?>><?php echo esc_html( $g->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Duration (mm:ss)</label>
                            <input type="text" name="track_duration" class="large-text" placeholder="e.g. 3:24" value="<?php echo esc_attr( $duration ); ?>" />
                        </div>
                    </div>
                    <div class="vibe-form-group">
                        <label>Lyrics</label>
                        <textarea name="track_lyrics" rows="5" class="large-text" placeholder="Paste lyrics here..."><?php echo esc_textarea( $lyrics ); ?></textarea>
                    </div>
                    <div class="vibe-form-group">
                        <label><input type="checkbox" name="track_featured" value="1" <?php checked( $featured, '1' ); ?> /> <strong>Featured Track</strong> (Show on Discovery)</label>
                    </div>
                    <button type="submit" class="vibe-btn vibe-btn-primary"><?php echo $is_edit ? 'Update Track' : 'Create Track'; ?></button>
                    <?php if ( $is_edit ) : ?><a href="?page=vibe-studio&tab=tracks" class="vibe-btn vibe-btn-outline" style="margin-left:10px;">Cancel</a><?php endif; ?>
                </form>
            </div>
            <div class="vibe-card">
                <h2>Recent Tracks</h2>
                <table class="vibe-table">
                    <thead><tr><th>Title</th><th>Artist</th><th>Album</th><th>Streams</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ( $tracks as $track ) : 
                            $album_id  = get_post_meta( $track->ID, '_vibe_track_album', true );
                            $streams   = (int) get_post_meta( $track->ID, '_vibe_stream_count', true );
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html( $track->post_title ); ?></strong></td>
                            <td>
                                <?php 
                                $t_artist_ids = get_post_meta( $track->ID, '_vibe_track_artist' );
                                $names = [];
                                foreach ( $t_artist_ids as $tid ) {
                                    $names[] = get_the_title( $tid );
                                }
                                echo ! empty( $names ) ? esc_html( implode( ', ', $names ) ) : '—';
                                ?>
                            </td>
                            <td><?php echo $album_id ? esc_html( get_the_title( $album_id ) ) : '—'; ?></td>
                            <td><span class="vibe-badge"><?php echo number_format( $streams ); ?></span></td>
                            <td><a href="?page=vibe-studio&tab=tracks&edit=<?php echo $track->ID; ?>" class="vibe-action-link">Edit</a></td>
                        </tr>
                        <?php endforeach; ?>
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
        <div class="vibe-admin-wrap">
            <div class="vibe-hero">
                <div class="vibe-hero-content">
                    <h1>Genres</h1>
                    <p>Organize your music library with custom genres and categories.</p>
                </div>
            </div>

            <div class="vibe-canvas">
                <div class="vibe-two-col">
                    <!-- Add Genre Form -->
                    <div class="vibe-card">
                        <h2><span class="dashicons dashicons-plus"></span> Add New Genre</h2>
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
                            <button type="submit" class="vibe-btn vibe-btn-primary">Create Genre</button>
                        </form>
                    </div>

                    <!-- Genre List -->
                    <div class="vibe-card">
                        <h2><span class="dashicons dashicons-category"></span> Existing Genres</h2>
                        <table class="vibe-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $genres as $genre ) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $genre->name ); ?></strong></td>
                                    <td><code><?php echo esc_html( $genre->slug ); ?></code></td>
                                    <td><?php echo esc_html( $genre->count ); ?></td>
                                    <td>
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
        <div class="vibe-admin-wrap">
            <div class="vibe-hero">
                <div class="vibe-hero-content">
                    <h1>Settings</h1>
                    <p>Configure your player slug, branding, and user registration settings.</p>
                </div>
            </div>

            <div class="vibe-canvas">
                <?php if ( $saved ) : ?>
                    <div class="notice notice-success is-dismissible" style="margin-bottom: 40px;"><p>✅ Settings updated successfully!</p></div>
                <?php endif; ?>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'vibe_save_settings', 'vibe_settings_nonce' ); ?>
                    <input type="hidden" name="action" value="vibe_save_settings" />

                    <div class="vibe-two-col" style="grid-template-columns: 1fr 1fr;">
                        <!-- Frontend Settings -->
                        <div class="vibe-card">
                            <h2><span class="dashicons dashicons-admin-settings"></span> General Configuration</h2>
                            <div class="vibe-form-group">
                                <label for="vibe_slug">Player Slug</label>
                                <p class="description" style="margin-bottom:10px;">URL path for the music player.</p>
                                <div class="vibe-slug-input" style="display:flex; align-items:center; background:#fcfcfc; border:1.5px solid #eee; border-radius:12px; overflow:hidden;">
                                    <span style="padding:10px 15px; background:#eee; color:#666; font-size:12px; border-right:1px solid #ddd;"><?php echo esc_html( site_url( '/' ) ); ?></span>
                                    <input type="text" id="vibe_slug" name="vibe_slug" value="<?php echo esc_attr( $slug ); ?>" style="border:none !important; border-radius:0 !important; flex:1;" />
                                </div>
                            </div>
                            <div class="vibe-form-group">
                                <label for="vibe_player_name">Player Name</label>
                                <input type="text" id="vibe_player_name" name="vibe_player_name" value="<?php echo esc_attr( $player_name ); ?>" />
                            </div>
                            <div class="vibe-form-group">
                                <label for="vibe_tagline">Tagline</label>
                                <input type="text" id="vibe_tagline" name="vibe_tagline" value="<?php echo esc_attr( $tagline ); ?>" />
                            </div>
                        </div>

                        <!-- Appearance & Security -->
                        <div class="vibe-card">
                            <h2><span class="dashicons dashicons-admin-appearance"></span> Experience & Security</h2>
                            <div class="vibe-form-group">
                                <label for="vibe_primary_color">Primary Accent Color</label>
                                <p class="description" style="margin-bottom:10px;">The main brand color used in the player UI.</p>
                                <input type="color" id="vibe_primary_color" name="vibe_primary_color" value="<?php echo esc_attr( $primary_color ); ?>" style="height:50px; padding:5px !important;" />
                            </div>
                            <div class="vibe-form-group" style="background:#fcfcfc; padding:20px; border-radius:16px; border:1px solid #eee;">
                                <label style="display:flex; align-items:center; gap:12px; cursor:pointer; margin:0;">
                                    <input type="checkbox" name="vibe_allow_registration" value="1" <?php checked( $allow_reg, '1' ); ?> style="width:auto !important;" />
                                    <span>Allow User Registration & Public Playlists</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" class="vibe-btn vibe-btn-primary vibe-btn-lg">Save All Changes</button>
                    </div>
                </form>
            </div>
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

    public function save_artist() {
        if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['vibe_artist_nonce'], 'vibe_save_artist' ) ) wp_die( 'Unauthorized' );
        
        $artist_id = absint( $_POST['artist_id'] ?? 0 );
        $title     = sanitize_text_field( $_POST['artist_title'] );
        
        $args = [
            'post_type'   => 'vibe_artist',
            'post_title'  => $title,
            'post_status' => 'publish',
        ];

        if ( $artist_id ) {
            $args['ID'] = $artist_id;
            wp_update_post( $args );
        } else {
            $artist_id = wp_insert_post( $args );
        }

        if ( $artist_id ) {
            if ( ! empty( $_POST['artist_image_id'] ) ) {
                set_post_thumbnail( $artist_id, absint( $_POST['artist_image_id'] ) );
            } else {
                delete_post_thumbnail( $artist_id );
            }
            update_post_meta( $artist_id, '_vibe_artist_monthly_listeners', absint( $_POST['artist_listeners'] ?? 0 ) );
            update_post_meta( $artist_id, '_vibe_artist_bio', sanitize_textarea_field( $_POST['artist_bio'] ?? '' ) );
            update_post_meta( $artist_id, '_vibe_artist_spotify', esc_url_raw( $_POST['artist_spotify'] ?? '' ) );
            update_post_meta( $artist_id, '_vibe_artist_featured', isset( $_POST['artist_featured'] ) ? '1' : '0' );
        }

        wp_redirect( admin_url( 'admin.php?page=vibe-studio&tab=artists&saved=1' ) );
        exit;
    }

    public function save_album() {
        if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['vibe_album_nonce'], 'vibe_save_album' ) ) wp_die( 'Unauthorized' );
        
        $album_id = absint( $_POST['album_id'] ?? 0 );
        $title    = sanitize_text_field( $_POST['album_title'] );
        
        $args = [
            'post_type'   => 'vibe_album',
            'post_title'  => $title,
            'post_status' => 'publish',
        ];

        if ( $album_id ) {
            $args['ID'] = $album_id;
            wp_update_post( $args );
        } else {
            $album_id = wp_insert_post( $args );
        }

        if ( $album_id ) {
            if ( ! empty( $_POST['album_image_id'] ) ) {
                set_post_thumbnail( $album_id, absint( $_POST['album_image_id'] ) );
            } else {
                delete_post_thumbnail( $album_id );
            }
            update_post_meta( $album_id, '_vibe_album_artist', absint( $_POST['album_artist'] ?? 0 ) );
            update_post_meta( $album_id, '_vibe_album_release_date', sanitize_text_field( $_POST['album_date'] ?? '' ) );
            update_post_meta( $album_id, '_vibe_album_featured', isset( $_POST['album_featured'] ) ? '1' : '0' );
        }

        wp_redirect( admin_url( 'admin.php?page=vibe-studio&tab=albums&saved=1' ) );
        exit;
    }

    public function save_track() {
        if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['vibe_track_nonce'], 'vibe_save_track' ) ) wp_die( 'Unauthorized' );
        
        $track_id = absint( $_POST['track_id'] ?? 0 );
        $title    = sanitize_text_field( $_POST['track_title'] );
        
        $args = [
            'post_type'   => 'vibe_track',
            'post_title'  => $title,
            'post_status' => 'publish',
        ];

        if ( $track_id ) {
            $args['ID'] = $track_id;
            wp_update_post( $args );
        } else {
            $track_id = wp_insert_post( $args );
        }

        if ( $track_id ) {
            update_post_meta( $track_id, '_vibe_track_audio_url', esc_url_raw( $_POST['track_audio_url'] ?? '' ) );
            update_post_meta( $track_id, '_vibe_track_video_url', esc_url_raw( $_POST['track_video_url'] ?? '' ) );
            
            // Multiple Artists
            delete_post_meta( $track_id, '_vibe_track_artist' );
            $artist_ids = isset( $_POST['track_artist'] ) ? (array) $_POST['track_artist'] : [];
            foreach ( $artist_ids as $aid ) {
                if ( $aid ) add_post_meta( $track_id, '_vibe_track_artist', absint( $aid ) );
            }

            update_post_meta( $track_id, '_vibe_track_album', absint( $_POST['track_album'] ?? 0 ) );
            update_post_meta( $track_id, '_vibe_track_duration', sanitize_text_field( $_POST['track_duration'] ?? '' ) );
            update_post_meta( $track_id, '_vibe_track_lyrics', sanitize_textarea_field( $_POST['track_lyrics'] ?? '' ) );
            update_post_meta( $track_id, '_vibe_track_featured', isset( $_POST['track_featured'] ) ? '1' : '0' );
            
            if ( ! empty( $_POST['track_image_id'] ) ) {
                set_post_thumbnail( $track_id, absint( $_POST['track_image_id'] ) );
            } else {
                delete_post_thumbnail( $track_id );
            }

            if ( ! empty( $_POST['track_genre'] ) ) {
                wp_set_object_terms( $track_id, absint( $_POST['track_genre'] ), 'vibe_genre' );
            }
        }

        wp_redirect( admin_url( 'admin.php?page=vibe-studio&tab=tracks&saved=1' ) );
        exit;
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function get_svg_icon() {
        return 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>' );
    }
}
