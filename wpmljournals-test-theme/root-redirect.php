<?php
/*
Template Name: Root redirect
*/

// This is used as the template for the WPML root page at `/`, which is coded to redirect to `/en/`

wp_redirect(home_url());

exit;