<!DOCTYPE html>

<?php
// Select the right page template, as defined in `util/routes.php`.
// This doesn't load the template, it only returns its name as a string.
$page_template = dispatch_page_template();

$cur_lang = apply_filters('wpml_current_language', null);
$url_root = "/" . $cur_lang;

function test_fn() {
    // echo 'trying query';
    // $qq = new WP_Query([
    //     'p' => 22956,
    //     // 'p' => 8081,
    //     'post_type' => 'article',
    // ]);
    // while ($qq->have_posts()) {
    //     $qq->the_title();
    // }
}
?>

<html lang="<?php echo $cur_lang ?>" <?php echo dir_attribute() ?>>

<head>
    <meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ) ?>" charset="<?php bloginfo( 'charset' ) ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" >

    <title><?php
        if (really_is_home()) {
            _e("International Communist Party", "icp-scratch");
        } else {
            wp_title('|', true, 'right');
            echo " " . __("International Communist Party", "icp-scratch");
        }
    ?></title>

    <meta name="description" content="<?php echo custom_description() ?>">
    
    <meta property="og:site_name" content='<?php _e("International Communist Party", "icp-scratch") ?>'>
    <meta property="og:title" content="<?php echo custom_title() ?>">
    <meta property="og:description" content="<?php echo custom_description() ?>">

    <?php
        if (is_single() || is_page()) {
            $pubtime = get_post_time('c');
            $modtime = get_post_modified_time('c');
            echo "<meta property='og:type' content='article'>";
            echo "<meta property='article:published_time' content='$pubtime'>";
            echo "<meta property='article:modified_time' content='$modtime'>";
        } else {
            echo "<meta property='og:type' content='website'>";
        }
    ?>

    <?php wp_head() ?>
</head>

<body>
    <?php get_template_part('parts/header') ?>

    <main style="padding-bottom: 1em">
        <?php test_fn() ?>
        <?php // Here we load the actual page template ?>
        <?php get_template_part($page_template) ?>
    </main>

    <?php wp_footer() ?>
</body>

</html>
