<?php

require_once(__DIR__ . "/metaboxes.php");
require_once(__DIR__ . "/metaboxes/indices.php");
require_once(__DIR__ . "/metaboxes/featured.php");

function register_editor_features() {
    register_metaboxes([
        indices_metabox_info(),
        featured_metabox_info(),
    ]);
}