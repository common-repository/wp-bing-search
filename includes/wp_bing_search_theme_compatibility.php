<?php

wp_bing_search_theme_compatibility::load();

class WP_Bing_Search_Theme_Compatibility {

	private static $counter = 1;

	private static $posts_pre_query_array = null;

	public static function load() {

		add_filter( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ), 10 );

		add_filter( 'posts_pre_query', array( __CLASS__, 'posts_pre_query' ), 10, 2 );

		add_filter( 'posts_results', array( __CLASS__, 'switch_post_object_to_bing' ), 10, 2 );
		add_filter( 'post_link', array( __CLASS__, 'change_permalink' ), 10, 3 );
		add_filter( 'the_author', array( __CLASS__, 'the_author' ), 10 );

		add_filter( 'get_search_query', array( __CLASS__, 'get_search_query' ), 10 );
		add_action( 'pre_get_search_form', array( __CLASS__, 'pre_get_search_form' ), 10 );
		add_filter( 'get_search_form', array( __CLASS__, 'after_get_search_form' ), -1 );

		add_filter( 'body_class', array( __CLASS__, 'add_bing_class' ), 10 );
	}


	/**
	 * pre_get_posts
	 *
	 * Ping api and set some variables.
	 *
	 * @param $wp_query
	 */
	public static function pre_get_posts( $wp_query ) {

		if ( ! $wp_query->is_admin && $wp_query->is_search && $wp_query->is_main_query() ) {

			wp_bing_search::$is_search = true;

			add_filter( 'get_edit_post_link', array( __CLASS__, 'get_edit_post_link' ), 10, 3 );

			// Set offset
			self::set_offset( $wp_query );

			// Get bing api results
			$results = wp_bing_search::get_response(
				wp_bing_search::get_args(
					array(
						'offset' => wp_bing_search::$offset,
					)
				)
			);

			if ( $results ) {

				global $wp_query;

				set_query_var( 'no_found_rows', false );

				// loop through results, create a new wp_query array with the results and assign to $api_results
				wp_bing_search::$api_results = self::map_bing_api_results_to_wp_query_posts( $results );

				self::set_found_posts();

				self::set_max_num_pages();

			} else {

				wp_bing_search::$api_results = null;

				return $wp_query;

			}
		}

	}

	/**
	 * posts_pre_query
	 *
	 * Stop WordPress from making a database call if we are using Bing.
	 *
	 * @param $posts
	 * @param $query
	 *
	 * @return null
	 */
	public static function posts_pre_query( $posts, $query ) {

		if ( ! $query->is_admin && $query->is_search && $query->is_main_query() ) {

			$posts = self::$posts_pre_query_array;
		}

		return $posts;

	}

	/**
	 * switch_post_object_to_bing
	 *
	 * Return Bing results if we have some, otherwise return nothing.
	 *
	 * @param $posts
	 * @param $wp_query
	 *
	 * @return null
	 */
	public static function switch_post_object_to_bing( $posts, $wp_query ) {

		if ( ! $wp_query->is_admin && $wp_query->is_search && $wp_query->is_main_query() && wp_bing_search::$api_results !== null ) {

			return wp_bing_search::$api_results;

		} elseif ( ! $wp_query->is_admin && $wp_query->is_search && $wp_query->is_main_query() && wp_bing_search::$api_results === null ) {

			return null;

		} else {

			return $posts;

		}

	}

	/**
	 * map_bing_api_results_to_wp_query_posts
	 *
	 * Create a new query with bing results mapped to WordPress query
	 *
	 * @param $results
	 *
	 * @return array
	 */
	public static function map_bing_api_results_to_wp_query_posts( $results ) {

		$posts_array           = array();
		$posts_pre_query_array = array();

		foreach ( $results as $result => $bing ) {

			$url = apply_filters( 'wp_bing_search_api_url', $bing->url );

			$site_post_id = url_to_postid( $url );

			if ( $site_post_id != '0' ) { // We have a matching post_id

				$post = get_post( $site_post_id );

			} else { // Use Bing data

				/**
				 * wp_bing_search_result_data_array
				 *
				 * Filter the array that maps to post data.
				 */
				$bing = apply_filters( 'wp_bing_search_result_data_array', $bing );

				$post_id                 = - abs( self::$counter );
				$post                    = new stdClass();
				$post->ID                = $post_id;
				$post->post_author       = 0;
				$post->post_date         = null;
				$post->post_date_gmt     = null;
				$post->post_modified     = null;
				$post->post_modified_gmt = null;
				$post->post_title        = $bing->name;
				$post->post_content      = $bing->snippet;
				$post->post_excerpt      = $bing->snippet;
				$post->post_status       = 'publish';
				$post->comment_status    = 'closed';
				$post->ping_status       = 'closed';
				$post->post_name         = sanitize_title( $bing->name );
				$post->post_type         = 'post';
				$post->filter            = 'raw';
				$post->guid              = $url;
			}

			$posts_array[ $result ]           = $post;
			$posts_pre_query_array[ $result ] = $post->ID;

			self::$counter ++;
		}

		self::$posts_pre_query_array = $posts_pre_query_array;

		return $posts_array;
	}

	/**
	 * set_offset
	 *
	 * Set wp_bing_search::$offset
	 *
	 * @param $query
	 */
	public static function set_offset( $query ) {

		if ( isset( $query->query['paged'] ) ) {

			wp_bing_search::$offset = ( $query->query['paged'] - 1 ) * wp_bing_search::$posts_per_page;

		}

	}

	/**
	 * set_found_posts
	 *
	 * Set found_posts in $wp_query
	 */
	public static function set_found_posts() {

		global $wp_query;

		if ( ! $wp_query->is_admin && $wp_query->is_search && $wp_query->is_main_query() ) {

			$estimated_matches           = wp_bing_search::$estimated_matches;
			$offset                      = wp_bing_search::$offset;
			wp_bing_search::$found_posts = $estimated_matches - $offset;

			$wp_query->found_posts = wp_bing_search::$found_posts;
		}

	}

	/**
	 * set_max_num_pages
	 *
	 * Set set_max_num_pages in $wp_query
	 */
	public static function set_max_num_pages() {

		global $wp_query;

		if ( ! $wp_query->is_admin && $wp_query->is_search && $wp_query->is_main_query() ) {

			$max_num_pages = wp_bing_search::$found_posts / wp_bing_search::$posts_per_page;

			$wp_query->max_num_pages = ceil( $max_num_pages );
		}

	}

	/**
	 * change_permalink
	 *
	 * Update permalinks to the $post->guid, which we set to the Bing url
	 *
	 * @param $url
	 * @param $post
	 * @param bool $leavename
	 *
	 * @return mixed
	 */
	public static function change_permalink( $url, $post, $leavename = false ) {

		if ( $post->ID > 0 ) {

			return $url;

		}

		return $post->guid;

	}

	/**
	 * get_edit_post_link
	 *
	 * If the post doesn't live on the site (result from another website)
	 * then hide the "edit post" link
	 *
	 * @param $link
	 * @param $post_id
	 * @param $context
	 *
	 * @return null
	 */
	public static function get_edit_post_link( $link, $post_id, $context ) {

		if ( $post_id > 0 ) {

			return $link;

		}

		return null;

	}

	/**
	 * Handle author data
	 */
	public static function the_author( $authordata ) {

		global $post;

		if ( $post->ID > 0 ) {

			return $authordata;

		}

		return self::parse_url( PHP_URL_HOST );

	}

	/**
	 * Add wp-bing-search to the body class of search pages
	 *
	 * @param [type] $classes
	 * @return void
	 */
	public static function add_bing_class( $classes ) {

		if ( wp_bing_search::$api_results !== null ) {

			$classes[] = 'wp-bing-search';

		}

		return $classes;

	}

	/**
	 * get_search_query
	 *
	 * Add altered query if exists
	 *
	 * @param $search_query
	 *
	 * @return string
	 */
	public static function get_search_query( $search_query ) {

		if ( wp_bing_search::$alteredQuery ) {

			return sprintf( esc_attr__( '%1$s (Including: %2$s)', 'wp-bing-search' ), $search_query, wp_bing_search::$alteredQuery );

		}

		return $search_query;

	}

	/**
	 * pre_get_search_form
	 *
	 * Remove altered query from search form.
	 * Only show original query in search form
	 */
	public static function pre_get_search_form() {

		remove_filter( 'get_search_query', array( __CLASS__, 'get_search_query' ), 10 );

	}

	/**
	 * after_get_search_form
	 *
	 * Restore the filter to show the altered query
	 * after the search form is already rendered
	 *
	 * @param $form
	 *
	 * @return mixed
	 */
	public static function after_get_search_form( $form ) {

		add_filter( 'get_search_query', array( __CLASS__, 'get_search_query' ), 10 );

		return $form;
	}


	/**
	 * Parse the story url
	 *
	 * @param [type] $component
	 * @return void
	 */
	public static function parse_url( $component = PHP_URL_HOST ) {

		global $post;

		return wp_parse_url( $post->guid, $component );

	}

}
