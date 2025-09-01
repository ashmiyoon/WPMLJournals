<div class="additions-heading">
    <h1><?php _e("Texts", "icp-scratch") ?></h1>
    <a href="<?php echo all_publications_url() ?>"><?php _e("In all languages", "icp-scratch") ?></a>
</div>

<?php
$cur_lang = apply_filters('wpml_current_language', null);

global $wpdb;
$texts = $wpdb->get_results($wpdb->prepare(
    <<<SQL
        SELECT ID, post_name, post_title, post_date
        FROM wp_posts INNER JOIN wp_icl_translations ON ID = wp_icl_translations.element_id
        WHERE post_type='article' AND element_type='post_article' AND wp_icl_translations.language_code = %s AND post_status = 'publish'
        ORDER BY post_date DESC
    SQL, $cur_lang
));

get_template_part('parts/text-listing', null, array('pubs' => $texts))
?>