<?php
/**
 * Theme functions and definitions
 */

if ( ! function_exists( 'changeable_child_setup' ) ) {
    function changeable_child_setup() {
        // Add theme support features
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'editor-styles' );
    }
}
add_action( 'after_setup_theme', 'changeable_child_setup' );

/**
 * Enqueue styles and fonts
 */
function changeable_child_enqueue_styles() {
    // Load parent theme stylesheet first (if this is a child theme)
    if ( is_child_theme() ) {
        wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    }

    // Load Google Fonts - Space Grotesk
    wp_enqueue_style(
        'changeable-google-fonts',
        'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap',
        false,
        null
    );

    // Load child theme stylesheet
    wp_enqueue_style(
        'changeable-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'parent-style', 'changeable-google-fonts' ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'changeable_child_enqueue_styles' );
