<?php
// Elfrida theme functions

/**
 * Define Constants
 *
 */
define( 'TEMPLATE_URL', get_stylesheet_directory_uri() ); // get_stylesheet_directory_uri()
//define("API_KEY", "75528e65e3a94933804f53798029c93f");

/**
 * Enqueues scripts and styles.
 *
 */

add_action( 'wp_enqueue_scripts', 'site_scripts_and_styles' );
function site_scripts_and_styles() {
    
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    //wp_enqueue_style( 'child-style', get_stylesheet_uri(), array( 'parent-style' ) );
    //wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/styles/style.css', array( 'parent-style' ) );
    
	//wp_enqueue_style( 'parent-stylesheet', get_template_directory_uri() . '/style.css' ); // Make sure the parent theme styles are properly inherited
    wp_dequeue_style( 'twenty-twenty-one-style-css' );
    
    $post_id = get_the_ID();
    
    // This is not really 'twentytwenty-style' but rather child-style, but because of setup for twentysixteen_scripts and the stylesheet dependencies, it is necessary
    // wp_enqueue_style( $handle, $src, $deps, $ver, $media );
    //$ver = filemtime( get_stylesheet_directory() . '/style.css');
    //wp_enqueue_style( 'twentytwentyone-style', get_stylesheet_uri(), NULL, $ver );
    //wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/styles/style.css', array( 'parent-style' ) );
    wp_enqueue_style( 'twenty-twenty-one-style', get_stylesheet_uri(), array( 'parent-style' ) );
	
	if ( is_admin() ) {		
		//wp_register_script('adminjs', TEMPLATE_URL . '/js/adminjs.js', array( 'jquery' ) );
		//wp_enqueue_script('adminjs');		
	}
		
	// Google Fonts
	// Open Sans -- default header font
	//wp_enqueue_style( 'wpb-google-fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,400,700,300', false );
	// Garamond -- for new concerts page &c.
	wp_enqueue_style( 'wpb-google-fonts', 'EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800', false );
	//https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap
    //<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital@0;1&display=swap" rel="stylesheet">
	
    // Enqueue the Dashicons script
	wp_enqueue_style( 'dashicons' );
	
}

// Load styles for MEJS audio player
//add_action( 'wp_footer', 'stc_theme_footer_scripts' );
function stc_theme_footer_scripts() {
	if ( wp_style_is( 'wp-mediaelement', 'enqueued' ) ) {
		wp_enqueue_style( 'stc-audio-player', get_stylesheet_directory_uri() . '/css/audio-player-style.css', array('wp-mediaelement'), '1.0' );
	}
}

// Enqueue admin styles
add_action( 'admin_enqueue_scripts', 'theme_enqueue_admin_scripts_and_styles' );
function theme_enqueue_admin_scripts_and_styles() {
    
    $filepath = get_stylesheet_directory() . '/admin.css';
    //$stat = stat($filepath); //$ver = $stat['ctime'];
    if ( !$ver = filemtime( $filepath ) ) { // version tag based on file modification time -- for cache-busting
        $ver = ""; // TODO: find a better alternative to nothing...
    }
    wp_enqueue_style( 'elfrida-admin',  get_stylesheet_directory_uri() . '/admin.css', NULL, $ver );
    
}

//add_action( 'after_setup_theme', 'stc_apostle_theme_setup', 11 ); //add_action( 'after_setup_theme', 'bweb_remove_post_formats', 11 );
function stc_apostle_theme_setup() {
    
    // Remove support for some post formats by specifying list to support
    add_theme_support( 'post-formats', array( 'aside', 'image', 'gallery', 'audio' ) ); // link, quote, video, status, chat
    
    add_editor_style( 'css/editor-style-apostle.css' );
	//add_editor_style( get_stylesheet_directory_uri() . '/css/editor-style-atchq.css' );
    
}

// Register theme menus
//add_action( 'init', 'register_my_menus' );
function register_my_menus() {
  register_nav_menus(
    array(
      	'secondary-header-nav' => __( 'Secondary Header Menu' ),
		'header-tabs' => __( 'Header Tabs Menu' ),
		//'secondary-header-nav-mobile' => __( 'Secondary Header Menu - Mobile' ),
      	'footer-nav' => __( 'Footer Menu' )
    )
  );
}


// Function to test if admin user is currently logged in -- this must be here instead of stc.php because it's needed for determining body classes (see below: atc_body_class)
function user_is_administrator() {
	
	if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
		$roles = ( array ) $current_user->roles;
		
		if ( in_array('administrator', $roles) ) { 
			return true;
		}
	}
	
	return false;
}


//add_filter( 'body_class', 'atc_body_class' );
function atc_body_class( $classes ) {
	
	if ( 
	is_singular( 'project' ) 
	OR is_post_type_archive('project')
	) {
		$classes[] = 'no-sidebar-full-width';
		$classes[] = 'no-sidebar';
	}
	
    // Determine whether to show admin-specific & troublshooting infos
	$current_user = wp_get_current_user();
    //if ( user_is_administrator() === true ) { 
    if ( current_user_can('edit_roles') || current_user_can('publish_events') ) {
    //if ( in_array( 'stc_administrator', $current_user->roles ) ) {
        $classes[] = 'admin';
        $classes[] = 'admin-view';
    }
    $devmode = get_query_var( 'devmode' ) ? get_query_var( 'devmode' ) : false;
    
    // Show troubleshooting info only for user stcdev in devmode
    $username = $current_user->user_login;
    if ( $username == 'stcdev' && $devmode ) { 
        $classes[] = 'dev-view';
    }
	
	if ( is_dev_site() ) { $classes[] = 'devsite'; }
	
	return $classes;
}

/*
 * Function to add Copyright info to the footer
 */
add_action( 'atc_footer_copyright', 'atc_footer_copyright', 10 );
//function to show the footer info, copyright information
if ( ! function_exists( 'atc_footer_copyright' ) ) :
function atc_footer_copyright() {
	
	$site_link = '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" ><span>' . get_bloginfo( 'name', 'display' ) . '</span></a>';
	$atc_link =  '<a href="'.esc_url( 'https://birdhive.com' ).'" target="_blank" title="'.esc_attr__( 'Birdhive Development & Design', 'atc' ).'" rel="designer"><span>'.__( 'Birdhive Development & Design', 'atc') .'</span></a>';

	$default_footer_value = sprintf( __( 'Copyright &copy; %1$s %2$s', 'atc' ), date( 'Y' ), $site_link ).' &mdash; '.sprintf( __( 'Website by %1$s', 'atc' ), $atc_link );

	$atc_footer_copyright = $default_footer_value;
	echo $atc_footer_copyright;
}
endif;

/* Limit excerpt length */
add_filter( 'excerpt_length', function($length) {
    return 15;
} );


// Replaces the excerpt "Read More" text by a link
//add_filter('excerpt_more', 'atc_excerpt_more');
function atc_excerpt_more($more) {
    global $post;
    return '<a class="moretag" href="'. get_permalink($post->ID) . '"><p><em>Read more...</em></p></a>';
}

add_filter( 'get_the_excerpt', 'excerpt_more_for_manual_excerpts' );
function excerpt_more_for_manual_excerpts( $excerpt ) {
    global $post;

    if ( has_excerpt( $post->ID ) ) {
        $excerpt .= atc_excerpt_more( '' );
    }

    return $excerpt;
}

// Allow select HTML tags in excerpts
// https://wordpress.stackexchange.com/questions/141125/allow-html-in-excerpt
function stc_allowedtags() {
    return '<style>,<br>,<em>,<strong>'; 
}

if ( ! function_exists( 'wpse_custom_wp_trim_excerpt' ) ) : 

    function wpse_custom_wp_trim_excerpt($excerpt) {
        
        global $post;
        
        $raw_excerpt = $excerpt;
        if ( '' == $excerpt ) {

            $excerpt = get_the_content('');
            $excerpt = strip_shortcodes( $excerpt );
            $excerpt = apply_filters('the_content', $excerpt);
            $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
            $excerpt = strip_tags($excerpt, stc_allowedtags()); /*IF you need to allow just certain tags. Delete if all tags are allowed */

            //Set the excerpt word count and only break after sentence is complete.
            $excerpt_word_count = 75;
            $excerpt_length = apply_filters('excerpt_length', $excerpt_word_count); 
            $tokens = array();
            $excerptOutput = '';
            $count = 0;

            // Divide the string into tokens; HTML tags, or words, followed by any whitespace
            preg_match_all('/(<[^>]+>|[^<>\s]+)\s*/u', $excerpt, $tokens);

            foreach ($tokens[0] as $token) { 

                if ($count >= $excerpt_length && preg_match('/[\,\;\?\.\!]\s*$/uS', $token)) { 
                // Limit reached, continue until , ; ? . or ! occur at the end
                    $excerptOutput .= trim($token);
                    break;
                }

                // Add words to complete sentence
                $count++;

                // Append what's left of the token
                $excerptOutput .= $token;
            }

            $excerpt = trim(force_balance_tags($excerptOutput));
            
            // After the content
            $excerpt .= atc_excerpt_more( '' );

            return $excerpt;   

        } else if ( has_excerpt( $post->ID ) ) {
            //$excerpt .= atc_excerpt_more( '' );
            //$excerpt .= "***";
        }
        return apply_filters('wpse_custom_wp_trim_excerpt', $wpse_excerpt, $raw_excerpt);
    }

endif;

add_post_type_support( 'page', 'excerpt' );



/* Function to allow for multiple different excerpt lengths as needed
 * Call as follows:
 * Adapted from https://www.wpexplorer.com/custom-excerpt-lengths-wordpress/
 *
 */
function atc_get_excerpt( $args = array() ) {

	// Defaults
	$defaults = array(
		'post'            => '',
		'length'          => 40,
		'readmore'        => false,
		'readmore_text'   => esc_html__( 'read more', 'text-domain' ),
		'readmore_after'  => '',
		'custom_excerpts' => true,
		'disable_more'    => false,
	);

	// Apply filters
	$defaults = apply_filters( 'atc_get_excerpt_defaults', $defaults );

	// Parse args
	$args = wp_parse_args( $args, $defaults );

	// Apply filters to args
	$args = apply_filters( 'atc_get_excerpt_args', $defaults );

	// Extract
	extract( $args );

	// Get global post data
	if ( ! $post ) {
		global $post;
	}

	// Get post ID
	$post_id = $post->ID;

	// Check for custom excerpt
	if ( $custom_excerpts && has_excerpt( $post_id ) ) {
		$output = $post->post_excerpt;
	}

	// No custom excerpt...so lets generate one
	else {
        
        $readmore_link = '<br /><a href="' . get_permalink( $post_id ) . '" class="readmore">' . $readmore_text . $readmore_after . '</a>';

		// Check for more tag and return content if it exists
		if ( ! $disable_more && strpos( $post->post_content, '<!--more-->' ) ) {
			$output = apply_filters( 'the_content', get_the_content( $readmore_text . $readmore_after ) );
		}

		// No more tag defined so generate excerpt using wp_trim_words
		else {

			// Generate excerpt
			$output = wp_trim_words( strip_shortcodes( $post->post_content ), $length );

			// Add readmore to excerpt if enabled
			if ( $readmore ) {

				$output .= apply_filters( 'atc_readmore_link', $readmore_link );

			}

		}

	}

	// Apply filters and echo
	return apply_filters( 'atc_get_excerpt', $output );

}



/*** EVENTS MANAGER ***/
// TODO: move to stc plugin?

//add_filter('em_cpt_event', 'em_enable_event_revisions', 10, 1);
//add_filter('em_cpt_event_recurring', 'em_enable_event_revisions', 10, 1);
function em_enable_event_revisions( $args ) {
	if( !in_array( 'revisions', $args['supports'] ) ) {
		$args['supports'][] = 'revisions';
	}
	return $args;
}


/* Temporary function for forcing update to try to get converted events to show up properly on front end (single event pages) */
//add_action('init','tmp_em_events_post_import_processing');
function tmp_em_events_post_import_processing(){

    $posts = get_posts( array('post_type' => 'event', 'numberposts' => 3 ) );

    foreach ( $posts as $post ):
        //$post['post_content'] = 'This is the updated content.';
        wp_update_post( $post );
        /*if (is_wp_error($post_id)) {
            $errors = $post_id->get_error_messages();
            foreach ($errors as $error) {
                echo $error;
            }
        }*/
    endforeach;
    
}

?>