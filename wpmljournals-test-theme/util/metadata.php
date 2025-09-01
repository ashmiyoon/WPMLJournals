<?php

require_once(get_template_directory() . "/util/routes.php");

function custom_canonical_url() {
    $page_type = get_query_var('page_type');
    if ($page_type) {
		switch ($page_type) {
			case 'search': return search_url();
			case 'contact': return contact_url();
            case 'categories': return all_categories_url();
            case 'texts': return all_texts_url();
			case 'publications': return all_publications_url();
			case 'indices': {
                $index_slug = get_query_var('index_slug');
				if ($index_slug) {
					return index_url($index_url);
				} else {
					return all_indices_url();
				}
            }
			case 'periodicals': {
                $mag_slug = get_query_var('periodical_slug');
				if ($mag_slug) {
                    $issue_slug = get_query_var('issue_slug');
					if ($issue_slug) {
                        return issue_url($mag_slug, $issue_slug);
					} else {
						return periodical_url($mag_slug);
					}
				} else {
					return all_periodicals_url();
				}
			}
		}
    } else {
        return null;
    }
}

function custom_title() {
    $page_type = get_query_var('page_type');
    if ($page_type) {
		switch ($page_type) {
			case 'search': return __("Search", "icp-scratch");
			case 'contact': return __("Contact", "icp-scratch");
            case 'categories': return __("Categories", "icp-scratch");
            case 'texts': return __("Texts", "icp-scratch");
			case 'publications': return __("All publications", "icp-scratch");
            case 'indices':
                $index_slug = get_query_var('index_slug');
                if ($index_slug) {
                    return index_name($index_slug);
                } else {
                    return __("Indices", "icp-scratch");
                }
			case 'periodicals': {
                $mag_slug = get_query_var('periodical_slug');
				if ($mag_slug) {
                    $issue_slug = get_query_var('issue_slug');
                    global $wpdb;
					if ($issue_slug) {
                        $issue_res = $wpdb->get_results(
                            $wpdb->prepare(
                                <<<SQL
                                    SELECT issue.title
                                    FROM wp_mlj_magazines AS mag
                                    JOIN wp_mlj_issues AS issue ON issue.mag_id = mag.id AND issue.slug = %s
                                    WHERE mag.slug = %s
                                SQL,
                                $issue_slug, $mag_slug
                            )
                        );
                        return $issue_res ? $issue_res[0]->title : __("Page not found");
					} else {
						$mag_res = $wpdb->get_results($wpdb->prepare(
                            "SELECT title FROM wp_mlj_magazines WHERE slug = %s"
                        , $mag_slug));
                        return $mag_res ? $mag_res[0]->title : __("Page not found");
					}
				} else {
					return __("Periodicals", "icp-scratch");
				}
			}
		}
    } else if (is_home()) {
        return __("International Communist Party", "icp-scratch");
    } else if (is_single() || is_page()) {
        return get_the_title();
    } else if (is_category()) {
        global $wp_query;
        $cat = $wp_query->get_queried_object();
        return $cat->name;
    } else {
        return null;
    }
}

// Twitter: 200 characters
// Facebook: 300 characters (posts), 110 characters (comments)
// Discord: ~350 characters
// We select the maximum (350) so the embed always displays as much text as possible.
// When writing manual excerpts, however, we may want to limit it to the minimum (110). Then some post content could be concatenated at the end.
function custom_description($limit=350) {
    $page_type = get_query_var('page_type');
    if ($page_type) {
		switch ($page_type) {
			case 'periodicals': {
                $mag_slug = get_query_var('periodical_slug');
				if ($mag_slug) {
                    $issue_slug = get_query_var('issue_slug');
					if ($issue_slug) {
                        return get_issue_description($mag_slug, $issue_slug, $limit);
					} else {
						return get_periodical_description($mag_slug, $limit);
					}
				} else {
					return null;
				}
			}
            default: return null;
		}
    } else if (is_home()) {
        // $set = __("What distinguishes our party", "icp-scratch");
        $par1 = __("The line running from Marx to Lenin to the foundation of the Third International and the birth of the Communist Party of Italy at Leghorn (Livorno) in 1921, and from there to the struggle of the Italian Communist Left against the degeneration in Moscow and to the rejection of popular fronts and coalitions of resistance groups.", "icp-scratch");
        $par2 = __("The tough work of restoring the revolutionary doctrine and the party organ, in contact with the working class, outside the realm of personal politics and electoral maneuvers.", "icp-scratch");
        return "$par1 $par2";
    } else if (is_single() || is_page()) {
        return get_post_description($limit);
    } else if (is_category()) {
        global $wp_query;
        $cat = $wp_query->get_queried_object();
        return $cat->description;
    } else {
        return null;
    }
}

function generate_post_description($limit) {
    // Handle case for parent posts
    global $wpdb;
    $res = $wpdb->get_results($wpdb->prepare(<<<SQL
        SELECT post_content
        FROM wp_posts
        WHERE post_type = 'article' AND post_status = 'publish' AND post_parent = %d
        ORDER BY menu_order
        LIMIT 1
    SQL, get_the_ID()));
    $content = $res ? $res[0]->post_content : get_the_content();
    $content = wp_strip_all_tags($content);
    if (mb_strlen($content) <= $limit) return $content;
    $replacement = wp_html_excerpt($content, $limit);
    // Trims to word boundary
    if (strrpos($replacement, ' ')) {
        $replacement = substr($replacement, 0, strrpos($replacement, ' '));
    }
    return $replacement;
}

function get_post_description($limit) {
    $desc = get_the_excerpt();
    if (empty($desc)) {
        return generate_post_description($limit);
    } else {
        return $desc;
    }
}

function get_issue_description($mag_slug, $issue_slug, $limit) {
    return null; // TODO
}

function get_periodical_description($mag_slug, $limit) {
    return null; // TODO
}