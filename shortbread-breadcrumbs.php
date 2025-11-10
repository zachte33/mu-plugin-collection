<?php
/**
 * Plugin Name: Shortbread Breadcrumbs
 * Description: Smart hierarchical breadcrumbs via shortcode <code>[shortbread_breadcrumbs]</code>.  
 *              Fully configurable from Settings → Breadcrumbs.
 * Author:      Pink Dog Digital
 * Author URI:  https://pinkdogdigital.com
 * Version:     1.2.2
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* -------------------------------------------------------------------------- */
/* 1. SETTINGS PAGE
/* -------------------------------------------------------------------------- */
function shortbread_register_settings() {
    register_setting( 'shortbread_breadcrumbs_options', 'shortbread_breadcrumbs_opts', 'shortbread_sanitize_opts' );

    add_settings_section( 'shortbread_main_section', 'Shortbread Breadcrumbs Settings', '__return_false', 'shortbread_breadcrumbs' );

    add_settings_field( 'shortbread_info', 'How to use', 'shortbread_info_callback', 'shortbread_breadcrumbs', 'shortbread_main_section' );

    add_settings_field( 'shortbread_link_color', 'Link colour', 'shortbread_color_callback', 'shortbread_breadcrumbs', 'shortbread_main_section', [ 'key' => 'link_color', 'default' => '#0066cc' ] );
    add_settings_field( 'shortbread_link_hover', 'Link hover colour', 'shortbread_color_callback', 'shortbread_breadcrumbs', 'shortbread_main_section', [ 'key' => 'link_hover', 'default' => '#004499' ] );
    add_settings_field( 'shortbread_text_color', 'Text colour', 'shortbread_color_callback', 'shortbread_breadcrumbs', 'shortbread_main_section', [ 'key' => 'text_color', 'default' => '#333' ] );

    add_settings_field( 'shortbread_font_size', 'Font size', 'shortbread_text_callback', 'shortbread_breadcrumbs', 'shortbread_main_section', [ 'key' => 'font_size', 'default' => '0.9em', 'placeholder' => 'e.g. 14px, 1rem' ] );

    $sides = [ 'top', 'right', 'bottom', 'left' ];
    foreach ( $sides as $side ) {
        add_settings_field(
            "shortbread_padding_$side",
            ucfirst( $side ) . ' padding',
            'shortbread_text_callback',
            'shortbread_breadcrumbs',
            'shortbread_main_section',
            [
                'key'         => "padding_$side",
                'default'     => ( $side === 'left' ? '0' : '1em' ),
                'placeholder' => 'e.g. 10px, 1em, 0',
            ]
        );
    }
}
add_action( 'admin_init', 'shortbread_register_settings' );

function shortbread_info_callback() {
    ?>
    <p><strong>Shortcode:</strong> <code>[shortbread_breadcrumbs]</code></p>
    <p>Use anywhere – pages, posts, widgets, or <code>do_shortcode()</code>.</p>
    <p>Supports <strong>px, em, rem, %</strong> for padding & font size.</p>
    <?php
}

function shortbread_color_callback( $args ) {
    $opts  = get_option( 'shortbread_breadcrumbs_opts', [] );
    $value = $opts[ $args['key'] ] ?? $args['default'];
    printf( '<input type="text" name="shortbread_breadcrumbs_opts[%s]" value="%s" class="shortbread-color-picker" />', esc_attr( $args['key'] ), esc_attr( $value ) );
}

function shortbread_text_callback( $args ) {
    $opts  = get_option( 'shortbread_breadcrumbs_opts', [] );
    $value = $opts[ $args['key'] ] ?? $args['default'];
    printf(
        '<input type="text" name="shortbread_breadcrumbs_opts[%s]" value="%s" placeholder="%s" style="width:120px;" />',
        esc_attr( $args['key'] ),
        esc_attr( $value ),
        esc_attr( $args['placeholder'] )
    );
}

/* Sanitize + defaults */
function shortbread_sanitize_opts( $input ) {
    $defaults = [
        'link_color'     => '#0066cc',
        'link_hover'     => '#004499',
        'text_color'     => '#333',
        'font_size'      => '0.9em',
        'padding_top'    => '1em',
        'padding_right'  => '1em',
        'padding_bottom' => '1em',
        'padding_left'   => '0',
    ];
    $sanitized = [];
    foreach ( $defaults as $k => $v ) {
        $sanitized[ $k ] = isset( $input[ $k ] ) ? sanitize_text_field( $input[ $k ] ) : $v;
    }
    return $sanitized;
}

/* Menu + Reset */
function shortbread_add_menu() {
    add_options_page( 'Breadcrumbs', 'Breadcrumbs', 'manage_options', 'shortbread_breadcrumbs', 'shortbread_options_page' );
}
add_action( 'admin_menu', 'shortbread_add_menu' );

function shortbread_options_page() {
    if ( isset( $_GET['reset_shortbread'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'reset_shortbread' ) ) {
        delete_option( 'shortbread_breadcrumbs_opts' );
        echo '<div class="updated"><p><strong>Settings reset to defaults!</strong></p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Shortbread Breadcrumbs</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'shortbread_breadcrumbs_options' ); ?>
            <?php do_settings_sections( 'shortbread_breadcrumbs' ); ?>
            <?php submit_button(); ?>
        </form>

        <hr>
        <p>
            <a href="<?php echo wp_nonce_url( add_query_arg( 'reset_shortbread', '1' ), 'reset_shortbread' ); ?>" 
               class="button button-secondary" 
               onclick="return confirm('Reset all Shortbread settings to defaults?');">
               Reset to Defaults
            </a>
        </p>
    </div>
    <?php
}

/* Color picker */
function shortbread_admin_assets( $hook ) {
    if ( $hook !== 'settings_page_shortbread_breadcrumbs' ) return;
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){ $(".shortbread-color-picker").wpColorPicker(); });' );
}
add_action( 'admin_enqueue_scripts', 'shortbread_admin_assets' );

/* -------------------------------------------------------------------------- */
/* 2. BREADCRUMBS SHORTCODE
/* -------------------------------------------------------------------------- */
function shortbread_breadcrumbs_shortcode() {
    if ( is_front_page() ) return '';

    $output = '<nav class="shortbread-breadcrumbs" aria-label="Breadcrumb"><ol>';
    $output .= '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';

    if ( is_home() ) {
        $output .= '<li>' . esc_html( get_the_title( get_option( 'page_for_posts' ) ) ) . '</li>';
    } elseif ( is_search() ) {
        $output .= '<li>Search Results for: ' . esc_html( get_search_query() ) . '</li>';
    } elseif ( is_404() ) {
        $output .= '<li>404 Not Found</li>';
    } elseif ( is_archive() ) {
        // FIXED: Clean if/elseif instead of broken nested ternaries
        if ( is_category() ) {
            $title = single_cat_title( '', false );
        } elseif ( is_tag() ) {
            $title = single_tag_title( '', false );
        } elseif ( is_author() ) {
            $title = 'Posts by ' . get_the_author();
        } elseif ( is_day() ) {
            $title = get_the_date();
        } elseif ( is_month() ) {
            $title = get_the_date( 'F Y' );
        } elseif ( is_year() ) {
            $title = get_the_date( 'Y' );
        } else {
            $title = post_type_archive_title( '', false );
        }
        $output .= '<li>' . esc_html( $title ) . '</li>';
    } elseif ( is_singular() ) {
        $post = get_queried_object();

        // CPT Archive
        if ( $post->post_type !== 'page' && $post->post_type !== 'post' ) {
            $pto = get_post_type_object( $post->post_type );
            if ( $pto && $pto->has_archive ) {
                $output .= '<li><a href="' . esc_url( get_post_type_archive_link( $post->post_type ) ) . '">' . esc_html( $pto->labels->name ) . '</a></li>';
            }
        }

        // Ancestors
        if ( is_post_type_hierarchical( $post->post_type ) ) {
            foreach ( array_reverse( get_post_ancestors( $post ) ) as $id ) {
                $output .= '<li><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></li>';
            }
        }

        // Post category
        if ( $post->post_type === 'post' ) {
            $cats = get_the_category();
            if ( ! empty( $cats ) ) {
                $output .= '<li><a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '">' . esc_html( $cats[0]->name ) . '</a></li>';
            }
        }

        // Current
        $output .= '<li>' . esc_html( get_the_title( $post ) ) . '</li>';
    }

    $output .= '</ol></nav>';
    return $output;
}
add_shortcode( 'shortbread_breadcrumbs', 'shortbread_breadcrumbs_shortcode' );

/* -------------------------------------------------------------------------- */
/* 3. DYNAMIC STYLES – PADDING WORKS
/* -------------------------------------------------------------------------- */
function shortbread_dynamic_styles() {
    $opts = wp_parse_args( get_option( 'shortbread_breadcrumbs_opts', [] ), [
        'link_color'     => '#0066cc',
        'link_hover'     => '#004499',
        'text_color'     => '#333',
        'font_size'      => '0.9em',
        'padding_top'    => '1em',
        'padding_right'  => '1em',
        'padding_bottom' => '1em',
        'padding_left'   => '0',
    ] );

    $css = "
    .shortbread-breadcrumbs ol {
        list-style: none;
        margin: 0;
        font-size: {$opts['font_size']};
        display: flex;
        flex-wrap: wrap;
        gap: .5em;
        align-items: center;
        color: {$opts['text_color']};
        padding: {$opts['padding_top']} {$opts['padding_right']} {$opts['padding_bottom']} {$opts['padding_left']};
    }
    .shortbread-breadcrumbs a {
        color: {$opts['link_color']};
        text-decoration: none;
    }
    .shortbread-breadcrumbs a:hover {
        color: {$opts['link_hover']};
        text-decoration: underline;
    }
    .shortbread-breadcrumbs li:not(:last-child)::after {
        content: '>';
        margin-left: .5em;
        color: #999;
    }
        .single-content ol {
padding-left: 0px !important;
    }
    ";

    echo '<style id="shortbread-dynamic-css">' . wp_strip_all_tags( $css ) . '</style>';
}
add_action( 'wp_head', 'shortbread_dynamic_styles', 99 );

/* -------------------------------------------------------------------------- */
/* 4. SCHEMA.ORG BREADCRUMBLIST – ALSO FIXED
/* -------------------------------------------------------------------------- */
function shortbread_add_schema_markup() {
    if ( is_front_page() ) return;

    $items = [];
    $position = 1;

    $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => 'Home', 'item' => home_url( '/' ) ];

    if ( is_home() ) {
        $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => get_the_title( get_option( 'page_for_posts' ) ) ];
    } elseif ( is_search() ) {
        $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => 'Search Results for: ' . get_search_query() ];
    } elseif ( is_404() ) {
        $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => '404 Not Found' ];
    } elseif ( is_archive() ) {
        // FIXED: Same clean logic
        if ( is_category() ) {
            $title = single_cat_title( '', false );
        } elseif ( is_tag() ) {
            $title = single_tag_title( '', false );
        } elseif ( is_author() ) {
            $title = 'Posts by ' . get_the_author();
        } elseif ( is_day() ) {
            $title = get_the_date();
        } elseif ( is_month() ) {
            $title = get_the_date( 'F Y' );
        } elseif ( is_year() ) {
            $title = get_the_date( 'Y' );
        } else {
            $title = post_type_archive_title( '', false );
        }
        $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => $title ];
    } elseif ( is_singular() ) {
        $post = get_queried_object();

        if ( $post->post_type !== 'page' && $post->post_type !== 'post' ) {
            $pto = get_post_type_object( $post->post_type );
            if ( $pto && $pto->has_archive ) {
                $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => $pto->labels->name, 'item' => get_post_type_archive_link( $post->post_type ) ];
            }
        }

        if ( is_post_type_hierarchical( $post->post_type ) ) {
            foreach ( array_reverse( get_post_ancestors( $post ) ) as $id ) {
                $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => get_the_title( $id ), 'item' => get_permalink( $id ) ];
            }
        }

        if ( $post->post_type === 'post' ) {
            $cats = get_the_category();
            if ( ! empty( $cats ) ) {
                $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => $cats[0]->name, 'item' => get_category_link( $cats[0]->term_id ) ];
            }
        }

        $items[] = [ '@type' => 'ListItem', 'position' => $position++, 'name' => get_the_title( $post ) ];
    }

    if ( count( $items ) > 1 ) {
        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items
        ];
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>';
    }
}
add_action( 'wp_head', 'shortbread_add_schema_markup', 10 );