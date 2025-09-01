<?php

// - Meta box for custom tables
// https://wordpress.stackexchange.com/questions/186026/add-special-meta-box-to-custom-post-type

function create_trid_for_post($post_id, $lang) {
    // Ugly HACK relying on internal WPML functionality
    global $sitepress;
    $sitepress->set_element_language_details(
        $post_id,
        'post_article',
        null,
        $lang,
    );

    return apply_filters('wpml_element_trid', NULL, $post_id, 'post_article');
}

function html_callback_for($info) {
    return function($post) use($info) {
        // Boilerplate stuff to avoid CSRF I guess
        wp_nonce_field("save-{$info['slug']}", "intcp_{$info['slug']}_nonce");

        $info['html_callback']($post);
    };
}

// Note: This hook is called twice when saving.
// The first time, $_POST is empty, and we should ignore it
// Then second time it will actually contain the data we care about
// The first time is simply filtered by the nonce check, since the nonce won't exist
function save_text_callback_for($infos) {
    return function($post_id) use($infos) {
        // Authorization check
        if (!current_user_can('edit_post', $post_id)) return;

        // This means that the meta box data won't be autosaved or preserved by revision history
        // WPML doesn't seem to assign trids to these ever, so I'm afraid of doing otherwise
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

        // Manually create translation group if it doesn't exist
        // This is a workaround around WPML behavior
        // Otherwise per-trid data like indices won't be saved when publishing a newly created post
        $post_trid = intval($_POST['icl_trid']);
        if (!$post_trid) {
            $post_trid = create_trid_for_post($post_id, $_POST['icl_post_language']);
        }

        foreach ($infos as $info) {
            // Nonce checking boilerplate
            if (!isset($_POST["intcp_{$info['slug']}_nonce"])
            ||  !wp_verify_nonce($_POST["intcp_{$info['slug']}_nonce"], "save-{$info['slug']}")) return;

            // The $_POST variable will now have key-value pairs for the form fields specified in the HTML
            // This comes from the form POST request that WordPress sends when saving the post
            $info['save_callback']($post_trid);
        }
    };
}

function register_metaboxes($infos) {
    // each info describes a meta box, and has params: slug, name, html_callback, save_callback
    // slug is used for internal ids and should be distinct for each of our meta boxes
    // name is for the meta box title
    // html callback takes a WP_Post argument and should be an html template for the meta box, with the necessary form fields. the post trid may not exist here
    // save callback takes a post trid argument and includes the form field arguments within the $_POST variable. the post trid is always valid here

    // Adds the boxes into the editor
    add_action('add_meta_boxes_article', function() use($infos) {
        foreach ($infos as $info) {
            add_meta_box(
                "intcp-text-{$info['slug']}",
                __($info['name'], 'icp-scratch'),
                html_callback_for($info),
                'article',
                'side',
                'core',
            );
        }
    });

    // Handles the saving of the data once the post is saved
    // Our wrapper takes care of dispatching the right save callback to each meta box
    add_action('save_post_article', save_text_callback_for($infos));
}