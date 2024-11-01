<?php

wp_bing_search_settings::load();

class WP_Bing_Search_Settings {

	private static $options = null;


	/**
	 * load
	 *
	 * Load this class
	 */
	public static function load() {

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ), 10 );
		add_action( 'admin_menu', array( __CLASS__, 'add_options_page' ), 10 );

		add_filter(
			'plugin_action_links_wp-bing-search/wp-bing-search.php',
			array(
				__CLASS__,
				'plugin_action_links',
			)
		);

		add_action( 'admin_notices', array( __CLASS__, 'admin_notice_setup' ), 10 );

	}

	/**
	 * Admin only
	 *
	 * Do admin_init stuff
	 */
	public static function admin_init() {

		self::$options = get_option( 'wp_bing_search' );

		self::$options = self::adjust_settings_if_no_piklist( wp_bing_search::$all_settings );

		self::setup_settings();
	}

	/**
	 * update the options array if using Piklist
	 *
	 * @param [type] $options
	 * @return void
	 */
	public static function adjust_settings_if_no_piklist( $options ) {

		$settings = array();

		if ( is_admin() ) {

			if ( ! class_exists( 'Piklist' ) && isset( $options) && is_array( $options['apiendpoint'] ) ) {

				foreach ( $options as $key => $value ) {

					$settings[ $key ] = $value[0];

				}

				update_option( 'wp_bing_search', $settings );

				$options = $settings;

			}
		}

		return $options;

	}

	/**
	 * plugin_action_links
	 *
	 * Add links on the plugins page
	 *
	 * @param $links
	 *
	 * @return array
	 */
	public static function plugin_action_links( $links ) {

		$custom_links = array(
			'<a href="' . admin_url( 'options-general.php?page=wp_bing_search' ) . '">' . __( 'Settings', 'wp-bing-search' ) . '</a>',
		);

		return array_merge( $links, $custom_links );

	}

	/**
	 * admin_notice_setup
	 *
	 * Let the user know if the plugin is not setup properly.
	 */
	public static function admin_notice_setup() {

		if ( ! wp_bing_search::good_to_go() ) {

			$class   = 'notice notice-error';
			$message = __( 'Bing Custom Search is not setup yet. Complete setup now and get better search results!.', 'wp-bing-search' );
			$link    = admin_url( 'options-general.php?page=wp_bing_search' );

			printf( '<div class="%1$s"><p>%3$s%2$s%4$s</p></div>', esc_attr( $class ), esc_html( $message ), '<a href="' . esc_url( $link ) . '">', '</a>' );

		}

	}

	/**
	 * add_options_page
	 *
	 * Add the settings page in WordPress
	 */
	public static function add_options_page() {

		add_options_page(
			'Bing Search',
			'Bing Search',
			'manage_options',
			'wp_bing_search',
			array(
				__CLASS__,
				'wp_bing_search_options_page',
			)
		);

	}

	/**
	 * setup_settings
	 *
	 * - Register setttings
	 * - Add settings section
	 * - Register settings fields
	 */
	public static function setup_settings() {

		register_setting( 'wp_bing_search', 'wp_bing_search' );

		add_settings_section(
			'wp_bing_search_section_1',
			'',
			'',
			'wp_bing_search'
		);

		add_settings_field(
			'apiendpoint',
			__( 'Bing API Endpoint', 'wp-bing-search' ),
			array( __CLASS__, 'apiendpoint_render' ),
			'wp_bing_search',
			'wp_bing_search_section_1'
		);

		add_settings_field(
			'customconfig',
			__( 'Custom Configuration ID', 'wp-bing-search' ),
			array( __CLASS__, 'customconfig_render' ),
			'wp_bing_search',
			'wp_bing_search_section_1'
		);

		add_settings_field(
			'mkt',
			__( 'Market', 'wp-bing-search' ),
			array( __CLASS__, 'mkt_render' ),
			'wp_bing_search',
			'wp_bing_search_section_1'
		);

		add_settings_field(
			'count',
			__( 'Results per page', 'wp-bing-search' ),
			array( __CLASS__, 'count_render' ),
			'wp_bing_search',
			'wp_bing_search_section_1'
		);

		add_settings_field(
			'safesearch',
			__( 'SafeSearch', 'wp-bing-search' ),
			array( __CLASS__, 'safesearch_render' ),
			'wp_bing_search',
			'wp_bing_search_section_1'
		);

		add_settings_field(
			'api_key',
			__( 'API Key', 'wp-bing-search' ),
			array( __CLASS__, 'api_key_render' ),
			'wp_bing_search',
			'wp_bing_search_section_1'
		);

	}

	/**
	 * apiendpoint_render
	 *
	 * render the apiendpoint field
	 */
	public static function apiendpoint_render() {
		?>

		<input type='url' class='large-text' name='wp_bing_search[apiendpoint]'
			value='<?php echo  isset( self::$options ) ? self::$options['apiendpoint'] :  ""; ?>'>
		<p class="description"><?php printf(__( 'If the Bing API Endpoint field is empty, the default Bing api endpoint will be used: %1$s', 'wp-bing-search' ),'<code>https://api.cognitive.microsoft.com/bingcustomsearch/v7.0/search</code>');?></p>
		<?php

	}


	/**
	 * customconfig_render
	 *
	 * render the customconfig field
	 */
	public static function customconfig_render() {
		?>

		<input type='text' class='large-text' name='wp_bing_search[customconfig]'
			value='<?php echo isset( self::$options ) ? self::$options['customconfig'] : ""; ?>'>
		<?php

	}


	/**
	 * mkt_render
	 *
	 * render the mkt field
	 */
	public static function mkt_render() {

		$mkts = wp_bing_search::get_search_mkts();

		?>

		<select name='wp_bing_search[mkt]'>

			<?php foreach ( $mkts as $key => $value ) : ?>

				<option value='<?php echo $key; ?>' <?php selected( isset( self::$options['mkt'] ) ? self::$options['mkt'] : '', $key ); ?>><?php echo esc_html( $value ); ?></option>

			<?php endforeach; ?>

		</select>

		<?php

	}


	/**
	 * count_render
	 *
	 * render the count field
	 */
	public static function count_render() {
		?>

		<input type='number' class='small-text' name='wp_bing_search[count]'
			   value='<?php echo esc_html( self::$options['count'] ); ?>'>

		<?php

	}


	/**
	 * safesearch_render
	 *
	 * render the safesearch field
	 */
	public static function safesearch_render() {

		$safeseach_options = wp_bing_search::get_safesearch_options();

		?>

		<select name='wp_bing_search[safesearch]'>

			<?php foreach ( $safeseach_options as $key => $value ) : ?>

				<option value='<?php echo esc_html( $key ); ?>' <?php isset( self::$options ) ? selected( self::$options['safesearch'], $key ) : ""; ?>><?php echo esc_html( $value ); ?></option>

			<?php endforeach; ?>

		</select>


		<?php

	}


	/**
	 * api_key_render
	 *
	 * render the api_key field
	 */
	public static function api_key_render() {
		?>

		<input type='text' class='large-text' name='wp_bing_search[api_key]'
			   value='<?php echo isset( self::$options ) ? esc_html( self::$options['api_key'] ) :  ""; ?>'>

		<?php

	}


	/**
	 * wp_bing_search_options_page
	 *
	 * Output the settings page
	 */
	public static function wp_bing_search_options_page() {
		?>

		<div class="wrap">


			<form action='options.php' method='post'>

				<h1><?php _e( 'Bing Search', 'wp-bing-search' ); ?></h1>

				<?php settings_fields( 'wp_bing_search' ); ?>
				<?php do_settings_sections( 'wp_bing_search' ); ?>
				<?php submit_button(); ?>

			</form>

			<h2><?php _e( 'Installation Instructions', 'wp-bing-search' ); ?></h2>
			<p>
				1. Sign up at <a href="https://customsearch.ai/" target="_blank">Microsoft</a> to get a free
				account.<br>
				2. Create a <a href="https://customsearch.ai/applications" target="_blank">new search engine
					(instance)</a><br>
				3. Refine your new search engine by adding your website, or any website(s) to the search.<br>
				4. Click on the "Custom Search Endpoint" button to go to your endpoint page.<br>
				5. Make note of your "Primary Key" and "Custom Configuration ID".<br>
				6. Fill in the fields above using your "Primary Key" and "Custom Configuration ID" from step 5.<br>
				8. Save your settings and start using your search as you normally do!<br>
			</p>

			<iframe width="560" height="315" src="https://www.youtube.com/embed/wOXwbJhvROI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>


		</div>
		<style>
			@media only screen and (min-width: 1160px) {
				.wrap {
					width: 50%;
				}
			}
		</style>
		<?php

	}

}
