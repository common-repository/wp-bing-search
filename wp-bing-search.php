<?php
/*
Plugin Name: Bing Custom Search for WordPress
Description: Bing Custom Search for WordPress
Version: 2.6.3
Author: SlipFire
Author URI: http://slipfire.com
Text Domain: wp-bing-search
Domain Path: /languages
License: GPLv2
Plugin Type: Piklist
*/

/*
	Copyright (c) 2012-2022 SlipFire Corp.
	All rights reserved.

	This software is distributed under the GNU General Public License, Version 2,
	June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
	St, Fifth Floor, Boston, MA 02110, USA

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

	*******************************************************************************
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
	ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*******************************************************************************
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Start the show
 */
wp_bing_search::load();

/**
 * Main Bing search class
 *
 * @since 1.0.0
 */
class WP_Bing_Search {

	/**
	 * Bing search endpoint
	 *
	 * @version 1.0.0
	 * @var string
	 */
	public static $custom_search_endpoint = 'https://api.cognitive.microsoft.com/bingcustomsearch/v7.0/search';

	/**
	 * Are we doing a search?
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    boolean
	 */
	public static $is_search = false;

	/**
	 * Hold active settings
	 *
	 * @since  1.0.0
	 * @access public
	 * @var array
	 */
	public static $all_settings = null;

	/**
	 * Hold settings for individual tab
	 * or if no tabs, return all data
	 *
	 * @since  2.0.0
	 * @access public
	 * @var array
	 */
	public static $tab_settings = null;

	/**
	 * Search results from Bing api
	 *
	 * @since  1.0.0
	 * @access public
	 * @var array
	 */
	public static $api_results = null;

	/**
	 * The original query from the Bing api
	 *
	 * @since  1.0.0
	 * @access public
	 * @var array
	 */
	public static $originalQuery = null;

	/**
	 * The altered query from the Bing api
	 *
	 * @since  1.0.0
	 * @access public
	 * @var array
	 */
	public static $alteredQuery = null;

	/**
	 * Estimated matches returned from Bing api
	 *
	 * @since  1.0.0
	 * @access public
	 * @var integer
	 */
	public static $estimated_matches = 0;

	/**
	 * Offset calculated
	 *
	 * @since  1.0.0
	 * @access public
	 * @var integer
	 */
	public static $offset = 0;

	/**
	 * Estimated matches - the offset
	 *
	 * @since  1.0.0
	 * @access public
	 * @var integer
	 */
	public static $found_posts = 0;

	/**
	 * Set default for posts per page
	 *
	 * @since  1.0.0
	 * @access public
	 * @var integer
	 */
	public static $posts_per_page = 10;

	/**
	 * Are we using tabs?
	 *
	 * @since  1.0.0
	 * @access public
	 * @var integer
	 */
	public static $using_tabs = false;

	/**
	 * Set default for tabs
	 *
	 * @since  1.0.0
	 * @access public
	 * @var integer
	 */
	public static $bing_tab = 0;

	/**
	 * Let's start Binging!
	 */
	public static function load() {

		add_action( 'init', array( __CLASS__, 'init' ), 10 );

		/**
		 * If the Piklist plugin is active
		 * let Piklist render the setting page
		 * otherwise use the default settings page
		 */
		if ( class_exists( 'Piklist' ) ) {
			add_filter( 'piklist_admin_pages', array( __CLASS__, 'let_piklist_render_settings_page' ) );
		} else {
			include 'includes/wp_bing_search_settings.php';
		}

		/**
		 * If we're good to go
		 * load the appropriate code for the search results.
		 */
		if ( self::good_to_go() ) {

			if ( isset( self::$all_settings['customconfig'][1] ) ) {
				include 'includes/wp_bing_search_tabs.php';
			}

			include 'includes/wp_bing_search_theme_compatibility.php';

		}
	}

	/**
	 * Get full url path to this plugins directory
	 *
	 * @return string
	 */
	public static function url() {

		return plugins_url( '/', __FILE__ );

	}

	/**
	 * Get filesystem directory path to this plugins directory
	 *
	 * @return string
	 */
	public static function path() {

		return plugin_dir_path( __FILE__ );

	}

	/**
	 * Items to run on init
	 */
	public static function init() {

		load_plugin_textdomain( 'wp-bing-search', false, basename( dirname( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Various checks to make sure we are good to go
	 *
	 * @return bool
	 */
	public static function good_to_go() {

		// Get settings
		self::$tab_settings = self::get_settings( self::which_search_tab_are_we_on() );

		// If multidimensional array is set, then we are using tabs
		self::$using_tabs = ! empty( self::$tab_settings['apiendpoint'][1] ) ? true : false;

		// Are the two fields besides apiendpoint set?
		if ( ! empty( self::$tab_settings['customconfig'] )
			&& ! empty( self::$tab_settings['api_key'] )
			) {
			return true;
		}

		return false;

	}

	/**
	 * Convert settings page to Piklist
	 *
	 * If Piklist plugin is activated, let it render the settings page
	 *
	 * @param array $pages
	 *
	 * @return array
	 */
	public static function let_piklist_render_settings_page( $pages ) {

		$pages[] = array(
			'page_title' => __( 'Bing Search', 'wp-bing-search' ),
			'menu_title' => __( 'Bing Search', 'wp-bing-search' ),
			'sub_menu'   => 'options-general.php',
			'capability' => 'manage_options',
			'menu_slug'  => 'wp_bing_search',
			'setting'    => 'wp_bing_search',
		);

		return $pages;
	}

	/**
	 * Loads theme compatibility
	 *
	 * @return mixed|void
	 */
	public static function load_theme_compatibility() {

		$load = true;

		/**
		 * Filters whether to load theme compatibility.
		 *
		 * You might want to stop theme compatibility if you use a shortcode or custom function instead.
		 *
		 * @param bool $load Whether theme compatibility should be loaded.
		 */
		return apply_filters( 'wp_bing_search_theme_compatibility', $load );
	}


	/**
	 * If we are using tabs, return the tab number from the url,
	 * otherwise return 0
	 *
	 * @return int
	 */
	public static function which_search_tab_are_we_on() {

		if ( isset( $_GET['bing_search_tab'] ) && is_numeric( $_GET['bing_search_tab'] ) ) {

			return intval( $_GET['bing_search_tab'] );

		}

		return 0;
	}

	/**
	 * Get the plugin settings
	 *
	 * @return array
	 */
	public static function get_settings( $bing_tab ) {

		$basic_settings_array = get_option( 'wp_bing_search' );

		if ( ! empty( $basic_settings_array ) ) {

			/**
			 * Tabs = multidimensional array of settings
			 * Single = simple array of settings
			 *
			 * Normalize data so it's always a multidimensional array of settings
			 */
			self::$all_settings = is_array( $basic_settings_array['apiendpoint'] ) ? $basic_settings_array : self::create_multidimensional_settings_array( $basic_settings_array );

			$settings_array = array(
				'apiendpoint' => self::$all_settings['apiendpoint'][ $bing_tab ],
				'customconfig' => self::$all_settings['customconfig'][ $bing_tab ],
				'mkt'          => self::$all_settings['mkt'][ $bing_tab ],
				'count'        => self::$all_settings['count'][ $bing_tab ],
				'safesearch'   => self::$all_settings['safesearch'][ $bing_tab ],
				'api_key'      => self::$all_settings['api_key'][ $bing_tab ],
			);

			/**
			 * Filter the settings array
			 */
			$setttings_array = apply_filters( 'wp_bing_search_settings', $settings_array );

			return $settings_array;

		}

		return false;

	}

	/**
	 * If we're using basic settings, convert to multidimensional array
	 *
	 * @param array $basic_settings_array
	 * @return void
	 */
	public static function create_multidimensional_settings_array( $basic_settings_array ) {

		$multidimensional_array = array();

		if ( ! empty( $basic_settings_array ) ) {

			foreach ( $basic_settings_array as $key => $value ) {
				$multidimensional_array[ $key ][0] = $value;
			}
		}

		return $multidimensional_array;

	}

	/**
	 * replace settings with additional args
	 *
	 * @param array $additional_args
	 *
	 * @return array
	 */
	public static function get_args( $additional_args = array() ) {

		$args = wp_parse_args( $additional_args, self::$tab_settings );

		self::$posts_per_page = $args['count'];
		self::$offset         = isset( $args['offset'] ) ? $args['offset'] : 0;

		$args['apiendpoint'] = ! empty( $args['apiendpoint'] ) ? $args['apiendpoint'] : wp_bing_search::$custom_search_endpoint;

		return $args;

	}


	/**
	 * get_response
	 *
	 * Get the search response from Bing.
	 *
	 * @param null $args
	 *
	 * @return bool
	 */
	public static function get_response( $args = null ) {

		$endpoint = self::build_endpoint( $args );
		$get_args = self::get_args();

		$options['headers']['Ocp-Apim-Subscription-Key'] = $get_args['api_key'];

		$response = wp_remote_get( $endpoint, $options );

		if ( is_wp_error( $response ) ) {

			return false;

		}

		if ( 200 !== $response['response']['code'] ) {

			wp_bing_search::$api_results = null;

			return;

		}

		$body = json_decode( $response['body'] );

		if ( ! empty( $body->queryContext ) ) {

			self::$originalQuery = ! empty( $body->queryContext->originalQuery ) ? $body->queryContext->originalQuery : null;
			self::$alteredQuery  = ! empty( $body->queryContext->alteredQuery ) ? $body->queryContext->alteredQuery : null;

		}

		$webPages = $body->webPages;

		if ( $webPages ) {

			self::$estimated_matches = $webPages->totalEstimatedMatches;

			$results = $webPages->value;

			return $results;

		} else {

			return false;

		}

	}

	/**
	 * build_endpoint
	 *
	 * build the endpoint to ping Bing.
	 *
	 * @param $args
	 *
	 * @return bool|string
	 */
	public static function build_endpoint( $args ) {

		$endpoint = add_query_arg(
			array(
				'mkt'          => $args['mkt'],
				'count'        => $args['count'],
				'offset'       => $args['offset'],
				'safesearch'   => $args['safesearch'],
				'customconfig' => $args['customconfig'],
				'q'            => $_GET['s'],
			),
			! empty( $args['apiendpoint'] ) ? $args['apiendpoint'] :  wp_bing_search::$custom_search_endpoint
		);

		return $endpoint;

	}

	/**
	 * get_matches
	 *
	 * Get estimated search results number
	 *
	 * @return int
	 */
	public static function get_matches() {

		return intval( self::$estimated_matches );

	}

	public static function get_search_mkts() {

		$mkts = array(
			'es-AR' => 'es-AR',
			'en-AU' => 'en-AU',
			'de-AT' => 'de-AT',
			'nl-BE' => 'nl-BE',
			'fr-BE' => 'fr-BE',
			'pt-BR' => 'pt-BR',
			'en-CA' => 'en-CA',
			'fr-CA' => 'fr-CA',
			'es-CL' => 'es-CL',
			'da-DK' => 'da-DK',
			'fi-FI' => 'fi-FI',
			'fr-FR' => 'fr-FR',
			'de-DE' => 'de-DE',
			'zh-HK' => 'zh-HK',
			'en-IN' => 'en-IN',
			'en-ID' => 'en-ID',
			'it-IT' => 'it-IT',
			'ja-JP' => 'ja-JP',
			'ko-KR' => 'ko-KR',
			'en-MY' => 'en-MY',
			'es-MX' => 'es-MX',
			'nl-NL' => 'nl-NL',
			'en-NZ' => 'en-NZ',
			'no-NO' => 'no-NO',
			'zh-CN' => 'zh-CN',
			'pl-PL' => 'pl-PL',
			'pt-PT' => 'pt-PT',
			'en-PH' => 'en-PH',
			'ru-RU' => 'ru-RU',
			'ar-SA' => 'ar-SA',
			'en-ZA' => 'en-ZA',
			'es-ES' => 'es-ES',
			'sv-SE' => 'sv-SE',
			'fr-CH' => 'fr-CH',
			'de-CH' => 'de-CH',
			'zh-TW' => 'zh-TW',
			'tr-TR' => 'tr-TR',
			'en-GB' => 'en-GB',
			'en-US' => 'en-US',
			'es-US' => 'es-US',
		);

		return $mkts;
	}

	public static function get_safesearch_options() {

		$safeseach_options = array(
			'off'      => __( 'Off', 'wp-bing-search' ),
			'moderate' => __( 'Moderate', 'wp-bing-search' ),
			'strict'   => __( 'Strict', 'wp-bing-search' ),
		);

		return $safeseach_options;
	}
    
}
