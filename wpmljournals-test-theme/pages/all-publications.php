<h1><?php _e("All publications", "icp-scratch") ?></h1>

<?php
global $wpdb;
$article_query = $wpdb->get_results(
    <<<SQL
        SELECT ID, post_name, post_title, post_date, language_code
        FROM wp_posts INNER JOIN wp_icl_translations ON ID = wp_icl_translations.element_id
        WHERE post_type='article' AND element_type='post_article' AND post_status = 'publish'
        ORDER BY post_date DESC
    SQL
);
?>

<?php get_template_part('parts/text-listing', null, array('pubs' => $article_query)) ?>