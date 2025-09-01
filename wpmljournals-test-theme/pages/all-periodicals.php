<h1><?php _e("Periodicals", "icp-scratch") ?></h1>

<?php
global $wpdb;

// We split periodicals into two groups: ongoing and archived
// We determine this by checking whether the end year is null

$mags = $wpdb->get_results(
    <<<SQL
        SELECT slug, title, start_year, end_year
        FROM wp_mlj_magazines
        ORDER BY (end_year IS NULL) DESC, start_year DESC
    SQL,
);

function print_mag($mag) {
    $start = $mag->start_year;
    $end = $mag->end_year ?? 'now';
    echo "<li><a href='{$mag->slug}'>{$mag->title} ({$start}-{$end})</a></li>";
}

?>

<h2><?php _ex("Ongoing", "periodicals", "icp-scratch") ?></h2>
<?php
echo "<ul>";
foreach ($mags as $mag) {
    if (is_null($mag->end_year)) {
        print_mag($mag);
    }
}
echo "</ul>";
?>

<h2><?php _ex("Archived", "periodicals", "icp-scratch") ?></h2>
<?php
echo "<ul>";
foreach ($mags as $mag) {
    if (!is_null($mag->end_year)) {
        print_mag($mag);
    }
}
echo "</ul>";
?>