<?php

// See https://gist.github.com/mohandere/4286103ce313d0cd6549

// We use the `wpseo_sitemap_index` hook to add sitemaps into the sitemap index
// And we use `$wpseo_sitemaps->register_sitemap` to add sitemap request handlers

// Note: `LISTED_LANGS` is defined in `constants.php`
/*
// define('SITEMAP_LANGS', LISTED_LANGS);
define('SITEMAP_LANGS', ['en']); // English links are enough, because we have alternate links in the HTML

function add_sitemap_hooks() {
    add_filter('wpseo_sitemap_index', 'add_press_sitemaps');
    add_action('init', function() {
        global $wpseo_sitemaps;
        $wpseo_sitemaps->register_sitemap('custom_page', 'generate_custom_page_sitemap');
        $wpseo_sitemaps->register_sitemap('index', 'generate_index_sitemap');
        $wpseo_sitemaps->register_sitemap('periodical', 'generate_periodical_sitemap');
        // foreach (SITEMAP_LANGS as $lang) {
        //     $wpseo_sitemaps->register_sitemap("issue_$lang", generate_issue_sitemap_for_lang($lang));
        // }
        $wpseo_sitemaps->register_sitemap("issue", generate_issue_sitemap_for_lang('en'));
    });
}

function add_press_sitemaps() {
    $prefix = site_url();
    return "
        <sitemap>
            <loc>$prefix/custom_page-sitemap.xml</loc>
        </sitemap>
        <sitemap>
            <loc>$prefix/index-sitemap.xml</loc>
        </sitemap>
        <sitemap>
            <loc>$prefix/periodical-sitemap.xml</loc>
        </sitemap>
        <sitemap>
            <loc>$prefix/issue-sitemap.xml</loc>
        </sitemap>
    ";
}

function set_sitemap($content) {
    global $wpseo_sitemaps;
    $wpseo_sitemaps->set_sitemap(
        '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        . $content .
        '</urlset>'
    );
}

function generate_custom_page_sitemap() {
    $lang_urls = [];
    foreach (SITEMAP_LANGS as $lang) {
        $res = [
            contact_url($lang),
            all_indices_url($lang),
            all_periodicals_url($lang),
            all_texts_url($lang),
            all_publications_url($lang),
            all_categories_url($lang),
            search_url($lang),
        ];
        $urls = array_map(function($item) use ($lang) {
            $url = site_url() . $item;
            return "<url><loc>$url</loc></url>
            ";
        }, $res);
        $lang_urls []= implode('', $urls);
    }
    $content = implode('', $lang_urls);
    set_sitemap($content);
}

function generate_index_sitemap() {
    global $wpdb;
    $res = $wpdb->get_results(<<<SQL
        SELECT slug
        FROM wp_mlj_indices
        ORDER BY id
    SQL);

    $urls = array_map(function($index) {
        $url = site_url() . index_url($index->slug);
        return "<url><loc>$url</loc></url>
        ";
    }, $res);

    $content = implode('', $urls);
    set_sitemap($content);
}

function generate_periodical_sitemap() {
    global $wpdb;
    $res = $wpdb->get_results(<<<SQL
        SELECT slug
        FROM wp_mlj_magazines
        ORDER BY id
    SQL);

    $lang_urls = [];
    foreach (SITEMAP_LANGS as $lang) {
        $urls = array_map(function($mag) use ($lang) {
            $url = site_url() . periodical_url($mag->slug, $lang);
            return "<url><loc>$url</loc></url>
            ";
        }, $res);
        $lang_urls []= implode('', $urls);
    }
    $content = implode('', $lang_urls);
    set_sitemap($content);
}

function generate_issue_sitemap_for_lang($lang) {
    return function() use ($lang) {
        global $wpdb;
        $res = $wpdb->get_results(<<<SQL
            SELECT mag.slug AS mag_slug, issue.slug AS issue_slug
            FROM wp_mlj_magazines mag JOIN wp_mlj_issues issue ON issue.mag_id = mag.id
            ORDER BY issue.mag_id, issue.no
        SQL);
    
        $urls = array_map(function($row) use ($lang) {
            $url = site_url() . issue_url($row->mag_slug, $row->issue_slug, $lang);
            return "<url><loc>$url</loc></url>
            ";
        }, $res);
        $content = implode('', $urls);
        set_sitemap($content);
    };
}

add_sitemap_hooks();
*/