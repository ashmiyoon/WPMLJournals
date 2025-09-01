<?php

function intcp_add_issue_apply_box() {
	$screens = [ 'post', 'article'];
	foreach ( $screens as $screen ) {
		add_meta_box(
			'wporg_box_id',                 // Unique ID
			'Apply issue',      // Box title
			'intcp_issue_apply_box_html',  // Content callback, must be of type callable
			$screen,                            // Post type
			"side"
		);
	}
}
add_action( 'add_meta_boxes', 'intcp_add_issue_apply_box' );

function get_assignments($trid) {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare(<<<SQL
        SELECT issue.title, ass.issue_id, ass.position
        FROM wp_mlj_assigned_issues ass
        JOIN wp_mlj_issues issue ON ass.issue_id = issue.id
        WHERE ass.text_trid = %d
        ORDER BY issue.start_date ASC
    SQL, $trid), ARRAY_A);
}

// This is only the initial HTML, not a data-dependent render function
function intcp_issue_apply_box_html( $post ) {
	?>
	<label for="intcp_magazines">Select magazine</label>
	<select name="intcp_magazine_field" id="intcp_magazine" class="postbox">
        <?php
            global $wpdb;
            $mags = $wpdb->get_results("SELECT id, title FROM `wp_mlj_magazines` ORDER BY start_year DESC");
            foreach($mags as $mag) {
                ?>
                    <option value="<?php echo $mag->id; ?>"><?php echo $mag->title; ?></option>
                <?php
            }
        ?>
	</select>
    <label for="intcp_issues">Select issue</label>
    <select name="intcp_issue_field" id="intcp_issues" class="postbox">
        <?php 
            global $wpdb;
            $issues = $wpdb->get_results(<<<SQL
                SELECT id, title
                FROM wp_mlj_issues
                WHERE mag_id = (
                    SELECT id FROM wp_mlj_magazines
                    ORDER BY start_year DESC
                    LIMIT 1
                )
                ORDER BY no DESC
            SQL);
            foreach($issues as $issue) {
                ?>
                    <option value="<?php echo $issue->id; ?>"><?php echo $issue->title; ?></option>
                <?php
            }
        ?>
    </select>
    <fieldset>
        <label for="intcp_position_input">Position</label>
        <input id="intcp_position_input" type="number"></input>
    </fieldset>
    <button id="intcp_add_button">Add</button>
    <p>Appears in these issues:</p>
    <ul id="intcp_issue_list"> 
        <?php 
            global $wpdb;
            global $post;
            $trid = apply_filters('wpml_element_trid', NULL, $post->ID);
            $issues = get_assignments($trid);
            foreach($issues as $issue) {
                ?>
                    <li>
                        <?php echo $issue['title'] ?>
                        <button onclick="delete_assignment(<?php echo $issue['issue_id'] ?>)">X</button>
                    </li>
                <?php
            }
        ?>
    </ul>
	<?php
}

// The piece after `wp_ajax_`  matches the action argument being sent in the POST request.
add_action( 'wp_ajax_intcp_magazine_ajax_change', 'intcp_magazine_ajax_handler' );
add_action( 'wp_ajax_intcp_issue_ajax_add_issue', 'intcp_issue_ajax_add_handler' );
add_action( 'wp_ajax_intcp_issue_ajax_delete_issue', 'intcp_issue_ajax_delete_handler' );

/**
 * Handles my AJAX request.
 */
function intcp_magazine_ajax_handler() {
    // Handle the ajax request here
    if ( array_key_exists( 'intcp_magazine_field_value', $_POST ) ) {
        $post_id = (int) $_POST['post_ID'];
        if ( current_user_can( 'edit_post', $post_id ) ) {
            $value = (int) $_POST['intcp_magazine_field_value'];
            global $wpdb;
            $issues = $wpdb->get_results($wpdb->prepare("SELECT id, title FROM `wp_mlj_issues` WHERE mag_id = %d ORDER BY no DESC;", $value), ARRAY_A);
            wp_send_json_success($issues);
        }
    }
 
    wp_die(); // All ajax handlers die when finished
}

// NOTE: TAKE TRID FROM ID
function intcp_issue_ajax_add_handler() {
    // Handle the ajax request here
    if ( array_key_exists( 'intcp_issue_field_value', $_POST ) ) {
        $post_id = (int) $_POST['post_ID'];
        $trid = apply_filters('wpml_element_trid', NULL, $post_id);
        if ( current_user_can( 'edit_post', $post_id ) ) {
            $issue_id_value = (int) $_POST['intcp_issue_field_value'];
            $position_value = (int) $_POST['intcp_issue_field_value_position'];
            global $wpdb;
            /*
            $wpdb->insert('wp_mlj_assigned_issues', array(
                "text_trid" => $trid,
                "issue_id" => $issue_id_value,
                "position" => $position_value
            ));
             */
            $sql = $wpdb->prepare(
                <<<SQL
                    INSERT INTO wp_mlj_assigned_issues (text_trid, issue_id, position)
                    VALUES (%s, %s, %s)
                SQL,
                $trid,
                $issue_id_value,
                $position_value,
            );
            $wpdb->query($sql);
            $issues = get_assignments($trid);
            wp_send_json_success($issues);
        }
    }
 
    wp_die(); // All ajax handlers die when finished
}

function intcp_issue_ajax_delete_handler() {
    // Handle the ajax request here
    if ( array_key_exists( 'intcp_issue_field_value', $_POST ) ) {
        $post_id = (int) $_POST['post_ID'];
        $trid = apply_filters('wpml_element_trid', NULL, $post_id);
        if ( current_user_can( 'edit_post', $post_id ) ) {
            $issue_id_value = (int) $_POST['intcp_issue_field_value'];
            global $wpdb;
            $wpdb->query($wpdb->prepare(
                <<<SQL
                    DELETE FROM wp_mlj_assigned_issues WHERE text_trid = %d AND issue_id = %d
                SQL,
                $trid,
                $issue_id_value
            ));
            $issues = get_assignments($trid);
            wp_send_json_success($issues);
        }
    }
 
    wp_die(); // All ajax handlers die when finished
}

?>
