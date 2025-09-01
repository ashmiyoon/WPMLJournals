<style>
.search {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 1rem;
}

.search-right-pane {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.search-bar {
    display: flex;
    gap: 0.5rem;
}

.search-text {
    flex-grow: 1;
}

.search-options {
    max-width: max(26em, 25%);
}

.search-options ul {
    list-style: none;
    padding-left: 1rem;
}

.search-options li > label {
    position: relative;
    left: 0.9em;
}

.search-results ul.additions {
    padding-left: 0;
}

</style>

<h1><?php _e("Search", "icp-scratch") ?></h1>

<?php
function print_input($cat_id, $cat_name) {
    $checked = $_GET["cat-{$cat_id}"] == 'on' ? "checked" : "";
    echo "<label><input name='cat-{$cat_id}' type='checkbox' {$checked}></input> {$cat_name}</label>";
}

function print_details($parent_slug, $parent_name, $cats) {
    echo "<details open>";
    echo "<summary>";
    print_input($parent_slug, $parent_name);
    echo "</summary>";
    print_details_list($cats);
    echo "</details>";
}

function print_details_list($cats) {
    global $wpdb;
    echo "<ul>";
    foreach ($cats as $cat) {
        echo "<li>";
        // $children = get_categories(array(
        //     'parent' => $cat->term_id,
        // ));
        $children = $wpdb->get_results($wpdb->prepare(<<<SQL
            SELECT tm.term_id, tm.name
            FROM wp_term_taxonomy tax
            JOIN wp_terms tm ON tm.term_id = tax.term_id
            WHERE tax.parent = %d
            ORDER BY tm.name
        SQL, $cat->term_id));
        if ($children) {
            print_details($cat->term_id, $cat->name, $children);
        } else {
            print_input($cat->term_id, $cat->name);
        }
        echo "</li>";
    }
    echo "</ul>";
}
?>

<form class="search">
    <div open class="search-options">
        <?php // <label><input name="titles-only" type="checkbox">Search titles only</label> ?>
        <details>
            <summary><?php _e("Categories", "icp-scratch") ?></summary>
            <?php print_details_list(get_categories(array(
                'parent' => 0,
            ))) ?>
        </details>
    </div>
    <div class="search-right-pane">
        <div class="search-bar">
            <input type="search" name="query" class="search-text"
                value="<?php echo stripslashes(esc_attr(trim(($_GET["query"])))) ?>"
            ></input>
            <input type="submit" value='<?php _e("Search", "icp-scratch") ?>'></input>
        </div>
        <div class="search-results">
            <?php
            if ($_GET['query'] !== null) {
                // $keywords = explode(" ", $_GET['query']);
                $text_query = $_GET['query'];
                $cats = array();
                foreach ($_GET as $key => $value) {
                    if (str_starts_with($key, 'cat-') && $value == 'on') {
                        $cats[] = substr($key, 4);
                    }
                }

                // // Select all descendant posts of category in the current language
                // // TODO use mysql full-text search?
                // $article_query = $wpdb->get_results($wpdb->prepare(<<<SQL
                //     SELECT DISTINCT post_name, post_title, post_date
                //     FROM wp_posts post
                //     JOIN wp_icl_translations tr ON tr.element_id = post.ID
                //     JOIN wp_term_relationships rel ON rel.object_id = post.ID
                //     WHERE post_type = 'article'
                //     AND post_status = 'publish'
                //     AND post_content LIKE CONCAT("%", %s, "%")
                //     AND tr.element_type = 'post_article'
                //     AND tr.language_code = %s
                //     AND rel.term_taxonomy_id IN (
                //         WITH RECURSIVE cte AS (
                //             SELECT %d AS child_term, %d AS child_tax
                //         UNION
                //             SELECT tax.term_id AS child_term, tax.term_taxonomy_id AS child_tax
                //             FROM wp_term_taxonomy tax
                //             JOIN cte ON tax.parent = cte.child_term
                //         )
                //         SELECT child_tax FROM cte
                //     )
                //     ORDER BY post_date DESC
                // SQL, $TODO));
            
                $wpq = new WP_Query(array(
                    's' => $text_query,
                    'post_type' => 'article',
                    'category__in' => $cats,
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                ));
                $id_list = $wpq->get_posts();

                global $wpdb;
                $id_list_str = "(" . implode(",", $id_list) . ")";
                $sql = <<<SQL
                    SELECT ID, post_name, post_title, post_date
                    FROM wp_posts
                    WHERE ID IN {$id_list_str}
                    ORDER BY post_date DESC
                SQL;
                // Interpolating the string directly should be fine because WP_query results are guaranteed to be just integers
            
                $results = $wpdb->get_results($sql);
                get_template_part('parts/text-listing', null, array('pubs' => $results));
            }
            ?>
        </div>
    </div>
</form>