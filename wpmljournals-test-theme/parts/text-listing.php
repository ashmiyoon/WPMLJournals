<?php
// Takes an argument 'pubs', which should be an array of objects with the fields
// - ID: int (the post id)
// - post_name: string (the post slug)
// - post_title: string
// - post_date: string (as we get it from wpdb)
// - language_code: ?string (may be null, in which case the current language is used)
?>

<ul class="additions">
<?php foreach ( $args['pubs'] as $pub ) { ?>
    <li>
        <a href="<?php echo text_url($pub->ID, $pub->post_name, $pub->language_code) ?>"><?php echo $pub->post_title; ?></a>
        <time datetime="<?php echo substr($pub->post_date, 0, 10) ?>"><?php echo substr($pub->post_date, 0, 10) ?></time>
    </li>
<?php } ?>
</ul>