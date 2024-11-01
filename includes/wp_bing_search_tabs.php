<?php

wp_bing_search_tabs::load();

class WP_Bing_Search_Tabs {

	public static function load() {

			add_action( 'loop_start', array( __CLASS__, 'add_tabs' ), 10 );

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );

	}

	public static function wp_enqueue_scripts() {

		if ( wp_bing_search::$api_results != null ) {

			wp_enqueue_style( 'wp-bing-search-tabs', wp_bing_search::url() . 'parts/css/wp-bing-search-tabs.css', array(), false );

		}

	}


	public static function add_tabs( $query ) {

		if ( ! $query->is_admin && $query->is_search && wp_bing_search::$api_results !== null ) {

			echo self::get_tab_markup();
		}

	}

	public static function get_tab_markup() {

		ob_start();

		include wp_bing_search::path() . 'parts/shared/tab-header.php';

		$contents = ob_get_contents();

		ob_end_clean();

		return $contents;

	}

}
