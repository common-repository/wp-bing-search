<?php $settings = wp_bing_search::$all_settings; ?>

<?php $tab_name = $settings['tab_name']; ?>

<div id="wp-bing-search-tabs">

	<?php foreach ( $tab_name as $tab => $name ) : ?>

		<?php

		$class = 'wp-bing-search-tab';

		$current_url = get_site_url() . $_SERVER['REQUEST_URI'];

		$url = add_query_arg(
			array(
				'bing_search_tab' => $tab,
			),
			$current_url
		);

		// are we asking for a tab
		if ( isset( $_GET['bing_search_tab'] ) ) {

			// if the tab == the current tab, then add active-wp-bing-search-tab to class
			if ( $_GET['bing_search_tab'] == $tab ) {

				$class = 'wp-bing-search-tab active-wp-bing-search-tab';

			}
		} else {

			if ( $tab == 0 ) {

				$class = 'wp-bing-search-tab active-wp-bing-search-tab';
			}
		}


		?>

		<a class="<?php echo $class; ?>" href="<?php echo $url; ?>" class="wp-bing-search-tab"><?php echo $name; ?></a>

	<?php endforeach; ?>




</div>
