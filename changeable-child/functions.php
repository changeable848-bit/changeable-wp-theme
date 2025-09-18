<?php
/**
 * Changeable Child – functions.php
 */

/**
 * Theme supports
 */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('editor-styles');

    // Load the same self-hosted font CSS in the editor
    add_editor_style('assets/fonts/space-grotesk/space-grotesk.css');
});

/**
 * Enqueue styles (parent → font → child)
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

    // Self-hosted Space Grotesk
    wp_enqueue_style(
        'changeable-space-grotesk',
        get_stylesheet_directory_uri() . '/assets/fonts/space-grotesk/space-grotesk.css',
        [],
        null
    );

    // Child stylesheet
    wp_enqueue_style(
        'changeable-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['parent-style', 'changeable-space-grotesk'],
        wp_get_theme()->get('Version')
    );
}, 20);

/**
 * Preload the Space Grotesk variable font
 */
add_filter('wp_resource_hints', function ($hints, $relation_type) {
    if ($relation_type !== 'preload') return $hints;

    $font_url = get_stylesheet_directory_uri() . '/assets/fonts/space-grotesk/SpaceGrotesk-VariableFont_wght.ttf';

    $hints[] = [
        'href'        => $font_url,
        'as'          => 'font',
        'type'        => 'font/ttf', // switch to 'font/woff2' after conversion
        'crossorigin' => 'anonymous',
    ];
    return $hints;
}, 10, 2);

/**
 * Portfolio (case studies) – Custom Post Type
 */
add_action('init', function () {
    $labels = [
        'name'                  => 'Portfolio',
        'singular_name'         => 'Case Study',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Case Study',
        'edit_item'             => 'Edit Case Study',
        'new_item'              => 'New Case Study',
        'view_item'             => 'View Case Study',
        'search_items'          => 'Search Portfolio',
        'not_found'             => 'No case studies found',
        'not_found_in_trash'    => 'No case studies found in Trash',
        'all_items'             => 'All Case Studies',
        'menu_name'             => 'Portfolio',
        'name_admin_bar'        => 'Case Study',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'has_archive'        => true,                 // /portfolio/
        'rewrite'            => ['slug' => 'portfolio'],
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
        'capability_type'    => 'post',
    ];

    register_post_type('portfolio', $args);
});

/**
 * Flush rewrite rules on theme switch (one-time)
 */
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});
