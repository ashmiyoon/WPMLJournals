<?php

function register_custom_rest_route() {
    register_rest_route('mlj-edit', '/request/', array(
        'methods' => 'POST',
        'callback' => 'mlj_handle_request',
        'permission_callback' => 'mlj_rest_permissions_check',
    ));
}
add_action('rest_api_init', 'register_custom_rest_route');

function mlj_rest_permissions_check(WP_REST_Request $request) {
    if ($request->get_param("auth") == "SUPER SECRET CODE") {
        return true;
    } else {
        return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot access this resource.' ), array( 'status' => 403 ) );
    }

    /*
    // Check if user is administrator
    $username = $request->get_param('username');
    $password = $request->get_param('password');

    $the_user = wp_authenticate($username, $password);

    if ($the_user instanceof WP_User and in_array("administrator", $the_user->roles)) {
        return true;
    } else {
        return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot access this resource.' ), array( 'status' => 403 ) );
    }
     */
}

function selection() {

}

function mlj_handle_request(WP_REST_Request $request) {
    global $wpdb;

    $action = $request->get_param("action");

    return mlj_send_tables($request);
}

function mlj_add_assignment(WP_REST_Request $request) {
    global $wpdb;

    $wpdb->insert(
        "wp_mlj_assigned_issues",
        array(
            'id' => NULL,
            'text_trid' => intval($request->get_param('article'))
        )
    );
}

function mlj_send_tables(WP_REST_Request $request) {
    global $wpdb;

    $resp = array();

    $sql_mag_cols = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'wp_mlj_magazines';";
    $col_results = $wpdb->get_results($sql_mag_cols, ARRAY_N);
    $cols = array_map(function($row) {return $row[0]; }, $col_results);
    $resp["mag_columns"] = $cols;

    $sql_mag_data = "SELECT * FROM wp_mlj_magazines ORDER BY start_year";
    $mag_results = $wpdb->get_results($sql_mag_data, ARRAY_N);
    $resp["mag_data"] = $mag_results;

    return new WP_REST_Response(
        $resp,
        200
    );
    
}

?>
