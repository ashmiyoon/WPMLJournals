<?php

global $wpdb;

$current_lang = apply_filters('wpml_current_language', NULL);
$mag_slug = get_query_var('periodical_slug');

$mag_res = $wpdb->get_results(
    $wpdb->prepare(
        <<<SQL
            SELECT
                id,
                title,
                lang,
                start_year,
                end_year
            FROM wp_mlj_magazines
            WHERE slug = %s
        SQL,
        $mag_slug
    )
);

if ($wpdb->last_error) {
    wp_die("query 1 error");
}

if (empty($mag_res)) {
    throw_404();
}

$mag = $mag_res[0];

// TODO refactor queries here and in issue.php
$issues_articles_res = $wpdb->get_results($wpdb->prepare(
    <<<SQL
        WITH base AS (
            SELECT
                issue.no,
                trid,
                post.ID AS ID,
                post.post_name AS post_slug,
                tr.language_code,
                ltr.name AS lang_name,
                post.post_title,
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
            WHERE issue.mag_id = %d
            AND tr.element_type = 'post_article'
            AND post.post_status = 'publish'
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
        ORDER BY no, list_position;

    SQL, [$current_lang, $mag->id, $current_lang]
));

if ($wpdb->last_error) {
    wp_die("article query error");
}

$issue_articles = [];
foreach ($issues_articles_res as $article) {
    if (!$issue_articles[$article->no]) $issue_articles[$article->no] = [];
    $issue_articles[$article->no][] = $article;
}

$issue_links_res = $wpdb->get_results($wpdb->prepare(<<<SQL
    WITH atts AS (
        SELECT issue_id, attach_type, guid AS url
        FROM wp_mlj_attachments JOIN wp_posts ON id = attach_post_id
    )
    SELECT
        issue.no,
        oa.url AS orig_pdf_url,
        sa.url AS scan_pdf_url
    FROM
        wp_mlj_issues issue
        LEFT JOIN atts oa ON oa.issue_id = id AND oa.attach_type = 'original'
        LEFT JOIN atts sa ON sa.issue_id = id AND sa.attach_type = 'scan'
    WHERE issue.mag_id = %d
    ORDER BY no
SQL, [$mag->id]));

if ($wpdb->last_error) {
    wp_die("pdf query error");
}

$issue_links = [];
foreach ($issue_links_res as $row) {
    $val = $row->orig_pdf_url ?? $row->scan_pdf_url;
    $issue_links[$row->no] = $val;
}
?>

<nav class="breadcrumbs"><?php
    $str = __("Periodicals", "icp-scratch");
    echo "<a href='..'>{$str}</a> &raquo; {$mag->title}"
?></nav>
<h1><?php echo $mag->title ?></h1>

<style>
    .issue-list {
        list-style: none;
        padding-left: 0;
        display: flex;
        flex-direction: column;
        gap: 1em;
    }

    .issue-entry-short {
        border: black 1px solid;
        padding-left: 1em;
        padding-right: 1em;
    }

    .issue-entry-short ol {
        margin-bottom: 1em;
    }

    .issue-entry-short-header {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }
</style>

<?php
$issues = $wpdb->get_results(
    $wpdb->prepare(
        <<<SQL
            SELECT no, slug, title, start_date, date_display
            FROM wp_mlj_issues
            WHERE mag_id = %d
            ORDER BY no DESC
        SQL,
        $mag->id
    )
);

if ($wpdb->last_error) {
    wp_die("query 2 error");
}

// Display query results
if (empty($issues)) {
    $txt = __("No issues listed.", "icp-scratch");
    echo "<p><i>$txt</i></p>";
} else {
    echo "<ul class='issue-list'>";
    foreach ($issues as $issue) {
        echo "<li class='issue-entry-short'>";
        echo "<div class='issue-entry-short-header'>";
        $date = format_mysql_date_with_specificity($issue->start_date, $issue->date_display);
        echo "<a href='{$issue->slug}'><h2>{$issue->title}</h2></a>";
        echo "<div>";
            echo "<p><time datetime='$date'>$date</time></p>";

            // PDF link
            // echo "test";
            $pdf_url = $issue_links[$issue->no];
            if ($pdf_url) {
                echo "<div style='text-align: right;'>[ <a href='$pdf_url'>PDF</a> ]</div>";
            }
            echo "</div>";
        echo "</div>";

        // Article list within issue
        $articles = $issue_articles[$issue->no];
        if (empty($articles)) {
            $txt = __("No articles listed.", "icp-scratch");
            echo "<p><i>{$txt}</i></p>";
        } else {
            echo "<ol>";
            foreach ($articles as $tuple) {
                // TODO refactor this and in issue.php

                // $url = text_url($tuple->ID, $tuple->post_slug, $tuple->language_code);
                $url = "{$issue->slug}/#{$tuple->trid}";
                // Display different styles depending on whether the article is translated or not
                if ($tuple->language_code == $current_lang) {
                    echo "<li><a href='{$url}'>{$tuple->post_title}</a></li>";
                } else {
                    // TODO refactor these styles and make function to generate text urls
                    echo "<li><a class='untranslated-link' href='{$url}'>
                        <span lang='{$tuple->language_code}'>{$tuple->post_title}</span> ({$tuple->lang_name})
                    </a></li>";
                }
            }
            echo "</ol>";
        }
        echo "</li>";
    }
    echo "</ul>";
}
?>