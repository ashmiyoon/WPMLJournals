<?php

/*
 * Plugin Name: MLJournals
 * Plugin URI: https://github.com/ashmiyoon/WPMLJournals
 * Description: A WPML-compatible plugin to organize multilingual texts into journals and periodicals. Originally designed for a small research collective to streamline archival and display for bilingual texts.
 * Author: Ashley Yoon
 * License: MIT
 */

$dir = plugin_dir_path(__FILE__);

require_once $dir . "/meta-box/issue_apply_box.php";


// Creates tables needed for MLJournals plugin
function mlj_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $magazines_sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mlj_magazines (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `title` varchar(200) NOT NULL,
            `slug` varchar(200) NOT NULL,
            `start_year` int(11) NOT NULL,
            `end_year` int(11) DEFAULT NULL,
            `lang` varchar(7) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB $charset_collate;
    SQL;
    $issues_sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mlj_issues (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `mag_id` bigint(20) NOT NULL,
            `title` varchar(200) NOT NULL,
            `slug` varchar(200) NOT NULL,
            `no` int(11) NOT NULL,
            `start_date` date NOT NULL,
            `end_date` date DEFAULT NULL,
            `date_display` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `mag_id` (`mag_id`,`slug`)
        ) ENGINE=InnoDB $charset_collate COMMENT='Issues and Series, groupings of posts in general.';
    SQL;
    $attachments_sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mlj_attachments (
            `issue_id` bigint(20) NOT NULL,
            `attach_type` enum('original','scan') NOT NULL,
            `attach_post_id` bigint(20) UNSIGNED NOT NULL,
            PRIMARY KEY (`issue_id`,`attach_type`),
            KEY `attach_post_id` (`attach_post_id`)
        ) ENGINE=InnoDB $charset_collate;
    SQL;
    $indices_sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mlj_indices (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `slug` varchar(200) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB $charset_collate
    SQL;
    $assigned_issues_sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mlj_assigned_issues (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `text_trid` bigint(20) DEFAULT NULL,
            `issue_id` bigint(20) DEFAULT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `text_trid_issue_id` (`text_trid`,`issue_id`),
            KEY `issue_id` (`issue_id`)
        ) ENGINE=InnoDB $charset_collate
    SQL;
    $assigned_indices_sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mlj_assigned_indices (
            `index_id` int(11) NOT NULL,
            `text_trid` bigint(20) NOT NULL,
            PRIMARY KEY (`index_id`,`text_trid`),
            KEY `text_trid` (`text_trid`)
        ) ENGINE=InnoDB $charset_collate
    SQL;

    $wpdb->query($magazines_sql);
    $wpdb->query($issues_sql);
    $wpdb->query($attachments_sql);
    $wpdb->query($indices_sql);
    $wpdb->query($assigned_issues_sql);
    $wpdb->query($assigned_indices_sql);
}

// Activation hook
function mlj_activate() {
    // Trigger functions to setup environment for the plugin
    mlj_create_tables();

    // Clear the permalinks after everything is registered, to prevent 404
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mlj_activate');

// Deactivation hook
function mlj_deactivate() {
    // Undo whatever the plugin has setup

    // Clear permalinks
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mlj_deactivate');


// For debugging purposes
function mlj_debug_print($the_obj, $name='') {
    if ($name) {
        echo "<h4>" . $name . "</h4>";
    }
    echo "<pre>";
    echo print_r($the_obj);
    echo "</pre>";
}

function mlj_meta_box_scripts()
{
    // get current admin screen, or null
    $screen = get_current_screen();
    // verify admin screen object
    if (is_object($screen)) {
        // enqueue only for specific post types
        if (in_array($screen->post_type, ['post', 'article'])) {
            // enqueue script
            wp_enqueue_script('intcp_issue_apply_magazine_meta_box_script', plugin_dir_url(__FILE__) . 'meta-box/js/issue-apply-magazine.js', ['jquery']);
            wp_localize_script(
                'intcp_issue_apply_magazine_meta_box_script',
                'intcp_issue_apply_magazine_meta_box_obj',
                [
                    'url' => admin_url('admin-ajax.php'),
                ]
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'mlj_meta_box_scripts');

?>
