<?php
/**
 * REST API Endpoints for VIBE Music
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_API {

    const NAMESPACE = 'vibe-music/v1';

    public function register() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        // Discovery (featured content)
        register_rest_route( self::NAMESPACE, '/discovery', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_discovery' ],
            'permission_callback' => '__return_true',
        ] );

        // Artists list
        register_rest_route( self::NAMESPACE, '/artists', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_artists' ],
            'permission_callback' => '__return_true',
        ] );

        // Single Artist
        register_rest_route( self::NAMESPACE, '/artist/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_artist' ],
            'permission_callback' => '__return_true',
            'args'                => [ 'id' => [ 'validate_callback' => fn( $p ) => is_numeric( $p ) ] ],
        ] );

        // Albums list
        register_rest_route( self::NAMESPACE, '/albums', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_albums' ],
            'permission_callback' => '__return_true',
        ] );

        // Single Album with tracks
        register_rest_route( self::NAMESPACE, '/album/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_album' ],
            'permission_callback' => '__return_true',
            'args'                => [ 'id' => [ 'validate_callback' => fn( $p ) => is_numeric( $p ) ] ],
        ] );

        // Tracks list
        register_rest_route( self::NAMESPACE, '/tracks', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_tracks' ],
            'permission_callback' => '__return_true',
        ] );

        // Search
        register_rest_route( self::NAMESPACE, '/search', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'search' ],
            'permission_callback' => '__return_true',
        ] );

        // Genres
        register_rest_route( self::NAMESPACE, '/genres', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_genres' ],
            'permission_callback' => '__return_true',
        ] );

        // Tracks by Genre
        register_rest_route( self::NAMESPACE, '/genre/(?P<slug>[a-zA-Z0-9-]+)/tracks', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_tracks_by_genre' ],
            'permission_callback' => '__return_true',
        ] );
    }

    // -------------------------------------------------------------------------
    // ENDPOINT HANDLERS
    // -------------------------------------------------------------------------

    public function get_discovery( $request ) {
        $data = [
            'featured_tracks'  => $this->format_tracks( get_posts( [
                'post_type'   => 'vibe_track',
                'numberposts' => 10,
                'meta_query'  => [ [ 'key' => '_vibe_track_featured', 'value' => '1' ] ],
            ] ) ),
            'featured_albums'  => $this->format_albums( get_posts( [
                'post_type'   => 'vibe_album',
                'numberposts' => 8,
                'meta_query'  => [ [ 'key' => '_vibe_album_featured', 'value' => '1' ] ],
            ] ) ),
            'featured_artists' => $this->format_artists( get_posts( [
                'post_type'   => 'vibe_artist',
                'numberposts' => 6,
                'meta_query'  => [ [ 'key' => '_vibe_artist_featured', 'value' => '1' ] ],
            ] ) ),
            'recent_tracks'    => $this->format_tracks( get_posts( [
                'post_type'   => 'vibe_track',
                'numberposts' => 20,
                'orderby'     => 'date',
                'order'       => 'DESC',
            ] ) ),
            'genres'           => $this->format_genres( get_terms( [ 'taxonomy' => 'vibe_genre', 'hide_empty' => false ] ) ),
        ];

        return rest_ensure_response( $data );
    }

    public function get_artists( $request ) {
        $page     = max( 1, intval( $request->get_param( 'page' ) ?? 1 ) );
        $per_page = min( 50, intval( $request->get_param( 'per_page' ) ?? 20 ) );

        $posts = get_posts( [
            'post_type'      => 'vibe_artist',
            'numberposts'    => $per_page,
            'offset'         => ( $page - 1 ) * $per_page,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        return rest_ensure_response( $this->format_artists( $posts ) );
    }

    public function get_artist( $request ) {
        $id   = $request['id'];
        $post = get_post( $id );

        if ( ! $post || $post->post_type !== 'vibe_artist' ) {
            return new WP_Error( 'not_found', 'Artist not found', [ 'status' => 404 ] );
        }

        $artist = $this->format_artist( $post );

        // Fetch artist's albums
        $albums = get_posts( [
            'post_type'   => 'vibe_album',
            'numberposts' => -1,
            'meta_query'  => [ [ 'key' => '_vibe_album_artist', 'value' => $id ] ],
            'orderby'     => 'meta_value',
            'meta_key'    => '_vibe_album_release_date',
            'order'       => 'DESC',
        ] );
        $artist['albums'] = $this->format_albums( $albums );

        // Fetch top tracks
        $tracks = get_posts( [
            'post_type'   => 'vibe_track',
            'numberposts' => 10,
            'meta_query'  => [ [ 'key' => '_vibe_track_artist', 'value' => $id ] ],
        ] );
        $artist['top_tracks'] = $this->format_tracks( $tracks );

        return rest_ensure_response( $artist );
    }

    public function get_albums( $request ) {
        $page     = max( 1, intval( $request->get_param( 'page' ) ?? 1 ) );
        $per_page = min( 50, intval( $request->get_param( 'per_page' ) ?? 20 ) );
        $artist   = $request->get_param( 'artist' );

        $args = [
            'post_type'   => 'vibe_album',
            'numberposts' => $per_page,
            'offset'      => ( $page - 1 ) * $per_page,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ];

        if ( $artist ) {
            $args['meta_query'] = [ [ 'key' => '_vibe_album_artist', 'value' => intval( $artist ) ] ];
        }

        return rest_ensure_response( $this->format_albums( get_posts( $args ) ) );
    }

    public function get_album( $request ) {
        $id   = $request['id'];
        $post = get_post( $id );

        if ( ! $post || $post->post_type !== 'vibe_album' ) {
            return new WP_Error( 'not_found', 'Album not found', [ 'status' => 404 ] );
        }

        $album = $this->format_album( $post );

        // Fetch tracks for this album
        $tracks = get_posts( [
            'post_type'   => 'vibe_track',
            'numberposts' => -1,
            'meta_query'  => [ [ 'key' => '_vibe_track_album', 'value' => $id ] ],
            'orderby'     => 'meta_value_num',
            'meta_key'    => '_vibe_track_number',
            'order'       => 'ASC',
        ] );
        $album['tracks'] = $this->format_tracks( $tracks );

        return rest_ensure_response( $album );
    }

    public function get_tracks( $request ) {
        $page     = max( 1, intval( $request->get_param( 'page' ) ?? 1 ) );
        $per_page = min( 100, intval( $request->get_param( 'per_page' ) ?? 30 ) );

        return rest_ensure_response( $this->format_tracks( get_posts( [
            'post_type'   => 'vibe_track',
            'numberposts' => $per_page,
            'offset'      => ( $page - 1 ) * $per_page,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ] ) ) );
    }

    public function search( $request ) {
        $query = sanitize_text_field( $request->get_param( 'q' ) ?? '' );

        if ( empty( $query ) ) {
            return rest_ensure_response( [ 'tracks' => [], 'albums' => [], 'artists' => [] ] );
        }

        $args = [
            's'           => $query,
            'numberposts' => 10,
        ];

        return rest_ensure_response( [
            'tracks'  => $this->format_tracks( get_posts( array_merge( $args, [ 'post_type' => 'vibe_track' ] ) ) ),
            'albums'  => $this->format_albums( get_posts( array_merge( $args, [ 'post_type' => 'vibe_album' ] ) ) ),
            'artists' => $this->format_artists( get_posts( array_merge( $args, [ 'post_type' => 'vibe_artist' ] ) ) ),
        ] );
    }

    public function get_genres( $request ) {
        $terms = get_terms( [ 'taxonomy' => 'vibe_genre', 'hide_empty' => false ] );
        return rest_ensure_response( $this->format_genres( $terms ) );
    }

    public function get_tracks_by_genre( $request ) {
        $slug  = $request['slug'];
        $term  = get_term_by( 'slug', $slug, 'vibe_genre' );

        if ( ! $term ) {
            return new WP_Error( 'not_found', 'Genre not found', [ 'status' => 404 ] );
        }

        $tracks = get_posts( [
            'post_type'   => 'vibe_track',
            'numberposts' => -1,
            'tax_query'   => [ [ 'taxonomy' => 'vibe_genre', 'field' => 'slug', 'terms' => $slug ] ],
        ] );

        return rest_ensure_response( [
            'genre'  => $this->format_genre( $term ),
            'tracks' => $this->format_tracks( $tracks ),
        ] );
    }

    // -------------------------------------------------------------------------
    // FORMATTERS
    // -------------------------------------------------------------------------

    private function format_artists( $posts ) {
        return array_map( [ $this, 'format_artist' ], $posts );
    }

    private function format_artist( $post ) {
        return [
            'id'                => $post->ID,
            'name'              => $post->post_title,
            'bio'               => get_post_meta( $post->ID, '_vibe_artist_bio', true ),
            'monthly_listeners' => (int) get_post_meta( $post->ID, '_vibe_artist_monthly_listeners', true ),
            'image'             => $this->get_featured_image( $post->ID ),
            'spotify_url'       => get_post_meta( $post->ID, '_vibe_artist_spotify', true ),
            'instagram_url'     => get_post_meta( $post->ID, '_vibe_artist_instagram', true ),
            'featured'          => get_post_meta( $post->ID, '_vibe_artist_featured', true ) === '1',
        ];
    }

    private function format_albums( $posts ) {
        return array_map( [ $this, 'format_album' ], $posts );
    }

    private function format_album( $post ) {
        $artist_id = get_post_meta( $post->ID, '_vibe_album_artist', true );
        return [
            'id'           => $post->ID,
            'title'        => $post->post_title,
            'cover'        => $this->get_featured_image( $post->ID ),
            'release_date' => get_post_meta( $post->ID, '_vibe_album_release_date', true ),
            'artist_id'    => (int) $artist_id,
            'artist_name'  => $artist_id ? get_the_title( $artist_id ) : null,
            'genres'       => $this->format_genres( wp_get_post_terms( $post->ID, 'vibe_genre' ) ),
            'featured'     => get_post_meta( $post->ID, '_vibe_album_featured', true ) === '1',
        ];
    }

    private function format_tracks( $posts ) {
        return array_map( [ $this, 'format_track' ], $posts );
    }

    private function format_track( $post ) {
        $artist_id = get_post_meta( $post->ID, '_vibe_track_artist', true );
        $album_id  = get_post_meta( $post->ID, '_vibe_track_album', true );
        return [
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'audio_url'   => get_post_meta( $post->ID, '_vibe_track_audio_url', true ),
            'cover'       => $album_id ? $this->get_featured_image( $album_id ) : $this->get_featured_image( $post->ID ),
            'duration'    => get_post_meta( $post->ID, '_vibe_track_duration', true ),
            'track_number'=> (int) get_post_meta( $post->ID, '_vibe_track_number', true ),
            'artist_id'   => (int) $artist_id,
            'artist_name' => $artist_id ? get_the_title( $artist_id ) : null,
            'album_id'    => (int) $album_id,
            'album_title' => $album_id ? get_the_title( $album_id ) : null,
            'lyrics'      => get_post_meta( $post->ID, '_vibe_track_lyrics', true ),
            'genres'      => $this->format_genres( wp_get_post_terms( $post->ID, 'vibe_genre' ) ),
            'featured'    => get_post_meta( $post->ID, '_vibe_track_featured', true ) === '1',
        ];
    }

    private function format_genres( $terms ) {
        if ( is_wp_error( $terms ) ) return [];
        return array_map( [ $this, 'format_genre' ], $terms );
    }

    private function format_genre( $term ) {
        return [
            'id'          => $term->term_id,
            'name'        => $term->name,
            'slug'        => $term->slug,
            'description' => $term->description,
            'count'       => $term->count,
        ];
    }

    private function get_featured_image( $post_id, $size = 'large' ) {
        $thumb_id = get_post_thumbnail_id( $post_id );
        if ( ! $thumb_id ) return null;
        $src = wp_get_attachment_image_src( $thumb_id, $size );
        return $src ? $src[0] : null;
    }
}
