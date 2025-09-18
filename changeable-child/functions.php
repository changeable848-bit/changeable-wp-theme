<?php
/**
 * Changeable Child – functions
 */

/**
 * Theme supports
 */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('editor-styles'); // allow editor styles
    // Load the same self-hosted font CSS in the editor
    add_editor_style('assets/fonts/space-grotesk/space-grotesk.css');
});

/**
 * Enqueue styles (parent → font → child)
 */
add_action('wp_enqueue_scripts', function () {
    // Parent stylesheet (TT25)
    if (is_child_theme()) {
        wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css', [], null);
    }

    // Self-hosted Space Grotesk (variable) – served via a small CSS file
    wp_enqueue_style(
        'changeable-space-grotesk',
        get_stylesheet_directory_uri() . '/assets/fonts/space-grotesk/space-grotesk.css',
        [],
        null
    );

    // Child stylesheet (depends on parent + font)
    wp_enqueue_style(
        'changeable-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['parent-style', 'changeable-space-grotesk'],
        wp_get_theme()->get('Version')
    );
}, 20);

/**
 * Preload the variable font for faster paint
 * (Works with ttf; switch to .woff2 when you convert)
 */
add_filter('wp_resource_hints', function ($hints, $relation_type) {
    if ($relation_type !== 'preload') return $hints;

    $font_url = get_stylesheet_directory_uri() . '/assets/fonts/space-grotesk/SpaceGrotesk-VariableFont_wght.ttf';

    $hints[] = [
        'href'        => $font_url,
        'as'          => 'font',
        'type'        => 'font/ttf', // change to 'font/woff2' if you upgrade
        'crossorigin' => 'anonymous',
    ];
    return $hints;

    /**
 * Block styles: font-weight presets for headings & paragraphs
 */
add_action('init', function () {
    $weights = [
        ['weight-300', 'Light (300)'],
        ['weight-400', 'Regular (400)'],
        ['weight-500', 'Medium (500)'],
        ['weight-700', 'Bold (700)'],
    ];

    foreach ($weights as [$name, $label]) {
        register_block_style('core/heading', [
            'name'  => $name,
            'label' => $label,
        ]);
        register_block_style('core/paragraph', [
            'name'  => $name,
            'label' => $label,
        ]);
    }
});

}, 10, 2);
