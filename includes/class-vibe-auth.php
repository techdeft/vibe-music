<?php
/**
 * Authentication REST API for VIBE Music
 * Handles Login, Register, Logout, and current user via WP users
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Auth {

    const NAMESPACE = 'vibe-music/v1';

    public function register() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        // Login
        register_rest_route( self::NAMESPACE, '/auth/login', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'login' ],
            'permission_callback' => '__return_true',
        ] );

        // Register
        register_rest_route( self::NAMESPACE, '/auth/register', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'register_user' ],
            'permission_callback' => '__return_true',
        ] );

        // Logout
        register_rest_route( self::NAMESPACE, '/auth/logout', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'logout' ],
            'permission_callback' => '__return_true',
        ] );

        // Current user
        register_rest_route( self::NAMESPACE, '/auth/me', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'me' ],
            'permission_callback' => '__return_true',
        ] );

        // Forgot password (sends WP reset email)
        register_rest_route( self::NAMESPACE, '/auth/forgot-password', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'forgot_password' ],
            'permission_callback' => '__return_true',
        ] );

        // --- PLAYLISTS ---

        // List user's playlists
        register_rest_route( self::NAMESPACE, '/playlists', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_playlists' ],
                'permission_callback' => [ $this, 'is_logged_in' ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'create_playlist' ],
                'permission_callback' => [ $this, 'is_logged_in' ],
            ],
        ] );

        // Single playlist
        register_rest_route( self::NAMESPACE, '/playlists/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_playlist' ],
                'permission_callback' => '__return_true',
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [ $this, 'update_playlist' ],
                'permission_callback' => [ $this, 'can_edit_playlist' ],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ $this, 'delete_playlist' ],
                'permission_callback' => [ $this, 'can_edit_playlist' ],
            ],
        ] );

        // Add/remove tracks from playlist
        register_rest_route( self::NAMESPACE, '/playlists/(?P<id>\d+)/tracks', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'add_track_to_playlist' ],
                'permission_callback' => [ $this, 'can_edit_playlist' ],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [ $this, 'remove_track_from_playlist' ],
                'permission_callback' => [ $this, 'can_edit_playlist' ],
            ],
        ] );
        
        // --- LIKES ---
        register_rest_route( self::NAMESPACE, '/tracks/(?P<id>\d+)/like', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'toggle_like' ],
            'permission_callback' => [ $this, 'is_logged_in' ],
        ] );

        // Liked Songs list
        register_rest_route( self::NAMESPACE, '/me/liked-songs', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_liked_songs' ],
            'permission_callback' => [ $this, 'is_logged_in' ],
        ] );
    }

    // -------------------------------------------------------------------------
    // PERMISSIONS
    // -------------------------------------------------------------------------

    public function is_logged_in() {
        return is_user_logged_in();
    }

    public function can_edit_playlist( $request ) {
        if ( ! is_user_logged_in() ) return false;
        $playlist_id = absint( $request['id'] );
        $playlist    = get_post( $playlist_id );
        if ( ! $playlist || $playlist->post_type !== 'vibe_playlist' ) return false;
        return (int) $playlist->post_author === get_current_user_id() || current_user_can( 'manage_options' );
    }

    // -------------------------------------------------------------------------
    // AUTH ENDPOINTS
    // -------------------------------------------------------------------------

    public function login( $request ) {
        $username = sanitize_text_field( $request->get_param( 'username' ) ?? '' );
        $password = $request->get_param( 'password' ) ?? '';

        if ( empty( $username ) || empty( $password ) ) {
            return new WP_Error( 'missing_fields', 'Username and password are required.', [ 'status' => 400 ] );
        }

        $user = wp_authenticate( $username, $password );

        if ( is_wp_error( $user ) ) {
            return new WP_Error( 'invalid_credentials', 'Invalid username or password.', [ 'status' => 401 ] );
        }

        // Set auth cookie
        wp_set_auth_cookie( $user->ID, true );
        wp_set_current_user( $user->ID );

        return rest_ensure_response( [
            'success' => true,
            'user'    => $this->format_user( $user ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public function register_user( $request ) {
        // Check if registration is enabled
        if ( get_option( 'vibe_allow_registration', '0' ) !== '1' ) {
            return new WP_Error( 'registration_disabled', 'User registration is currently disabled.', [ 'status' => 403 ] );
        }

        $username  = sanitize_user( $request->get_param( 'username' ) ?? '' );
        $email     = sanitize_email( $request->get_param( 'email' ) ?? '' );
        $password  = $request->get_param( 'password' ) ?? '';
        $display   = sanitize_text_field( $request->get_param( 'display_name' ) ?? $username );

        if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
            return new WP_Error( 'missing_fields', 'All fields are required.', [ 'status' => 400 ] );
        }

        if ( ! is_email( $email ) ) {
            return new WP_Error( 'invalid_email', 'Please enter a valid email address.', [ 'status' => 400 ] );
        }

        if ( username_exists( $username ) ) {
            return new WP_Error( 'username_taken', 'That username is already taken.', [ 'status' => 409 ] );
        }

        if ( email_exists( $email ) ) {
            return new WP_Error( 'email_taken', 'An account with that email already exists.', [ 'status' => 409 ] );
        }

        if ( strlen( $password ) < 6 ) {
            return new WP_Error( 'weak_password', 'Password must be at least 6 characters.', [ 'status' => 400 ] );
        }

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            return new WP_Error( 'registration_failed', $user_id->get_error_message(), [ 'status' => 500 ] );
        }

        wp_update_user( [ 'ID' => $user_id, 'display_name' => $display ] );

        $user = get_user_by( 'id', $user_id );
        wp_set_auth_cookie( $user_id, true );
        wp_set_current_user( $user_id );

        return rest_ensure_response( [
            'success' => true,
            'user'    => $this->format_user( $user ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public function logout( $request ) {
        wp_logout();
        return rest_ensure_response( [ 'success' => true ] );
    }

    public function me( $request ) {
        if ( ! is_user_logged_in() ) {
            return rest_ensure_response( [ 'user' => null ] );
        }
        return rest_ensure_response( [ 'user' => $this->format_user( wp_get_current_user() ) ] );
    }

    public function forgot_password( $request ) {
        $email = sanitize_email( $request->get_param( 'email' ) ?? '' );

        if ( ! $email || ! is_email( $email ) ) {
            return new WP_Error( 'invalid_email', 'Please provide a valid email address.', [ 'status' => 400 ] );
        }

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            // Don't reveal if email exists - always return success for security
            return rest_ensure_response( [ 'success' => true, 'message' => 'If that email exists, a reset link has been sent.' ] );
        }

        // Use WP's built-in password reset
        $result = retrieve_password( $user->user_login );

        return rest_ensure_response( [ 'success' => true, 'message' => 'If that email exists, a reset link has been sent.' ] );
    }

    // -------------------------------------------------------------------------
    // PLAYLIST ENDPOINTS
    // -------------------------------------------------------------------------

    public function get_playlists( $request ) {
        $user_id = get_current_user_id();
        $posts   = get_posts( [
            'post_type'   => 'vibe_playlist',
            'author'      => $user_id,
            'numberposts' => -1,
            'post_status' => [ 'publish', 'private' ],
            'orderby'     => 'date',
            'order'       => 'DESC',
        ] );

        return rest_ensure_response( array_map( [ $this, 'format_playlist' ], $posts ) );
    }

    public function create_playlist( $request ) {
        $name        = sanitize_text_field( $request->get_param( 'name' ) ?? '' );
        $description = sanitize_textarea_field( $request->get_param( 'description' ) ?? '' );
        $public      = (bool) $request->get_param( 'public' );

        if ( empty( $name ) ) {
            return new WP_Error( 'missing_name', 'Playlist name is required.', [ 'status' => 400 ] );
        }

        $post_id = wp_insert_post( [
            'post_type'    => 'vibe_playlist',
            'post_title'   => $name,
            'post_content' => $description,
            'post_status'  => $public ? 'publish' : 'private',
            'post_author'  => get_current_user_id(),
        ] );

        if ( is_wp_error( $post_id ) ) {
            return new WP_Error( 'creation_failed', 'Failed to create playlist.', [ 'status' => 500 ] );
        }

        update_post_meta( $post_id, '_vibe_playlist_tracks', [] );

        return rest_ensure_response( $this->format_playlist( get_post( $post_id ) ) );
    }

    public function get_playlist( $request ) {
        $post = get_post( absint( $request['id'] ) );

        if ( ! $post || $post->post_type !== 'vibe_playlist' ) {
            return new WP_Error( 'not_found', 'Playlist not found.', [ 'status' => 404 ] );
        }

        // Private playlists only visible to owner
        if ( $post->post_status === 'private' && (int) $post->post_author !== get_current_user_id() ) {
            return new WP_Error( 'forbidden', 'This playlist is private.', [ 'status' => 403 ] );
        }

        return rest_ensure_response( $this->format_playlist( $post, true ) );
    }

    public function update_playlist( $request ) {
        $post_id = absint( $request['id'] );
        $name    = sanitize_text_field( $request->get_param( 'name' ) ?? '' );
        $desc    = sanitize_textarea_field( $request->get_param( 'description' ) ?? '' );
        $public  = $request->get_param( 'public' );

        $update = [ 'ID' => $post_id ];
        if ( $name ) $update['post_title'] = $name;
        if ( $desc !== null ) $update['post_content'] = $desc;
        if ( $public !== null ) $update['post_status'] = $public ? 'publish' : 'private';

        wp_update_post( $update );

        return rest_ensure_response( $this->format_playlist( get_post( $post_id ) ) );
    }

    public function delete_playlist( $request ) {
        wp_delete_post( absint( $request['id'] ), true );
        return rest_ensure_response( [ 'success' => true ] );
    }

    public function add_track_to_playlist( $request ) {
        $post_id  = absint( $request['id'] );
        $track_id = absint( $request->get_param( 'track_id' ) ?? 0 );

        if ( ! $track_id || get_post_type( $track_id ) !== 'vibe_track' ) {
            return new WP_Error( 'invalid_track', 'Invalid track ID.', [ 'status' => 400 ] );
        }

        $tracks   = get_post_meta( $post_id, '_vibe_playlist_tracks', true ) ?: [];
        if ( ! in_array( $track_id, $tracks ) ) {
            $tracks[] = $track_id;
            update_post_meta( $post_id, '_vibe_playlist_tracks', $tracks );
        }

        return rest_ensure_response( $this->format_playlist( get_post( $post_id ), true ) );
    }

    public function toggle_like( $request ) {
        $user_id  = get_current_user_id();
        $track_id = absint( $request['id'] );

        if ( get_post_type( $track_id ) !== 'vibe_track' ) {
            return new WP_Error( 'invalid_track', 'Invalid track ID.', [ 'status' => 400 ] );
        }

        $liked_tracks = get_user_meta( $user_id, '_vibe_liked_tracks', true ) ?: [];
        $is_liked     = in_array( $track_id, $liked_tracks );

        $likes_count = (int) get_post_meta( $track_id, '_vibe_likes_count', true );

        if ( $is_liked ) {
            // Unlike
            $liked_tracks = array_values( array_filter( $liked_tracks, fn( $id ) => $id != $track_id ) );
            $likes_count  = max( 0, $likes_count - 1 );
            $liked        = false;
        } else {
            // Like
            $liked_tracks[] = $track_id;
            $likes_count++;
            $liked          = true;
        }

        update_user_meta( $user_id, '_vibe_liked_tracks', $liked_tracks );
        update_post_meta( $track_id, '_vibe_likes_count', $likes_count );

        return rest_ensure_response( [
            'success'      => true,
            'liked'        => $liked,
            'likes_count'  => $likes_count,
            'liked_tracks' => $liked_tracks,
        ] );
    }

    public function get_liked_songs( $request ) {
        $user_id      = get_current_user_id();
        $liked_tracks = get_user_meta( $user_id, '_vibe_liked_tracks', true ) ?: [];
        
        if ( empty( $liked_tracks ) ) {
            return rest_ensure_response( [] );
        }

        $posts = get_posts( [
            'post_type'   => 'vibe_track',
            'post__in'    => $liked_tracks,
            'numberposts' => -1,
            'orderby'     => 'post__in',
        ] );

        $api = new Vibe_API();
        return rest_ensure_response( array_map( [ $api, 'format_track' ], $posts ) );
    }

    public function remove_track_from_playlist( $request ) {
        $post_id  = absint( $request['id'] );
        $track_id = absint( $request->get_param( 'track_id' ) ?? 0 );

        $tracks = get_post_meta( $post_id, '_vibe_playlist_tracks', true ) ?: [];
        $tracks = array_values( array_filter( $tracks, fn( $t ) => $t != $track_id ) );
        update_post_meta( $post_id, '_vibe_playlist_tracks', $tracks );

        return rest_ensure_response( $this->format_playlist( get_post( $post_id ), true ) );
    }

    // -------------------------------------------------------------------------
    // FORMATTERS
    // -------------------------------------------------------------------------

    private function format_user( $user ) {
        return [
            'id'           => $user->ID,
            'username'     => $user->user_login,
            'display_name' => $user->display_name,
            'email'        => $user->user_email,
            'avatar'       => get_avatar_url( $user->ID, [ 'size' => 96 ] ),
            'is_admin'     => user_can( $user->ID, 'manage_options' ),
            'liked_tracks' => get_user_meta( $user->ID, '_vibe_liked_tracks', true ) ?: [],
        ];
    }

    private function format_playlist( $post, $with_tracks = false ) {
        $tracks_ids = get_post_meta( $post->ID, '_vibe_playlist_tracks', true ) ?: [];
        $author     = get_user_by( 'id', $post->post_author );

        $data = [
            'id'           => $post->ID,
            'name'         => $post->post_title,
            'description'  => $post->post_content,
            'public'       => $post->post_status === 'publish',
            'author_id'    => (int) $post->post_author,
            'author_name'  => $author ? $author->display_name : 'Unknown',
            'track_count'  => count( $tracks_ids ),
            'cover'        => $this->get_playlist_cover( $tracks_ids ) ?: '',
            'created_at'   => $post->post_date,
        ];

        if ( $with_tracks ) {
            $data['tracks'] = $this->get_tracks_by_ids( $tracks_ids );
        }

        return $data;
    }

    private function get_tracks_by_ids( $ids ) {
        if ( empty( $ids ) ) return [];
        $posts = get_posts( [
            'post_type'      => 'vibe_track',
            'post__in'       => $ids,
            'numberposts'    => -1,
            'orderby'        => 'post__in',
        ] );

        $api = new Vibe_API();
        return array_map( fn( $p ) => $this->format_track_simple( $p ), $posts );
    }

    private function format_track_simple( $post ) {
        $artist_id = get_post_meta( $post->ID, '_vibe_track_artist', true );
        $album_id  = get_post_meta( $post->ID, '_vibe_track_album', true );
        return [
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'audio_url'   => get_post_meta( $post->ID, '_vibe_track_audio_url', true ) ?: '',
            'cover'       => ( $album_id ? $this->get_featured_image( $album_id ) : $this->get_featured_image( $post->ID ) ) ?: '',
            'duration'    => get_post_meta( $post->ID, '_vibe_track_duration', true ) ?: '',
            'artist_id'   => (int) $artist_id,
            'artist_name' => $artist_id ? get_the_title( $artist_id ) : '',
            'album_id'    => (int) $album_id,
            'album_title' => $album_id ? get_the_title( $album_id ) : '',
        ];
    }

    private function get_playlist_cover( $track_ids ) {
        // Use the cover of the first track with an album
        foreach ( $track_ids as $track_id ) {
            $album_id = get_post_meta( $track_id, '_vibe_track_album', true );
            if ( $album_id ) {
                $src = $this->get_featured_image( $album_id );
                if ( $src ) return $src;
            }
            $src = $this->get_featured_image( $track_id );
            if ( $src ) return $src;
        }
        return '';
    }

    private function get_featured_image( $post_id ) {
        $thumb_id = get_post_thumbnail_id( $post_id );
        if ( ! $thumb_id ) return '';
        $src = wp_get_attachment_image_src( $thumb_id, 'large' );
        return $src ? $src[0] : '';
    }
}
