<?php
$cur_lang = apply_filters('wpml_current_language', NULL);
$index_slug = get_query_var('index_slug');
$index_name = index_name($index_slug);
?>

<?php
global $wpdb;

$index_res = $wpdb->get_results($wpdb->prepare(
    <<<SQL
        SELECT id
        FROM wp_mlj_indices
        WHERE slug = %s
    SQL,
    $index_slug
));

if (empty($index_res)) {
    throw_404();
}

$index_id = $index_res[0]->id;
?>

<nav class="breadcrumbs">
    <a href="<?php echo all_indices_url() ?>"><?php _e("Indices", 'icp-scratch') ?></a> &raquo;
    <?php echo $index_name ?>
</nav>
<h1><?php echo $index_name ?></h1>

<?php

$res = $wpdb->get_results($wpdb->prepare(
    <<<SQL
        WITH res AS (
            SELECT
                trid,
                post.ID AS ID,
                post.post_name AS post_slug,
                tr.language_code,
                ltr.name AS lang_name,
                post_name,
                post_title,
                post_date,
                CASE
                    WHEN tr.language_code =  %s  THEN  0
                    WHEN tr.language_code = 'en' THEN 10
                    WHEN tr.language_code = 'it' THEN 11
                    WHEN tr.language_code = source_language_code THEN 100
                    ELSE 101 + tr.translation_id -- make score unique to avoid duplicates
                END AS language_priority,
                RANK() OVER (PARTITION BY trid ORDER BY language_priority) AS translation_rank
            FROM wp_mlj_assigned_indices rel
            JOIN wp_icl_translations tr ON tr.trid = rel.text_trid
            JOIN wp_icl_languages_translations AS ltr ON ltr.language_code = tr.language_code
            JOIN wp_posts post ON post.ID = tr.element_id
            WHERE rel.index_id = %d
            AND post_type = 'article'
            AND post_status = 'publish'
            AND tr.element_type = 'post_article'
            AND ltr.display_language_code = %s
        )
        SELECT *
        FROM res
        WHERE translation_rank = 1
        ORDER BY post_date ASC
    SQL,
    $cur_lang, $index_id, $cur_lang
));

if ($wpdb->last_error) {
    echo $wpdb->last_error;
}

// Mixes the functionality of usual addition lists but supporting listing of untranslated texts
echo "<ul class='additions'>";
foreach ($res as $tuple) {
    $lang = $tuple->language_code;
    $url = text_url($tuple->ID, $tuple->post_name, $lang);
    $untranslated = $lang != $cur_lang;
    $datestr = substr($tuple->post_date, 0, 10);
    echo "<li>";
    if ($untranslated) {
        echo "<a class='untranslated-link' href='$url'><span lang='$lang'>{$tuple->post_title}</span> ({$tuple->lang_name})</a>";
    } else {
        echo "<a href='$url'>{$tuple->post_title}</a>";
    }
    echo "<time datetime='$datestr'>$datestr</time>";
    echo "</li>";
}
echo "</ul>";
