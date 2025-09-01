<?php

// Populates summarize table if not recently updated.
function mlj_summarize($table_name, $sql_query, $force = false) {
    global $wpdb;

    $results = get_transient("uptodate_$table_name");

    if (!$results or $force) {
        $results = $wpdb->get_results($sql_query, ARRAY_A);

        foreach ($results as $row) {
            $wpdb->replace($table_name, $row);
        }

        set_transient("uptodate_$table_name", true, get_option("mlj_summarize_timeout"));
    }
}


// Retrieves all publications from database (IPC, TCP, etc.),
// sorted first by year, then by date.
// @return Object output of get_results() upon query.
function mlj_get_magazines($opt = OBJECT, $force = false) {
    $magazine_sql = "
        SELECT
            CAST(tax.term_id AS INT) AS mag_id,
            terms.name AS name,
            terms.slug AS slug,
            CAST(meta_start.meta_value AS INT) AS start_year,
            CAST(meta_end.meta_value AS INT) AS end_year,
            CAST(meta_lang.meta_value AS CHAR(7)) AS pub_lang
        FROM
            `wp_term_taxonomy` AS tax
        JOIN `wp_terms` AS terms
            ON terms.term_id = tax.term_id
        JOIN `wp_termmeta` AS meta_start
            ON  meta_start.term_id  = tax.term_id
            AND meta_start.meta_key = 'start_year'
        LEFT JOIN `wp_termmeta` AS meta_end
            ON  meta_end.term_id  = tax.term_id
            AND meta_end.meta_key = 'end_year'
        LEFT JOIN `wp_termmeta` AS meta_lang
            ON  meta_lang.term_id  = tax.term_id
            AND meta_lang.meta_key = 'pub_lang'
        WHERE
            tax.taxonomy = 'series_group'
        ORDER BY
            start_year DESC,
            name ASC;";

    mlj_summarize("wp_mlj_sum_magazines", $magazine_sql, $force);

    global $wpdb;
    return $wpdb->get_results("SELECT * FROM wp_mlj_sum_magazines", $opt);
}

function mlj_get_issues_mags($opt = OBJECT, $force = false) {
    global $wpdb;

    $results = get_transient("mlj_issues_cache");

    if (!$results or $force) {
        $issue_mag_sql = "
SELECT
    posts.ID AS issue_post_id,
    posts.series_id AS issue_term_id,
    terms_issues.name AS issue_name,
--  rel.term_taxonomy_id AS rel_pair_id,
    tax.term_id AS mag_term_id,
    terms_mags.name AS mag_name,
    CAST(meta_start.meta_value AS INT) AS mag_start_year,
    CAST(meta_no.meta_value AS INT) AS issue_no,
    CAST(meta_date.meta_value AS DATE) AS pub_date,
    CAST(meta_spec.meta_value AS INT) AS specificity
FROM (
    SELECT
        ID,
        CAST(SUBSTRING(post_title, 17) AS INT) AS series_id
    FROM wp_posts
    WHERE post_type = 'series_grouping'
) AS posts
JOIN wp_term_relationships AS rel
    ON rel.object_id = posts.ID
JOIN wp_term_taxonomy AS tax
    ON tax.term_taxonomy_id = rel.term_taxonomy_id
JOIN wp_terms AS terms_mags
    ON terms_mags.term_id = tax.term_id
JOIN wp_terms AS terms_issues
    ON terms_issues.term_id = posts.series_id
JOIN wp_termmeta AS meta_start
    ON meta_start.term_id = tax.term_id
    AND meta_start.meta_key = 'start_year'
JOIN wp_termmeta AS meta_no
    ON meta_no.term_id = posts.series_id
    AND meta_no.meta_key = 'issue_no'
JOIN wp_termmeta AS meta_date
    ON meta_date.term_id = posts.series_id
    AND meta_date.meta_key = 'pub_date'
JOIN wp_termmeta AS meta_spec
    ON meta_spec.term_id = posts.series_id
    AND meta_spec.meta_key = 'specificity'
ORDER BY
    mag_start_year DESC,
    pub_date DESC,
    issue_no DESC;
        ";

        $results = $wpdb->get_results($issue_mag_sql, $opt);
        set_transient("mlj_issues_cache", $results,
                      get_option("mlj_cache_timeout"));
    }


    return $results;
}

function mlj_get_post_taxonomies($opt = OBJECT, $force = false) {
    global $wpdb;

    $results = get_transient("mlj_taxlist_cache");

    if (!$results or $force) {

        $post_category_sql = "
SELECT
    CAST(category_table.orig_post_id AS INT) AS pretrans_post_id,
    CAST(category_table.term_id AS INT) AS category_id,
    CAST(series_table.term_id AS INT) AS issue_id,
    CAST(tmeta.meta_value AS DATE) AS pub_date,
    category_table.name AS category_name,
    category_table.slug AS category_slug,
    series_table.name AS issue_name,
    series_table.slug AS issue_slug
FROM
    (
    SELECT
        tax.term_id AS term_id,
        terms.name AS name,
        terms.slug AS slug,
        orig_posts.ID AS orig_post_id
    FROM
        wp_term_relationships AS rel
    JOIN wp_term_taxonomy AS tax
        ON tax.term_taxonomy_id = rel.term_taxonomy_id
        AND tax.taxonomy = 'category'
    JOIN wp_terms AS terms
        ON tax.term_id = terms.term_id
    JOIN wp_posts AS orig_posts
        ON orig_posts.ID = rel.object_id
    ) AS category_table
LEFT JOIN
    (
    SELECT
        tax.term_id AS term_id,
        terms.name AS name,
        terms.slug AS slug,
        orig_posts.ID AS orig_post_id
    FROM
        wp_term_relationships AS rel
    JOIN wp_term_taxonomy AS tax
        ON tax.term_taxonomy_id = rel.term_taxonomy_id
        AND tax.taxonomy = 'series'
    JOIN wp_terms AS terms
        ON tax.term_id = terms.term_id
    JOIN wp_posts AS orig_posts
        ON orig_posts.ID = rel.object_id
    ) AS series_table
    ON series_table.orig_post_id = category_table.orig_post_id
LEFT JOIN wp_termmeta AS tmeta
    ON tmeta.term_id = series_table.term_id
    AND tmeta.meta_key = 'pub_date'
ORDER BY pub_date DESC
        ;";

        $results = $wpdb->get_results($post_category_sql, $opt);
        set_transient("mlj_taxlist_cache", $results, get_option("mlj_cache_timeout"));
    }

    return $results;
}

$test_sql1 = "
SELECT
    *
FROM
    (
    SELECT
        orig_posts.ID AS untrans_post_id,
        tax.term_id AS category_id,
        posttrans.element_id AS trans_category_id
    FROM
        wp_term_relationships AS rel
    JOIN wp_term_taxonomy AS tax
        ON tax.term_taxonomy_id = rel.term_taxonomy_id
        AND tax.taxonomy = 'category'
    JOIN wp_terms AS terms
        ON tax.term_id = terms.term_id
    JOIN wp_posts AS orig_posts
        ON orig_posts.ID = rel.object_id
    LEFT JOIN wp_icl_translations AS pretrans
        ON pretrans.element_type = 'tax_category'
        AND pretrans.element_id = tax.term_taxonomy_id
    LEFT JOIN wp_icl_translations AS posttrans
        ON posttrans.trid = pretrans.trid
        AND posttrans.language_code = 'en'
    ) AS category_table
LEFT JOIN
    (
    SELECT
        orig_posts.ID AS untrans_post_id,
        tax.term_id AS issue_id,
        posttrans.element_id AS trans_issue_id
    FROM
        wp_term_relationships AS rel
    JOIN wp_term_taxonomy AS tax
        ON tax.term_taxonomy_id = rel.term_taxonomy_id
        AND tax.taxonomy = 'series'
    JOIN wp_terms AS terms
        ON tax.term_id = terms.term_id
    JOIN wp_posts AS orig_posts
        ON orig_posts.ID = rel.object_id
    LEFT JOIN wp_icl_translations AS pretrans
        ON pretrans.element_type = 'tax_category'
        AND pretrans.element_id = tax.term_taxonomy_id
    LEFT JOIN wp_icl_translations AS posttrans
        ON posttrans.trid = pretrans.trid
        AND posttrans.language_code = 'en'
    ) AS series_table
    ON series_table.untrans_post_id = category_table.untrans_post_id;
";


// Returns list of term_taxonomy_ids corresponding to categories
function mlj_get_base_categories($opt = OBJECT, $force = false) {
}

function mlj_get_category_translations($lang = false, $opt = OBJECT, $force = false) {
    global $wpdb;

    if (!$lang) {
        $lang = apply_filters("wpml_current_language", null);
    }

    $results = get_transient("mlj_categories_$lang");

    if (!$results or $force) {

        $translated_category_sql = "
SELECT
    pretrans.language_code AS pretrans_lang,
--    posttrans.source_language_code AS src_lang,
    posttrans.language_code AS posttrans_lang,
    terms.name AS posttrans_name,
    terms.slug AS posttrans_slug,
    CASE
        WHEN posttrans.language_code = 'en' THEN 2
        WHEN posttrans.language_code = 'it' THEN 3
        WHEN posttrans.language_code = 'es' THEN 1
    END AS lang_priority,
    pretrans.element_id AS pretrans_taxid,
    posttrans.element_id AS posttrans_taxid,
    posttrans.trid
FROM wp_term_taxonomy AS tax
JOIN wp_icl_translations AS pretrans
    ON pretrans.element_id = tax.term_taxonomy_id
    AND pretrans.element_type = 'tax_category'
JOIN wp_icl_translations AS posttrans
    ON posttrans.trid = pretrans.trid
    AND posttrans.language_code IN ('en', 'it', 'es')
LEFT JOIN wp_term_taxonomy AS tax2
    ON tax2.term_taxonomy_id = posttrans.element_id
LEFT JOIN wp_terms AS terms
    ON terms.term_id = tax2.term_id
WHERE tax.taxonomy = 'category';
        ;";

        $results = $wpdb->get_results($translated_category_sql, $opt);
        set_transient("mlj_trans_cat_cache", $results, get_option("mlj_cache_timeout"));
    }

    return $results;
}

function mlj_display_table($results) {
    if (!$results) {
        echo "<pre>SOME ERROR OCCURED - NO RESULTS</pre>";
        return;
    }

    echo '<table border="1" class="sql_output"><thead><tr>';

    foreach (array_keys($results[0]) as $header) {
        echo '<th>' . esc_html($header) . '</th>';
    }

    echo '</tr></thead><tbody>';

    foreach ($results as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . esc_html($cell) . '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
}


// Get series posts!
$issue_articles_query = "
SELECT DISTINCT
    posttr.trid,
--    post.ID AS post_id,
--    pretr.element_id AS pre_id,
    posttr.element_id AS post_id,
    posttr.language_code,
    post_name,
    post_title,
    meta_value AS article_position,
    CASE
        WHEN posttr.language_code = 'de' THEN 0
        WHEN posttr.language_code = 'en' THEN 10
        WHEN posttr.language_code = 'it' THEN 11
        WHEN posttr.language_code = posttr.source_language_code THEN 100
        ELSE 101
    END AS language_priority
FROM wp_postmeta AS meta
JOIN wp_icl_translations AS pretr
    ON pretr.element_id = meta.post_id
JOIN wp_icl_translations AS posttr
    ON posttr.trid = pretr.trid
JOIN wp_posts AS post
    ON post.ID = posttr.element_id
    AND post.post_status = 'publish'
WHERE meta_key = CONCAT('_series_part_', 9185)
ORDER BY article_position, language_priority;
";
