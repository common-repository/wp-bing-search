<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$option_name = 'wp_bing_search';

delete_option($option_name);
delete_site_option($option_name);
