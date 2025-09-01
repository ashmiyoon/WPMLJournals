<?php

global $wpdb;

$mag_slug = get_query_var('periodical_slug');
$issue_slug = get_query_var('issue_slug');

$issue_res = $wpdb->get_results(
    $wpdb->prepare(
        <<<SQL
            SELECT
                issue.mag_id AS mag_id,
                issue.id,
                issue.no,
                mag.title AS mag_title,
                issue.title AS issue_title,
                start_date,
                end_date,
                date_display
            FROM wp_mlj_magazines AS mag
            JOIN wp_mlj_issues AS issue ON issue.mag_id = mag.id AND issue.slug = %s
            WHERE mag.slug = %s
        SQL,
        array($issue_slug, $mag_slug)
    )
);

if ($wpdb->last_error) {
    wp_die("query error");
}

if (empty($issue_res)) {
    throw_404();
}

$issue = $issue_res[0];
$issue_id = $issue->id;

$attaches_arr = $wpdb->get_results(
    $wpdb->prepare(
        <<<SQL
            SELECT
                attach_type AS type, post.guid AS url
            FROM wp_mlj_attachments AS att
            JOIN wp_posts AS post ON attach_post_id = post.id
            WHERE att.issue_id = %d
        SQL,
        $issue_id,
    )
);
$attaches = [];
foreach ($attaches_arr as $attach) {
    $attaches[$attach->type] = $attach->url;
}
?>

<?php
$siblings = $wpdb->get_results($wpdb->prepare(<<<SQL
    SELECT id, slug, title
    FROM wp_mlj_issues
    WHERE mag_id = %d
    ORDER BY no ASC
SQL, $issue->mag_id));

$num_pages = count($siblings);
assert($num_pages != 0); // Impossible because the result should always contain at least the current post

$last_page = $num_pages - 1;
$cur_page = null;
foreach ($siblings as $i => $page) {
    if ($page->id == $issue_id) {
        $cur_page = $i;
        break;
    }
}
assert(!is_null($cur_page));
?>

<nav class="breadcrumbs"><?php
    $txt = __("Periodicals", "icp-scratch");
    echo "<a href='../..'>{$txt}</a> &raquo; <a href='..'>{$issue->mag_title}</a> &raquo; {$issue->issue_title}"
?></nav>

<h1><?php echo $issue->issue_title ?></h1>

<div class="issue-publinks">
    <div>
        <?php
        if ($issue->start_date) {
            $date = format_mysql_date_with_specificity($issue->start_date, $issue->date_display);
            $datestr = "<time datetime='{$date}'>{$date}</time>";
            $content = sprintf(_x("Published in %s.", "date", "icp-scratch"), $datestr);
            echo "<p>$content</p>";
        }
        ?>
    </div>
    <nav class="issue-links">
        <?php
        $which = $attaches['original'] ? 'original' : 'scan';
        $pdf_url = $attaches[$which];
        if ($pdf_url) {
            // $text = match ($which) {
            //     case 'original' => "PDF",
            //     case 'scan' => "PDF (Scan)",
            //     default => "Unknown",
            // };
            $text = "PDF";
            echo "[ <a href='$pdf_url'>$text</a> ]";
        }
        ?>
    </nav>
</div>

<style>
    .issue-publinks {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: baseline;
    }

    .issue-nav {
        border-bottom: black 1px solid;
        margin-bottom: 1em;
        padding-right: 1em;
    }

    .issue-articles {
        display: flex;
        flex-direction: column;
        gap: 1em;
    }

    .issue-articles article {
        border-bottom: black 1px solid;
        /*border: black 1px solid;
        padding-left: 0.5em;
        padding-right: 0.5em;*/
    }

    .untranslated-article {
        border: black 1px dashed;
        padding-left: 0.5em;
        padding-right: 0.5em;
    }
</style>

<style>
    /* Hide ToC from articles within issue pages */
    #ez-toc-container {
        display: none;
    }
</style>

<?php

$query = <<<SQL
WITH base AS (
    SELECT
        trid,
        post.ID AS ID,
        post.post_name AS post_slug,
        tr.language_code,
        ltr.name AS lang_name,
        post_name,
        post_title,
        post_content,
        list.position AS list_position,
        CASE
            WHEN tr.language_code = 'en' THEN 0
            WHEN tr.language_code = 'it' THEN 11
            WHEN tr.language_code = %s THEN 100
            ELSE 101 + tr.translation_id
        END AS language_priority
    FROM
             wp_mlj_issues       AS issue
        JOIN wp_mlj_assigned_issues AS list  ON list.issue_id = issue.id
        JOIN wp_icl_translations   AS tr    ON tr.trid = list.text_trid
        JOIN wp_posts              AS post  ON post.ID = tr.element_id
        JOIN wp_icl_languages_translations AS ltr ON ltr.language_code = tr.language_code
    WHERE issue_id = %d
      AND element_type = 'post_article'
      AND post_status = 'publish'
      AND ltr.display_language_code = %s
),
ranked AS (
    SELECT
        *,
        RANK() OVER (
            PARTITION BY trid
            ORDER BY language_priority
        ) AS translation_rank
    FROM base
)
SELECT *
FROM ranked
WHERE translation_rank = 1
ORDER BY list_position;

SQL;
/*<<<SQL
    WITH res AS (
        SELECT
            trid,
            post.ID AS ID,
            post.post_name AS post_slug,
            tr.language_code,
            ltr.name AS lang_name,
            post_name,
            post_title,
            post_content,
            list.position AS list_position,
            CASE
                WHEN tr.language_code =  %s  THEN  0
                WHEN tr.language_code = 'en' THEN 10
                WHEN tr.language_code = 'it' THEN 11
                WHEN tr.language_code = source_language_code THEN 100
                ELSE 101 + tr.translation_id -- make score unique to avoid duplicates
            END AS language_priority,
            RANK() OVER (PARTITION BY trid ORDER BY language_priority) AS translation_rank
        FROM
                 wp_mlj_issues       AS issue
            JOIN wp_mlj_assigned_issues AS list  ON list.issue_id = issue.id
            JOIN wp_icl_translations   AS tr    ON tr.trid = list.text_trid
            JOIN wp_posts              AS post  ON post.ID = tr.element_id
            JOIN wp_icl_languages_translations AS ltr ON ltr.language_code = tr.language_code
            WHERE issue_id = %d
            AND element_type = 'post_article'
            AND post_status = 'publish'
            AND ltr.display_language_code = %s
    )
    SELECT *
    FROM res
    WHERE translation_rank = 1
    ORDER BY list_position
SQL;*/

$current_lang = apply_filters('wpml_current_language', NULL);
$res = $wpdb->get_results($wpdb->prepare($query, array($current_lang, $issue_id, $current_lang)));

if ($wpdb->last_error) {
    wp_die("query 2 error");
}

// Shift headings by 1
function fix_headings($content) {
    $content = preg_replace("/<h6(.*)>(.*)<\/h6>/", "<strong$1>$2</strong>", $content);
    $content = preg_replace("/<h5(.*)>(.*)<\/h5>/", "<h6$1>$2</h6>", $content);
    $content = preg_replace("/<h4(.*)>(.*)<\/h4>/", "<h5$1>$2</h5>", $content);
    $content = preg_replace("/<h3(.*)>(.*)<\/h3>/", "<h4$1>$2</h4>", $content);
    $content = preg_replace("/<h2(.*)>(.*)<\/h2>/", "<h3$1>$2</h3>", $content);
    return $content;
}

// Display query results
if (empty($res)) {
    $txt = __("No articles listed.", "icp-scratch");
    echo "<p><i>{$txt}</i></p>";
} else {
    echo "<nav class='issue-nav'>";
    echo "<ol>";
    foreach ($res as $tuple) {
        $url = "#{$tuple->trid}";
        $lang = $tuple->language_code;
        $untrans = $lang != $current_lang;
        if ($untrans) {
            echo "<li><a class='untranslated-link' href='$url'><span lang='$lang'>{$tuple->post_title}</span> ({$tuple->lang_name})</a></li>";
        } else {
            echo "<li><a href='$url'>{$tuple->post_title}</a></li>";
        }
    }
    echo "</ol>";
    echo "</nav>";

    echo "<div class='issue-articles'>";
    foreach ($res as $tuple) {
        $lang = $tuple->language_code;
        $untrans = $lang != $current_lang;
        if ($untrans) {
            echo "<article class='untranslated-article' lang='$lang'>";
        } else {
            echo "<article>";
        }
        $url = text_url($tuple->ID, $tuple->post_slug, $tuple->language_code);

        echo "<a href='$url'><h2 id='{$tuple->trid}'>{$tuple->post_title}</h2></a>";

        // Set $wp_query because it gets used by the content filters
        global $wp_query;
        $wp_query = new WP_Query([
            'post_type' => 'article',
            'p' => $tuple->ID,
        ]);
        $wp_query->the_post();

        $content = $tuple->post_content;
        $content = render_content($content);
        $content = fix_headings($content);
        echo $content;
        echo "</article>";
    }
    echo "</div>";
}
?>

<nav class="page-switcher">
    <div class="prev-post">
        <?php
        if ($cur_page > 0) {
            $page = $siblings[$cur_page-1];
            $url = issue_url($mag_slug, $page->slug);
            echo "<a href='{$url}'>&laquo; {$page->title}</a>";
        }
        ?>
    </div>
    <div class="next-post">
        <?php
        if ($cur_page < $last_page) {
            $page = $siblings[$cur_page+1];
            $url = issue_url($mag_slug, $page->slug);
            echo "<a href='{$url}'>{$page->title} &raquo;</a>";
        }
        ?>
    </div>
</nav>
