<?php

// - Meta box for custom tables
// https://wordpress.stackexchange.com/questions/186026/add-special-meta-box-to-custom-post-type

function register_indices_meta_box() {
    // Adds the box into the editor
    add_action('add_meta_boxes_article', function() {
        add_meta_box(
            'intcp-text-indices',
            __("Indices", 'icp-scratch'),
            'html_callback',
            'article',
            'side',
            'core',
        );
    });

    // Handles the saving of the data once the post is saved
    add_action('save_post_article', 'save_text_callback');
}

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

// Assumes trid exists
function set_text_indices($post_id, $checked_index_ids) {
    global $wpdb;

    $post_trid = $wpdb->get_results(<<<SQL
        SELECT trid
        FROM wp_icl_translations
        WHERE element_type = 'post_article' AND element_id = $post_id
        LIMIT 1
    SQL)[0]->trid;

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

function html_callback($post) {
    // Boilerplate stuff to avoid CSRF I guess
    wp_nonce_field('save-indices', 'intcp_indices_nonce');

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

// Note: This hook is called twice when saving.
// The first time, $_POST is empty, and we should ignore it
// Then second time it will actually contain the data we care about
// The first time is simply filtered by the first if statement, since the nonce won't exist
function save_text_callback($post_id) {
    // Nonce checking boilerplate
    if (!isset($_POST['intcp_indices_nonce'])
    ||  !wp_verify_nonce($_POST['intcp_indices_nonce'], 'save-indices')) return;

    // Authorization check
    if (!current_user_can('edit_post', $post_id)) return;

    // This means that indices won't be autosaved or preserved by revision history
    // WPML doesn't seem to assign trids to these ever, so I'm afraid of doing otherwise
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

    // Manually create translation group if it doesn't exist
    // This is a workaround around WPML behavior
    // Otherwise indices won't be saved when publishing a newly created post
    $post_trid = intval($_POST['icl_trid']);
    if (!$post_trid) {
        global $sitepress;
        $sitepress->set_element_language_details(
            $post_id,
            'post_article',
            null,
            $_POST['icl_post_language'],
        );
    }

    // This comes from the form POST request that WordPress sends when saving the post
    // Important: We need a default because the key will not exist whenever the list is actually empty
    $checked_indices_input = $_POST['intcp_indices_ids'] ?: [];

    // Parse/sanitize input. Converting the presumed string ids to actual integers should be safe enough.
    // Failure will result in 0, which is not a valid index id and will error when trying to insert into the tables.
    $checked_indices = array_map(intval(...), $checked_indices_input);

    set_text_indices($post_id, $checked_indices);
}