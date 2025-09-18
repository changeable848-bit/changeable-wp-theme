<?php
/**
 * Theme: Changeable (TT25 child)
 * Purpose: setup, helpers, redirects, patterns, and Solutions Hub shortcode
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
  add_editor_style('styles/blocks.css');
});

/**
 * Register Block Pattern Category
 */
add_action('init', function () {
  if ( function_exists('register_block_pattern_category') ) {
    register_block_pattern_category(
      'changeable',
      ['label' => __('Changeable', 'changeable')]
    );
  }
});

/**
 * Font preloads (expects WOFF2 files in /assets/fonts)
 * Adjust filenames if yours differ.
 */
add_filter('wp_resource_hints', function($hints, $relation){
  if ($relation !== 'preload') return $hints;

  $font_dir = get_stylesheet_directory_uri() . '/assets/fonts';
  $candidates = [
    $font_dir . '/space-grotesk-regular.woff2',
    $font_dir . '/space-grotesk-medium.woff2',
    $font_dir . '/space-grotesk-bold.woff2',
  ];
  foreach ($candidates as $url) {
    $hints[] = [
      'href' => $url,
      'as'   => 'font',
      'type' => 'font/woff2',
      'crossorigin' => 'anonymous'
    ];
  }
  return $hints;
}, 10, 2);

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
 * Usage in templates/content: [changeable_solutions_grid]
 * Pulls child pages under the parent page with path 'solutions'
 * and renders a simple responsive grid (image, title, excerpt).
 */
function changeable_render_solutions_grid($atts = []) {
  $atts = shortcode_atts([
    'parent_path' => 'solutions',
    'columns'     => 3,
    'limit'       => 12,
  ], $atts, 'changeable_solutions_grid');

  $parent = get_page_by_path( trim($atts['parent_path'], " /") );
  if ( ! $parent ) {
    return '<p><!-- Solutions parent page not found. Create /solutions/ --></p>';
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
    return '<p><!-- No child pages under /solutions/ yet. Add some. --></p>';
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
