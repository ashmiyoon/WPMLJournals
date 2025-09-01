<?php

function get_text_indices($post_id) {
    global $wpdb;

    // Get the post trid, then select all text indices, adding a 'checked' column
    // This column is true for all indices the post is associated to
    return $wpdb->get_results(<<<SQL
        SELECT
            id,
            slug,
            id IN (
                SELECT index_id
                FROM wp_mlj_assigned_indices
                WHERE text_trid = (
                    SELECT trid
                    FROM wp_icl_translations
                    WHERE element_type = 'post_article' AND element_id = $post_id
                    LIMIT 1
                )
            ) AS checked
        FROM wp_mlj_indices
        ORDER BY id ASC
    SQL);
}

function set_text_indices($post_trid, $checked_index_ids) {
    global $wpdb;

    // Now we simply delete all the existing index entries for the post and save only the checked ones
    // This should be simpler and more robust than tracking the individual changes

    $values = implode(",", array_map(
        fn($index_id) => "($post_trid, $index_id)",
        $checked_index_ids)
    );

    // (I'm not sure I like this split multi-statement query, what if an exception is thrown in the middle)
    $wpdb->query("START TRANSACTION");
    $res1 = $wpdb->query("DELETE FROM wp_mlj_assigned_indices WHERE text_trid = $post_trid");
    $res2 = true;
    if ($checked_index_ids) {
        // This query only works when the list is not empty because SQL is a bad language
        $res2 = $wpdb->query("INSERT INTO wp_mlj_assigned_indices (text_trid, index_id) VALUES $values");
    }
    // Need to explicitly compare with false (signals error) since query will return 0 (which is a falsy value) when no rows are affected
    if ($res1 !== false && $res2 !== false) {
        $wpdb->query("COMMIT");
    } else {
        $wpdb->query("ROLLBACK");
    }
}

function indices_html_callback($post) {
    // Display fields vertically
    $style = "display: flex; flex-direction: column; align-items: flex-start";

    echo "<div style='$style'>";
    $indices = get_text_indices($post->ID);
    foreach ($indices as $index) {
        $name = index_name($index->slug);
        $checked = $index->checked ? "checked" : "";
        echo "<label style='cursor: pointer'><input type='checkbox' name='intcp_indices_ids[]' value={$index->id} $checked></input>$name</label>";
        // With this specification, the POST request will return an array of the specified values for the checked boxes only
        // In this case, these values will be the index ids
    }
    echo "</div>";
}

function indices_save_callback($post_trid) {
    // Important: We need a default because the key will not exist whenever the list is actually empty
    $checked_indices_input = $_POST['intcp_indices_ids'] ?: [];

    // Parse/sanitize input. Converting the presumed string ids to actual integers should be safe enough.
    // Failure will result in 0, which is not a valid index id and will error when trying to insert into the tables.
    $checked_indices = array_map(intval(...), $checked_indices_input);

    set_text_indices($post_trid, $checked_indices);    
}

function indices_metabox_info() {
    return [
        'slug' => "indices",
        'name' => "Indices",
        'html_callback' => 'indices_html_callback',
        'save_callback' => 'indices_save_callback',
    ];
}