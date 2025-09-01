<?php
$cur_lang = apply_filters('wpml_current_language', null);
// $url_root = get_bloginfo('url') . "/" . $cur_lang;
$url_root = "/{$cur_lang}";

$tcp_slug = select_tcp_for_lang($cur_lang);
$cl_slug = select_cl_for_lang($cur_lang);

function get_mag_title($slug) {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare("
        SELECT title FROM wp_mlj_magazines WHERE slug = %s
    ", $slug))[0]->title;
}
?>

<header>
    <div class="top">
        <?php if (really_is_home()) { ?>
            <h1><?php _e("Research Publication Collective", "icp-scratch") ?></h1>
        <?php } else { ?>
            <p class="header-title"><a href="<?php echo $url_root ?>/"><?php _e("Research Publication Collective", "icp-scratch") ?></a></p>
        <?php } ?>
        <!-- <div class="lang-switcher"> -->
            <?php // do_action('wpml_add_language_selector'); ?>
            
        <!-- </div> -->
        
        <?php get_template_part('parts/lang-switcher') ?>
    </div>

    <nav class="main-nav">
        <div>
            <a href="<?php echo $url_root ?>/"><?php _e("Home", "icp-scratch") ?></a>
            | <a href="<?php echo $url_root ?>/research-papers/"><?php _e("Research Papers", "icp-scratch") ?></a>
            | <a href="<?php echo $url_root ?>/about-us/"><?php _e("About Us", "icp-scratch") ?></a>
            | <a href="<?php echo $url_root ?>/contact/"><?php _e("Contact", "icp-scratch") ?></a>
        </div>
        <div>
            <a href="<?php echo $url_root ?>/periodicals/"><?php _e("Periodicals", "icp-scratch") ?></a><?php
            if ($tcp_slug) {
                $url = periodical_url($tcp_slug);
                $title = get_mag_title($tcp_slug);
                echo ": <a href='{$url}'><cite>{$title}</cite></a>";
            }
            if ($cl_slug) {
                $url = periodical_url($cl_slug);
                $title = get_mag_title($cl_slug);
                $sep = $tcp_slug ? ',' : ':';
                echo "{$sep} <a href='{$url}'><cite>{$title}</cite></a>";
            }
            ?>
            | <a href="<?php echo $url_root ?>/texts/"><?php _e("Texts", "icp-scratch") ?></a>
            | <a href="<?php echo $url_root ?>/indices/"><?php _e("Indices", "icp-scratch") ?></a>
            | <a href="<?php echo $url_root ?>/search/"><?php _e("Search", "icp-scratch") ?></a>
        </div>
    </nav>
</header>
