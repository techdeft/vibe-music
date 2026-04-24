<?php
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

$playlists = get_posts([
    'post_type' => 'vibe_playlist',
    'post_status' => 'any',
    'numberposts' => -1
]);

echo "Found " . count($playlists) . " playlists\n";
foreach ($playlists as $p) {
    echo "ID: {$p->ID}, Title: {$p->post_title}, Status: {$p->post_status}, Author: {$p->post_author}\n";
    $tracks = get_post_meta($p->ID, '_vibe_playlist_tracks', true);
    echo "Tracks: " . json_encode($tracks) . "\n\n";
}
