<?php
/**
 * Changeable Child – functions
 */

/**
 * Theme supports + load editor styles
 */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('editor-styles');

    // Load font-face CSS in the editor so Gutenberg renders Space Grotesk
    add_editor_style([
        'assets/fonts/space-grotesk/space-grotesk.css',
        'assets/editor.css' // optional file if you created it; safe to leave even if missing
    ]);
});

/**
 * Enqueue styles (parent → font → child), with cache-busting and late priority
 */
add_action('wp_enqueue_scripts', function () {
    // Parent stylesheet (TT25)
    if (is_child_theme()) {
        wp_enqueue_style(
            'parent-style',
            get_template_directory_uri() . '/style.css',
            [],
            null
        );
    }

    // Cache-busting versions
    $font_css_path  = get_stylesheet_directory() . '/assets/fonts/space-grotesk/space-grotesk.css';
    $child_css_path = get_stylesheet_directory() . '/style.css';
    $font_ver  = file_exists($font_css_path)  ? filemtime($font_css_path)  : null;
    $child_ver = file_exists($child_css_path) ? filemtime($child_css_path) : wp_get_theme()->get('Version');

    // Self-hosted Space Grotesk (variable)
    wp_enqueue_style(
        'changeable-space-grotesk',
        get_stylesheet_directory_uri() . '/assets/fonts/space-grotesk/space-grotesk.css',
        [],
        $font_ver
    );

    // Child stylesheet
    wp_enqueue_style(
        'changeable-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['parent-style', 'changeable-space-grotesk'],
        $child_ver
    );
}, 99);

/**
 * Preload the variable font for faster first paint
 * (Update to .woff2 and 'font/woff2' after you convert)
 */
add_filter('wp_resource_hints', function ($hints, $relation_type) {
    if ($relation_type !== 'preload') return $hints;

    $font_url = get_stylesheet_directory_uri() . '/assets/fonts/space-grotesk/SpaceGrotesk-VariableFont_wght.ttf';

    $hints[] = [
        'href'        => $font_url,
        'as'          => 'font',
        'type'        => 'font/ttf',
        'crossorigin' => 'anonymous',
    ];
    return $hints;
}, 10, 2);
