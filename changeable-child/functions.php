<?php
/**
 * Theme: Changeable (child)
 * Purpose: setup, fonts, Portfolio CPT, redirects, and Solutions Hub shortcode
 */

if ( ! defined('ABSPATH') ) { exit; }

/**
 * Theme supports + editor styles
 */
add_action('after_setup_theme', function () {
  add_theme_support('wp-block-styles');
  add_theme_support('align-wide');
  add_theme_support('responsive-embeds');
  add_theme_support('editor-styles');

  // Load editor styles (so the block editor uses your font + spacing)
  add_editor_style('assets/editor.css');
});

/**
 * Front-end fonts (uses your existing variable Space Grotesk files & CSS)
 * File already in repo: /assets/fonts/space-grotesk/space-grotesk.css
 */
add_action('wp_enqueue_scripts', function () {
  $css_path = get_stylesheet_directory() . '/assets/fonts/space-grotesk/space-grotesk.css';
  $css_uri  = get_stylesheet_directory_uri() . '/assets/fonts/space-grotesk/space-grotesk.css';

  // Version with filemtime so browsers pick up changes immediately
  $ver = file_exists($css_path) ? filemtime($css_path) : null;

  wp_enqueue_style(
    'changeable-space-grotesk',
    $css_uri,
    [],
    $ver
  );
}, 5);

/**
 * Portfolio Custom Post Type
 * Makes "Portfolio" appear in WP-Admin sidebar.
 */
add_action('init', function () {
  $labels = [
    'name'               => __('Portfolio', 'changeable'),
    'singular_name'      => __('Case Study', 'changeable'),
    'menu_name'          => __('Portfolio', 'changeable'),
    'name_admin_bar'     => __('Case Study', 'changeable'),
    'add_new'            => __('Add New', 'changeable'),
    'add_new_item'       => __('Add New Case Study', 'changeable'),
    'edit_item'          => __('Edit Case Study', 'changeable'),
    'new_item'           => __('New Case Study', 'changeable'),
    'view_item'          => __('View Case Study', 'changeable'),
    'view_items'         => __('View Portfolio', 'changeable'),
    'search_items'       => __('Search Portfolio', 'changeable'),
    'not_found'          => __('No case studies found.', 'changeable'),
    'not_found_in_trash' => __('No case studies found in Trash.', 'changeable'),
    'all_items'          => __('All Case Studies', 'changeable'),
  ];

  register_post_type('portfolio', [
    'labels'             => $labels,
    'public'             => true,
    'show_in_menu'       => true,
    'show_in_rest'       => true,              // Gutenberg compatible
    'menu_position'      => 21,
    'menu_icon'          => 'dashicons-portfolio',
    'has_archive'        => true,              // /portfolio/
    'rewrite'            => ['slug' => 'portfolio'],
    'supports'           => [
      'title',
      'editor',
      'excerpt',
      'thumbnail',
      'revisions'
    ]
  ]);
});

/**
 * Lightweight redirects (301)
 * - /blog-insights/*  -> /blog/*
 * - /work-portfolio/* -> /portfolio/*
 */
add_action('template_redirect', function () {
  $request_uri = $_SERVER['REQUEST_URI'] ?? '';
  $host        = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];

  if (preg_match('#^/blog-insights(?:/.*)?$#', $request_uri)) {
    $target = preg_replace('#^/blog-insights#', '/blog', $request_uri);
    wp_safe_redirect( $host . $target, 301 );
    exit;
  }

  if (preg_match('#^/work-portfolio(?:/.*)?$#', $request_uri)) {
    $target = preg_replace('#^/work-portfolio#', '/portfolio', $request_uri);
    wp_safe_redirect( $host . $target, 301 );
    exit;
  }
});

/**
 * Solutions Hub shortcode
 * Usage in pages/templates: [changeable_solutions_grid columns="3" limit="12"]
 * Pulls child pages under the parent page /solutions/
 */
function changeable_render_solutions_grid($atts = []) {
  $atts = shortcode_atts([
    'parent_path' => 'solutions',
    'columns'     => 3,
    'limit'       => 12,
  ], $atts, 'changeable_solutions_grid');

  $parent = get_page_by_path( trim($atts['parent_path'], " /") );
  if ( ! $parent ) {
    return '<p><!-- Create a page at /solutions/ to populate this grid. --></p>';
  }

  $q = new WP_Query([
    'post_type'      => 'page',
    'post_parent'    => $parent->ID,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'posts_per_page' => intval($atts['limit']),
    'no_found_rows'  => true,
  ]);

  if ( ! $q->have_posts() ) {
    return '<p><!-- No child pages under /solutions/ yet. --></p>';
  }

  $cols = max(1, min(6, intval($atts['columns'])));

  ob_start(); ?>
  <div class="chg-solutions-grid chg-cols-<?php echo esc_attr($cols); ?>">
    <?php while ( $q->have_posts() ) : $q->the_post(); ?>
      <article class="chg-solution-item">
        <a class="chg-solution-media" href="<?php the_permalink(); ?>">
          <?php if ( has_post_thumbnail() ) {
            the_post_thumbnail('large', ['class' => 'chg-solution-thumb']);
          } ?>
        </a>
        <h3 class="chg-solution-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <div class="chg-solution-excerpt">
          <?php echo wp_kses_post( wpautop( get_the_excerpt() ?: '' ) ); ?>
          <p class="chg-more"><a href="<?php the_permalink(); ?>">Learn more →</a></p>
        </div>
      </article>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('changeable_solutions_grid', 'changeable_render_solutions_grid');
