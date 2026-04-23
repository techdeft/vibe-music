<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo esc_html( get_option( 'vibe_player_name', 'VIBE' ) ); ?> — <?php echo esc_html( get_option( 'vibe_tagline', 'Live the Sound' ) ); ?></title>
    <?php wp_head(); ?>
    <style>
        /* Prevent WordPress theme from interfering */
        body { margin: 0 !important; padding: 0 !important; background: #0A0A0A !important; }
        #wpadminbar { display: none !important; }
        html { margin-top: 0 !important; }
        /* Hide any theme header/footer */
        header:not(#vibe-app *), footer:not(#vibe-app *), .site-header, .site-footer { display: none !important; }
    </style>
</head>
<body>
    <div id="vibe-app"></div>
    <?php wp_footer(); ?>
</body>
</html>
