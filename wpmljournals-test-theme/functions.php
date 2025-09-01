<?php

require_once(get_template_directory() . "/constants.php");

wp_cache_add_non_persistent_groups([
    'icl_tax_permalink_filter', // This fixes a bug where WordPress generates wrong language URLs for terms and categories
]);

// Apparently we can't just set the custom permalink in the 'slug' parameter while registering a custom post type
// (That variable just seems to be meant to be a prefix)
// So we need to instead hack the permastruct variable after the custom post type has been created
// (This should run *after* the text/article post type has been created)
add_action('init', function() {
    global $wp_rewrite;

    // Note that %post_id% is not actually automatically replaced
    // This is why we also need to define a `post_type_link` action
    $wp_rewrite->extra_permastructs['article']['struct'] = 'texts/%post_id%/%article%/';

    // // We don't flush now because we already flush when declaring the other rewriting rules
    // flush_rewrite_rules();
});

// Needed to set %post_id% in the text URLs
function custom_text_permalink($permalink, $post) {
    if ($post->post_type == 'article') {
        $permalink = str_replace("%post_id%", $post->ID, $permalink);
    }
    return $permalink;
}
add_filter('post_type_link', 'custom_text_permalink', 10, 2);

// // Disable redirects by partial slug matching, since this seems to produce false positives
// add_filter('do_redirect_guess_404_permalink', function() {
//     return false;
// });

function icp_enqueue_styles() {
    wp_enqueue_style('icp-scratch-style', get_stylesheet_uri(), [], filemtime(get_template_directory() . '/style.css'));
    wp_enqueue_style('google-font-eb-garamond', 'https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&display=swap', false);
    wp_enqueue_style('google-font-cormorant-garamond', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap', false);
	// wp_enqueue_style('noto-serif-sc-font', 'https://fonts.googleapis.com/css2?family=Noto+Serif+SC&display=swap', false);
    // wp_enqueue_style('noto-serif-tc-font', 'https://fonts.googleapis.com/css2?family=Noto+Serif+TC&display=swap', false);
    // wp_enqueue_style('noto-serif-jp-font', 'https://fonts.googleapis.com/css2?family=Noto+Serif+JP&display=swap', false);
    // wp_enqueue_style('noto-serif-kr-font', 'https://fonts.googleapis.com/css2?family=Noto+Serif+KR&display=swap', false);
    // wp_enqueue_style('noto-naskh-arabic-font', 'https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic&display=swap', false);
    // wp_enqueue_style('amiri-font', 'https://fonts.googleapis.com/css2?family=Amiri&display=swap', false);
	// wp_enqueue_style('david-libre-font', 'https://fonts.googleapis.com/css2?family=David+Libre&display=swap', false);
	// wp_enqueue_style('noto-serif-devanagari-font', 'https://fonts.googleapis.com/css2?family=Noto+Serif+Devanagari&display=swap', false);
}
add_action('wp_enqueue_scripts', 'icp_enqueue_styles');

// This loads the string translations
add_action('after_setup_theme', function() {
    load_theme_textdomain('icp-scratch', get_template_directory() . '/langs');
});

require_once(get_template_directory() . "/util/routes.php");

// Set up the rewriting rules for custom routes
add_filter('query_vars', 'add_issue_query_vars');
add_action('init', 'add_issue_routes');
// add_action('after_switch_theme', 'add_issue_routes');

// Restrict REST API access to authenticated users only
add_filter( 'rest_authentication_errors', function( $result ) {
    // If a previous authentication check was applied,
    // pass that result along without modification.
    if ( true === $result || is_wp_error( $result ) ) {
        return $result;
    }

    // No authentication has been performed yet.
    // Return an error if user is not logged in.
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_not_logged_in',
            __( 'You are not currently logged in.' ),
            array( 'status' => 401 )
        );
    }

    // Our custom authentication check should have no effect
    // on logged-in requests
    return $result;
});

require_once(get_template_directory() . "/util/metadata.php");

function title_function($title, $sep, $seplocation) {
    $fix = custom_title();
    if ($fix) {
        return match ($seplocation) {
            'left' => "$sep $fix",
            'right' => "$fix $sep",
            default => $fix,
        };
    } else {
        return $title;
    }
}
// IMPORTANT: We set the priority to 16 so we can override Yoast SEO's 15
// Otherwise Yoast will mess up the titles
add_filter('wp_title', 'title_function', 16, 3);

// Fix canonical URLs for custom pages, which will otherwise point to the home page
add_filter('wpseo_canonical', function($canonical) {
    $tail = custom_canonical_url();
    if ($tail) {
        return URL_PREFIX . $tail;
    } else {
        return $canonical;
    }
});

// This is necessary for WPML to generate the right alternate link metadata in custom routes
// Otherwise it will just link back to the home page
function custom_wpml_alternate_hreflang_func($url, $lang_code) {
    $fix = map_custom_url($lang_code);
    if ($fix) {
        $url = str_replace("/$lang_code/", $url, $fix);
    }
    return $url;
}
add_filter('wpml_alternate_hreflang', 'custom_wpml_alternate_hreflang_func', 10, 2);

// Disable Yoast description setting. We have our custom functionality for that.
add_filter('wpseo_frontend_presenters', function($presenters) {
    return array_filter($presenters, function($presenter) {
        return !$presenter instanceof Yoast\WP\SEO\Presenters\Meta_Description_Presenter;
    });
});

// Some utility functions below

function format_mysql_date_with_specificity($date_str, $specificity) {
    $date = new Datetime($date_str);
    return match ((int)$specificity) {
        0 => null,
        1 => $date->format("Y"),
        2 => $date->format("Y-m"),
        3 => $date->format("Y-m-d"),
        default => assert(false)
    };
}

function throw_404() {
    // FIXME this doesn't actually throw a 404 status code
    status_header(404);
	nocache_headers();
    get_template_part('pages/404');
    exit;
}

function select_tcp_for_lang($lang) {
    return match ($lang) {
        'en' => 'tic',
        'it' => 'ipc',
        //'es' => 'epc',
        //'tr' => 'komunist-parti',
        default => "tic"
    };
}

function select_cl_for_lang($lang) {
    return match ($lang) {
        // 'en' => 'communist-left',
        'it' => 'comunismo',
        // 'fr' => 'la-gauche-comuniste',
        // 'es' => 'la-izquierda-comunista',
        default => null
    };
}

function render_content($content) {
    // NOTE: This does not process teaser blocks
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content); // This is done in the `the_content` function too for some reason
    return $content;
}

// TODO move to a gettext file?
function index_name_table($lang, $slug) {
    return match ([$lang, $slug]) {
        ['en', 'marx-engels-economic-works'] => "Marx-Engels Economic Works",
        ['en', 'organic-centralism']         => "Organic Centralism",
        ['en', 'against-imperialist-wars']   => "Against Imperialist Wars",
        ['en', 'revolution-in-russia']       => "Revolution in Russia",
        ['en', 'theory-of-crisis']           => "Marxist Theory of Crisis",
        ['en', 'union-question']             => "Union Question",
        ['en', 'theory-of-knowledge']        => "Marxist Theory of Knowledge",
        ['en', 'anti-parliamentarism']       => "Anti-Parliamentarism",
        ['en', 'national-question']          => "National Question",
        ['en', 'middle-east-north-africa']   => "Middle East and North Africa",
        ['en', 'africa']                     => "Africa",
        ['en', 'imperialism-and-oil']        => "Imperialism & Oil",
        ['en', 'china']                      => "China",
        ['en', 'latin-america']              => "Latin America",
        ['en', 'military-question']          => "Military Question",
        ['en', 'workers-movement-usa']       => "Workers' Movement in the USA",
        ['en', 'religion']                   => "Religion",
        ['en', 'racial-question-usa']        => "The Racial Question in the USA",

        ['it', 'marx-engels-economic-works'] => "Opere Economiche di Marx-Engels",
        ['it', 'organic-centralism']         => "Centralismo Organico",
        ['it', 'against-imperialist-wars']   => "Contro la Guerra Imperialista",
        ['it', 'revolution-in-russia']       => "Rivoluzione in Russia",
        ['it', 'theory-of-crisis']           => "Teoria Marxista delle Crisi",
        ['it', 'union-question']             => "Questione Sindacale",
        ['it', 'theory-of-knowledge']        => "Teoria Marxista della Conoscenza",
        ['it', 'anti-parliamentarism']       => "Antiparliamentarismo",
        ['it', 'national-question']          => "Questione Nazionale",
        ['it', 'middle-east-north-africa']   => "Medio Oriente e Nordafrica",
        ['it', 'africa']                     => "Africa",
        ['it', 'imperialism-and-oil']        => "Imperialismo e Petrolio",
        ['it', 'china']                      => "Cina",
        ['it', 'latin-america']              => "America Latina",
        ['it', 'military-question']          => "Questione Militare",
        ['it', 'workers-movement-usa']       => "Movimento Operaio negli USA",
        ['it', 'religion']                   => "Religione",
        ['it', 'racial-question-usa']        => "Questione Razziale negli USA",

        ['es', 'marx-engels-economic-works'] => "Obras Económicas de Marx y Engels",
        ['es', 'organic-centralism']         => "Centralismo Orgánico",
        ['es', 'against-imperialist-wars']   => "Contra la Guerra Imperialista",
        ['es', 'revolution-in-russia']       => "Revolución en Rusia",
        ['es', 'theory-of-crisis']           => "Teoría Marxista del la Crisis",
        ['es', 'union-question']             => "Cuestión Sindical",
        ['es', 'theory-of-knowledge']        => "Teoría Marxista del Conocimiento",
        ['es', 'anti-parliamentarism']       => "Antiparliamentarismo",
        ['es', 'national-question']          => "Cuestión Nacional",
        ['es', 'middle-east-north-africa']   => "Medio Oriente y Norte de África",
        ['es', 'africa']                     => "África",
        ['es', 'imperialism-and-oil']        => "Imperialismo y Petróleo",
        ['es', 'china']                      => "China",
        ['es', 'latin-america']              => "América Latina",
        ['es', 'military-question']          => "Cuestión Militar",
        ['es', 'workers-movement-usa']       => "Movimento Obrero en USA",
        ['es', 'religion']                   => "Religión",
        ['es', 'racial-question-usa']        => "Cuestión Racial en USA",

        default => null,
    };
}

function index_name($slug, $lang=null) {
    $lang = $lang ?: apply_filters('wpml_current_language', NULL);
    return index_name_table($lang, $slug) ?: (index_name_table('en', $slug) ?: $slug);
}

function dir_attribute($lang=null) {
    $dir = match ($lang ?? apply_filters('wpml_current_language', null)) {
        'ar', 'he', 'ur', 'fa' => 'rtl',
        default => 'ltr',
    };
    return "dir=\"$dir\"";
}

require_once(get_template_directory() . "/util/sitemaps.php");

// Register meta boxes for the post editor
require_once(get_template_directory() . "/editor/indices.php");
register_indices_meta_box();

?>
