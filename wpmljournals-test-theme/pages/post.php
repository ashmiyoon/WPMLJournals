<?php

global $wpdb;

?>

<article>

<header>

<h1><?php the_title(); ?></h1>

<?php if (!is_page()) { ?>
    <?php
    // if ( has_excerpt() ) {
    //     echo '<p class="excerpt">' . get_the_excerpt() . '</p>';
    // }
    ?>
    <?php $date_format = _x("F j, Y", "date format ymd", "icp-scratch"); ?>
    <p><time><?php echo ucfirst(get_the_time($date_format)); ?></time></p>

    <?php
        $index_query = $wpdb->get_results($wpdb->prepare(<<<SQL
            SELECT ix.slug
            FROM wp_posts post
            JOIN wp_icl_translations tr ON tr.element_id = post.id
            JOIN wp_mlj_assigned_indices rel ON rel.text_trid = tr.trid
            JOIN wp_mlj_indices ix ON ix.id = rel.index_id
            WHERE post.id = %d AND tr.element_type = 'post_article'
            ORDER BY ix.id
        SQL, get_the_ID()));

        if ($index_query) {
            $ilinks = [];
            foreach ($index_query as $ix) {
                $name = index_name($ix->slug);
                $url = index_url($ix->slug);
                $ilinks[] = "<a href=\"$url\">$name</a>";
            }
            $links_str = implode(", ", $ilinks);
            
            echo "<p>";
            _e("Indices", "icp-scratch");
            echo ": ";
            echo $links_str;
            echo "</p>";
        }
    ?>

    <?php if (has_category()) : ?>
        <p><?php _e("Categories", 'icp-scratch') ?>: <?php the_category(', '); ?></p>
    <?php endif; ?>
<?php } ?>

<?php
$parent_id_res = $wpdb->get_results($wpdb->prepare(<<<SQL
    SELECT post_parent FROM wp_posts WHERE ID = %d
SQL, get_the_ID()));
$parent_id = $parent_id_res[0]->post_parent;

if ($parent_id != 0) {
    $parent_res = $wpdb->get_results($wpdb->prepare(<<<SQL
        SELECT
            ID, post_name, post_title
        FROM wp_posts
        WHERE ID = %d
    SQL, $parent_id));
    $parent = $parent_res[0];
    $url = text_url($parent->ID, $parent->post_name);
    $txt = __("Parent post:", "icp-scratch");
    echo "<p>$txt <a href='{$url}'>{$parent->post_title}</a></p>";
}
?>

<?php
$children = $wpdb->get_results($wpdb->prepare(<<<SQL
    SELECT
        ID, trid, post_name, post_title, post_content
    FROM wp_posts post
    JOIN wp_icl_translations tr ON tr.element_id = post.id
    WHERE post_parent = %d AND element_type = 'post_article' AND post_type = 'article' AND post_status = 'publish'
    ORDER BY menu_order, post_date ASC
SQL, get_the_ID()));

if ($children) {
    $txt = __("Child posts:", "icp-scratch");
    echo "<p>$txt</p>";
    echo "<ol>";
    foreach ($children as $child) {
        $url = text_url($child->ID, $child->post_name);
        echo "<li><a href='{$url}'>{$child->post_title}</a></li>";
    }
    echo "</ol>";
}
?>

<?php
$sources = $wpdb->get_results($wpdb->prepare(<<<SQL
    SELECT
        mag.slug AS mag_slug,
        issue.slug AS issue_slug,
        issue.title AS issue_name,
        issue.start_date AS issue_date,
        issue.date_display AS date_display
    FROM wp_mlj_assigned_issues rel
    JOIN wp_mlj_issues issue ON issue.id = rel.issue_id
    JOIN wp_mlj_magazines mag ON mag.id = issue.mag_id
    WHERE rel.text_trid = (
        SELECT tr.trid
        FROM wp_icl_translations tr
        WHERE element_type = 'post_article'
        AND tr.element_id = %d
    )
    ORDER BY issue_date ASC
SQL, get_the_ID()));

if ($sources) {
    $cur_lang = apply_filters('wpml_current_language', null);
    $txt = _x("This article was published in:", "sources", "icp-scratch");
    echo "<p>$txt</p>";
    echo "<ul>";
    foreach ($sources as $source) {
        $i_date = format_mysql_date_with_specificity($source->issue_date, $source->date_display);
        $i_href = issue_url($source->mag_slug, $source->issue_slug);
        $i_name = $source->issue_name;
        echo "<li><a href='$i_href'><cite dir='ltr'>$i_name</cite></a> (<time datetime='$i_date'>$i_date</time>)</li>";
    }
    echo "</ul>";
}

// TODO list sources for parts?
// I think we would need consistent menu_order numbering for this though

// $child_trids = array_map($children, function($child) {
//     return $child->trid;
// });
?>

<?php
if (!is_page()) {
    $cur_lang = apply_filters('wpml_current_language', null);
    $transes = $wpdb->get_results($wpdb->prepare(<<<SQL
        SELECT post.ID AS ID, ltr.name AS lang, tr.language_code AS lang_code, post_name AS slug, post_title AS title
        FROM wp_icl_translations tr
        JOIN wp_posts post ON tr.element_id = post.ID
        JOIN wp_icl_languages_translations ltr ON tr.language_code = ltr.language_code
        WHERE tr.element_type = 'post_article'
        AND post.post_type = 'article'
        AND post.post_status = 'publish'
        AND ltr.display_language_code = %s
        AND tr.trid = (
            SELECT trid
            FROM wp_icl_translations
            WHERE element_type = 'post_article' AND element_id = %d
        )
        ORDER BY post_date ASC
    SQL, $cur_lang, get_the_ID()));

    if (count($transes) > 1) {
        $txt = __("Available translations:", "icp-scratch");
        echo "<p>$txt</p>";
        echo "<ul>";
        foreach ($transes as $trans) {
            $url = text_url($trans->ID, $trans->slug, $trans->lang_code);
            $dir = dir_attribute($trans->lang_code);
            echo "<li>{$trans->lang}: <a href='{$url}'><cite lang='{$trans->lang_code}' $dir>{$trans->title}</cite></a></li>";
        }
        echo "</ul>";
    }
}
?>

</header>

<?php
    if (!empty($children)) {
        foreach ($children as $child) {
            echo render_content($child->post_content);
        }
    } else {
        the_content();
    }
?>
<?php wp_link_pages(); ?>
<?php edit_post_link(); ?>

<?php
if ($parent_id != 0) {
    $siblings = $wpdb->get_results($wpdb->prepare(<<<SQL
        SELECT ID, post_name, post_title
        FROM wp_posts
        WHERE post_parent = %d AND post_type = 'article' AND post_status = 'publish'
        ORDER BY post_date ASC
    SQL, $parent_id));

    $num_pages = count($siblings);
    assert($num_pages != 0); // Impossible because the result should always contain at least the current post

    $last_page = $num_pages - 1;
    $cur_page = null;
    foreach ($siblings as $i => $page) {
        if ($page->ID == get_the_ID()) {
            $cur_page = $i;
            break;
        }
    }
    assert(!is_null($cur_page));
?>
    <nav class="page-switcher">
        <div class="prev-post">
            <?php
            if ($cur_page > 0) {
                $page = $siblings[$cur_page-1];
                $url = text_url($page->ID, $page->post_name);
                echo "<a href='{$url}'>&laquo; {$page->post_title}</a>";
            }
            ?>
        </div>
        <div class="next-post">
            <?php
            if ($cur_page < $last_page) {
                $page = $siblings[$cur_page+1];
                $url = text_url($page->ID, $page->post_name);
                echo "<a href='{$url}'>{$page->post_title} &raquo;</a>";
            }
            ?>
        </div>
    </nav>
<?php } ?>

</article>
