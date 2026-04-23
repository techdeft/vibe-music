<?php
/**
 * Custom Post Types and Taxonomies for VIBE Music
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_CPT {

    public function register() {
        add_action( 'init', [ $this, 'register_post_types' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta' ] );
    }

    // -------------------------------------------------------------------------
    // POST TYPES
    // -------------------------------------------------------------------------

    public function register_post_types() {

        // ARTIST
        register_post_type( 'vibe_artist', [
            'labels'            => $this->get_labels( 'Artist', 'Artists' ),
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => false, // We add under our own menu
            'supports'          => [ 'title', 'editor', 'thumbnail' ],
            'show_in_rest'      => true,
            'menu_icon'         => 'dashicons-microphone',
        ] );

        // ALBUM
        register_post_type( 'vibe_album', [
            'labels'            => $this->get_labels( 'Album', 'Albums' ),
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => false,
            'supports'          => [ 'title', 'thumbnail' ],
            'show_in_rest'      => true,
            'menu_icon'         => 'dashicons-album',
        ] );

        // TRACK
        register_post_type( 'vibe_track', [
            'labels'            => $this->get_labels( 'Track', 'Tracks' ),
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => false,
            'supports'          => [ 'title', 'thumbnail' ],
            'show_in_rest'      => true,
            'menu_icon'         => 'dashicons-format-audio',
        ] );
    }

    // -------------------------------------------------------------------------
    // TAXONOMIES
    // -------------------------------------------------------------------------

    public function register_taxonomies() {

        // GENRE
        register_taxonomy( 'vibe_genre', [ 'vibe_track', 'vibe_album' ], [
            'labels'            => [
                'name'          => 'Genres',
                'singular_name' => 'Genre',
                'add_new_item'  => 'Add New Genre',
                'edit_item'     => 'Edit Genre',
            ],
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_ui'           => true,
            'show_in_menu'      => false,
        ] );
    }

    // -------------------------------------------------------------------------
    // META BOXES
    // -------------------------------------------------------------------------

    public function add_meta_boxes() {

        // Artist meta box
        add_meta_box( 'vibe_artist_meta', 'Artist Details', [ $this, 'artist_meta_box' ], 'vibe_artist', 'normal', 'high' );

        // Album meta box
        add_meta_box( 'vibe_album_meta', 'Album Details', [ $this, 'album_meta_box' ], 'vibe_album', 'normal', 'high' );

        // Track meta box
        add_meta_box( 'vibe_track_meta', 'Track Details', [ $this, 'track_meta_box' ], 'vibe_track', 'normal', 'high' );
    }

    // Artist details meta box
    public function artist_meta_box( $post ) {
        wp_nonce_field( 'vibe_artist_meta', 'vibe_artist_nonce' );
        $bio        = get_post_meta( $post->ID, '_vibe_artist_bio', true );
        $monthly    = get_post_meta( $post->ID, '_vibe_artist_monthly_listeners', true );
        $spotify    = get_post_meta( $post->ID, '_vibe_artist_spotify', true );
        $instagram  = get_post_meta( $post->ID, '_vibe_artist_instagram', true );
        $featured   = get_post_meta( $post->ID, '_vibe_artist_featured', true );
        ?>
        <style>.vibe-meta-table { width:100%; } .vibe-meta-table td { padding: 8px 4px; } .vibe-meta-table input[type=text], .vibe-meta-table input[type=url], .vibe-meta-table input[type=number], .vibe-meta-table textarea { width:100%; }</style>
        <table class="vibe-meta-table">
            <tr>
                <td width="150"><label><strong>Monthly Listeners</strong></label></td>
                <td><input type="number" name="vibe_artist_monthly_listeners" value="<?php echo esc_attr( $monthly ); ?>" placeholder="e.g. 1500000" /></td>
            </tr>
            <tr>
                <td><label><strong>Short Bio</strong></label></td>
                <td><textarea name="vibe_artist_bio" rows="3"><?php echo esc_textarea( $bio ); ?></textarea></td>
            </tr>
            <tr>
                <td><label><strong>Spotify URL</strong></label></td>
                <td><input type="url" name="vibe_artist_spotify" value="<?php echo esc_attr( $spotify ); ?>" placeholder="https://open.spotify.com/artist/..." /></td>
            </tr>
            <tr>
                <td><label><strong>Instagram URL</strong></label></td>
                <td><input type="url" name="vibe_artist_instagram" value="<?php echo esc_attr( $instagram ); ?>" placeholder="https://instagram.com/..." /></td>
            </tr>
            <tr>
                <td><label><strong>Featured Artist</strong></label></td>
                <td><label><input type="checkbox" name="vibe_artist_featured" value="1" <?php checked( $featured, '1' ); ?> /> Show on Discovery page</label></td>
            </tr>
        </table>
        <?php
    }

    // Album details meta box
    public function album_meta_box( $post ) {
        wp_nonce_field( 'vibe_album_meta', 'vibe_album_nonce' );
        $artist_id      = get_post_meta( $post->ID, '_vibe_album_artist', true );
        $release_date   = get_post_meta( $post->ID, '_vibe_album_release_date', true );
        $featured       = get_post_meta( $post->ID, '_vibe_album_featured', true );
        $artists        = get_posts( [ 'post_type' => 'vibe_artist', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        ?>
        <table class="vibe-meta-table">
            <tr>
                <td width="150"><label><strong>Artist</strong></label></td>
                <td>
                    <select name="vibe_album_artist" style="width:100%">
                        <option value="">— Select Artist —</option>
                        <?php foreach ( $artists as $artist ) : ?>
                            <option value="<?php echo $artist->ID; ?>" <?php selected( $artist_id, $artist->ID ); ?>><?php echo esc_html( $artist->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label><strong>Release Date</strong></label></td>
                <td><input type="date" name="vibe_album_release_date" value="<?php echo esc_attr( $release_date ); ?>" /></td>
            </tr>
            <tr>
                <td><label><strong>Featured Album</strong></label></td>
                <td><label><input type="checkbox" name="vibe_album_featured" value="1" <?php checked( $featured, '1' ); ?> /> Show on Discovery page</label></td>
            </tr>
        </table>
        <?php
    }

    // Track details meta box
    public function track_meta_box( $post ) {
        wp_nonce_field( 'vibe_track_meta', 'vibe_track_nonce' );
        $artist_id  = get_post_meta( $post->ID, '_vibe_track_artist', true );
        $album_id   = get_post_meta( $post->ID, '_vibe_track_album', true );
        $audio_url  = get_post_meta( $post->ID, '_vibe_track_audio_url', true );
        $duration   = get_post_meta( $post->ID, '_vibe_track_duration', true );
        $track_no   = get_post_meta( $post->ID, '_vibe_track_number', true );
        $featured   = get_post_meta( $post->ID, '_vibe_track_featured', true );
        $lyrics     = get_post_meta( $post->ID, '_vibe_track_lyrics', true );

        $artists = get_posts( [ 'post_type' => 'vibe_artist', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        $albums  = get_posts( [ 'post_type' => 'vibe_album', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        ?>
        <table class="vibe-meta-table">
            <tr>
                <td width="150"><label><strong>Artist</strong></label></td>
                <td>
                    <select name="vibe_track_artist" style="width:100%">
                        <option value="">— Select Artist —</option>
                        <?php foreach ( $artists as $artist ) : ?>
                            <option value="<?php echo $artist->ID; ?>" <?php selected( $artist_id, $artist->ID ); ?>><?php echo esc_html( $artist->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label><strong>Album</strong></label></td>
                <td>
                    <select name="vibe_track_album" style="width:100%">
                        <option value="">— No Album / Single —</option>
                        <?php foreach ( $albums as $album ) : ?>
                            <option value="<?php echo $album->ID; ?>" <?php selected( $album_id, $album->ID ); ?>><?php echo esc_html( $album->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label><strong>Audio File URL</strong></label></td>
                <td>
                    <input type="text" name="vibe_track_audio_url" id="vibe_track_audio_url" value="<?php echo esc_attr( $audio_url ); ?>" placeholder="Select or paste audio URL" />
                    <button type="button" class="button button-secondary" onclick="vibeSelectAudio()">Select Audio</button>
                </td>
            </tr>
            <tr>
                <td><label><strong>Duration (mm:ss)</strong></label></td>
                <td><input type="text" name="vibe_track_duration" value="<?php echo esc_attr( $duration ); ?>" placeholder="e.g. 3:42" /></td>
            </tr>
            <tr>
                <td><label><strong>Track Number</strong></label></td>
                <td><input type="number" name="vibe_track_number" value="<?php echo esc_attr( $track_no ); ?>" placeholder="e.g. 1" /></td>
            </tr>
            <tr>
                <td><label><strong>Featured Track</strong></label></td>
                <td><label><input type="checkbox" name="vibe_track_featured" value="1" <?php checked( $featured, '1' ); ?> /> Show on Discovery page</label></td>
            </tr>
            <tr>
                <td><label><strong>Lyrics</strong></label></td>
                <td><textarea name="vibe_track_lyrics" rows="5" placeholder="Paste lyrics here..."><?php echo esc_textarea( $lyrics ); ?></textarea></td>
            </tr>
        </table>
        <script>
        function vibeSelectAudio() {
            var frame = wp.media({
                title: 'Select Audio File',
                button: { text: 'Use this file' },
                library: { type: 'audio' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                document.getElementById('vibe_track_audio_url').value = attachment.url;
            });
            frame.open();
        }
        </script>
        <?php
    }

    // -------------------------------------------------------------------------
    // SAVE META
    // -------------------------------------------------------------------------

    public function save_meta( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $post_type = get_post_type( $post_id );

        if ( $post_type === 'vibe_artist' && isset( $_POST['vibe_artist_nonce'] ) && wp_verify_nonce( $_POST['vibe_artist_nonce'], 'vibe_artist_meta' ) ) {
            update_post_meta( $post_id, '_vibe_artist_bio', sanitize_textarea_field( $_POST['vibe_artist_bio'] ?? '' ) );
            update_post_meta( $post_id, '_vibe_artist_monthly_listeners', absint( $_POST['vibe_artist_monthly_listeners'] ?? 0 ) );
            update_post_meta( $post_id, '_vibe_artist_spotify', esc_url_raw( $_POST['vibe_artist_spotify'] ?? '' ) );
            update_post_meta( $post_id, '_vibe_artist_instagram', esc_url_raw( $_POST['vibe_artist_instagram'] ?? '' ) );
            update_post_meta( $post_id, '_vibe_artist_featured', isset( $_POST['vibe_artist_featured'] ) ? '1' : '0' );
        }

        if ( $post_type === 'vibe_album' && isset( $_POST['vibe_album_nonce'] ) && wp_verify_nonce( $_POST['vibe_album_nonce'], 'vibe_album_meta' ) ) {
            update_post_meta( $post_id, '_vibe_album_artist', absint( $_POST['vibe_album_artist'] ?? 0 ) );
            update_post_meta( $post_id, '_vibe_album_release_date', sanitize_text_field( $_POST['vibe_album_release_date'] ?? '' ) );
            update_post_meta( $post_id, '_vibe_album_featured', isset( $_POST['vibe_album_featured'] ) ? '1' : '0' );
        }

        if ( $post_type === 'vibe_track' && isset( $_POST['vibe_track_nonce'] ) && wp_verify_nonce( $_POST['vibe_track_nonce'], 'vibe_track_meta' ) ) {
            update_post_meta( $post_id, '_vibe_track_artist', absint( $_POST['vibe_track_artist'] ?? 0 ) );
            update_post_meta( $post_id, '_vibe_track_album', absint( $_POST['vibe_track_album'] ?? 0 ) );
            update_post_meta( $post_id, '_vibe_track_audio_url', esc_url_raw( $_POST['vibe_track_audio_url'] ?? '' ) );
            update_post_meta( $post_id, '_vibe_track_duration', sanitize_text_field( $_POST['vibe_track_duration'] ?? '' ) );
            update_post_meta( $post_id, '_vibe_track_number', absint( $_POST['vibe_track_number'] ?? 0 ) );
            update_post_meta( $post_id, '_vibe_track_featured', isset( $_POST['vibe_track_featured'] ) ? '1' : '0' );
            update_post_meta( $post_id, '_vibe_track_lyrics', sanitize_textarea_field( $_POST['vibe_track_lyrics'] ?? '' ) );
        }
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function get_labels( $singular, $plural ) {
        return [
            'name'               => $plural,
            'singular_name'      => $singular,
            'add_new'            => "Add New $singular",
            'add_new_item'       => "Add New $singular",
            'edit_item'          => "Edit $singular",
            'new_item'           => "New $singular",
            'view_item'          => "View $singular",
            'search_items'       => "Search $plural",
            'not_found'          => "No $plural found",
            'not_found_in_trash' => "No $plural found in Trash",
        ];
    }
}
