<h1><?php _e("Indices", "icp-scratch") ?></h1>

<?php
global $wpdb;

// We may want to add a certain ordering later
$indices = $wpdb->get_results(<<<SQL
    SELECT id, slug
    FROM wp_mlj_indices
    ORDER BY id
SQL);

echo "<ul>";
foreach ($indices as $index) {
    $url = index_url($index->slug);
    $name = index_name($index->slug);
    echo "<li><a href='$url'>$name</a></li>";
}
echo "</ul>";

?>