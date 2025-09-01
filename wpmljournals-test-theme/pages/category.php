<?php

global $wp_query;
$cat = $wp_query->get_queried_object();
$cat_term_id = $cat->term_id;
$cat_tax_id = $cat->term_taxonomy_id;
$cur_lang = apply_filters('wpml_current_language', NULL);

global $wpdb;
?>

<nav class="breadcrumbs">
    <a href="<?php echo all_categories_url() ?>"><?php _e("Categories", 'icp-scratch') ?></a> &raquo;
    <?php echo get_term_parents_list($cat_term_id, 'category', array(
        'separator' => ' &raquo; ',
        'inclusive' => false,
    )); ?>
    <?php echo $cat->name ?>
</nav>
<h1><?php echo $cat->name ?></h1>

<?php
    $children = get_categories(array(
        'parent' => $cat_term_id,
    ));
    if ($children) {
?>
    <h2><?php _e("Child categories", "icp-scratch") ?></h2>
    <ul>
    <?php foreach ($children as $child) { ?>
        <li><a href="<?php echo $child->slug ?>"><?php echo $child->name; ?></a></li>
    <?php } ?>
    </ul>
<?php } ?>

<h2><?php _e("Texts", "icp-scratch") ?></h2>
<?php
// Select all descendant posts of category in the current language
$article_query = $wpdb->get_results($wpdb->prepare(<<<SQL
    SELECT DISTINCT ID, post_name, post_title, post_date
    FROM wp_posts post
    JOIN wp_icl_translations tr ON tr.element_id = post.ID
    JOIN wp_term_relationships rel ON rel.object_id = post.ID
    WHERE post_type = 'article'
    AND post_status = 'publish'
    AND tr.element_type = 'post_article'
    AND tr.language_code = %s
    AND rel.term_taxonomy_id IN (
        WITH RECURSIVE cte AS (
            SELECT %d AS child_term, %d AS child_tax
        UNION
            SELECT tax.term_id AS child_term, tax.term_taxonomy_id AS child_tax
            FROM wp_term_taxonomy tax
            JOIN cte ON tax.parent = cte.child_term
        )
        SELECT child_tax FROM cte
    )
    ORDER BY post_date DESC
SQL, $cur_lang, $cat_term_id, $cat_tax_id));
?>

<?php
if ($article_query) {
    get_template_part('parts/text-listing', null, array('pubs' => $article_query));
} else {
    $txt = __("No posts in this category.", "icp-scratch");
    echo "<p><i>$txt</i></p>";
}
?>