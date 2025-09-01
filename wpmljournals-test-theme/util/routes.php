<?php

// -- Rewriting rule functions

function add_issue_query_vars($vars) {
	$vars[] = 'page_type';
	$vars[] = 'periodical_slug';
	$vars[] = 'issue_slug';
	$vars[] = 'index_slug';
	return $vars;
}

function add_issue_routes() {
	add_rewrite_rule(
		'^texts/?$',
		'index.php?page_type=texts',
		'top'
	);
	add_rewrite_rule(
		'^categories/?$',
		'index.php?page_type=categories',
		'top'
	);
	add_rewrite_rule(
		'^indices/?$',
		'index.php?page_type=indices',
		'top'
	);
	add_rewrite_rule(
		'^indices/([^/]+)/?$',
		'index.php?page_type=indices&index_slug=$matches[1]',
		'top'
	);
	add_rewrite_rule(
		'^search/?$',
		'index.php?page_type=search',
		'top'
	);
	add_rewrite_rule(
		'^all-publications/?$',
		'index.php?page_type=publications',
		'top'
	);
	add_rewrite_rule(
		'^periodicals/?$',
		'index.php?page_type=periodicals',
		'top'
	);
	add_rewrite_rule(
		'^periodicals/([^/]+)/?$',
		'index.php?page_type=periodicals&periodical_slug=$matches[1]',
		'top'
	);
	add_rewrite_rule(
		'^periodicals/([^/]+)/([^/]+)/?$',
		'index.php?page_type=periodicals&periodical_slug=$matches[1]&issue_slug=$matches[2]',
		'top'
	);
	flush_rewrite_rules();
}

// -- Template dispatching function

function dispatch_page_template() {
	if (get_query_var('page_type')) {
		switch (get_query_var('page_type')) {
			case 'search': return 'pages/search';
			case 'publications': return 'pages/all-publications';
			case 'texts': return 'pages/all-texts';
			case 'categories': return 'pages/all-categories';
			case 'indices':
				if (get_query_var('index_slug')) {
					return 'pages/index';
				} else {
					return 'pages/all-indices';
				}
			case 'periodicals': {
				if (get_query_var('periodical_slug')) {
					if (get_query_var('issue_slug')) {
						return 'pages/issue';
					} else {
						return 'pages/periodical';
					}
				} else {
					return 'pages/all-periodicals';
				}
			}
		}
    } else if (is_home()) {
        return 'pages/home';
    } else if (is_category()) {
        return 'pages/category';
    } else if (is_singular()) {
        return 'pages/post';
    }

	// If none matched:
	throw_404();
}

// The `is_home` function returns `true` in custom routes for some reason, so we add this stricter function
function really_is_home() {
	return is_home() && !get_query_var('page_type');
}

// -- Functions used to generate URLs (for links in templates)

function get_url_base($lang=null) {
	$cur_lang = $lang ?? apply_filters('wpml_current_language', null);
	return "/{$cur_lang}";
}

// Prepends the language prefix to an URL
function lang_url($tail, $lang=null) {
	return get_url_base($lang) . $tail;
}

// (`home_url` is already taken)
function homepage_url() {
	return lang_url("/");
}

function all_texts_url($lang=null) {
	return lang_url("/texts/", $lang);
}

function text_url($id, $slug, $lang=null) {
	return all_texts_url($lang) . "$id/$slug/";
}

function all_periodicals_url($lang=null) {
	return lang_url("/periodicals/", $lang);
}

function all_categories_url($lang=null) {
	return lang_url("/categories/", $lang);
}

function all_indices_url($lang=null) {
	return lang_url("/indices/", $lang);
}

function index_url($slug, $lang=null) {
	return all_indices_url($lang) . "$slug/";
}

function periodical_url($slug, $lang=null) {
	return all_periodicals_url($lang) . "{$slug}/";
}

function issue_url($periodical_slug, $issue_slug, $lang=null) {
	return periodical_url($periodical_slug, $lang) . "{$issue_slug}/";
}

function all_publications_url($lang=null) {
	return lang_url("/all-publications/", $lang);
}

function search_url($lang=null) {
	return lang_url("/search/", $lang);
}

function contact_url($lang=null) {
	return lang_url("/contact/", $lang);
}

// class PageData {
// 	public $template_name;
// 	public $title;
// 	public $url;
// }

// Get custom page URL corresponding to query parameters
// Returns null if not a custom page
function map_custom_url($lang) {
    $page_type = get_query_var('page_type');
    if ($page_type) {
		switch ($page_type) {
			case 'search': return search_url($lang);
			case 'contact': return contact_url($lang);
			case 'publications': return all_publications_url($lang);
			case 'texts': return all_texts_url($lang);
			case 'categories': return all_categories_url($lang);
			case 'periodicals': {
                $mag_slug = get_query_var('periodical_slug');
				if ($mag_slug) {
                    $issue_slug = get_query_var('issue_slug');
					if ($issue_slug) {
						return issue_url($mag_slug, $issue_slug, $lang);
					} else {
						return periodical_url($mag_slug, $lang);
					}
				} else {
					return all_periodicals_url($lang);
				}
			}
		}
    }
    return null;
}
