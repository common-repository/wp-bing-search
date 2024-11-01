<?php
/*
Title: Settings
Setting: wp_bing_search
Order: 10
Tab: Main
Flow: WP Bing Search Settings
*/

	piklist(
		'field',
		array(
			'type'     => 'group',
			'label'    => __( 'Settings', 'wp-bing-search' ),
			'add_more' => true,
			'fields'   => array(
				array(
					'type'    => 'text',
					'field'   => 'tab_name',
					'label'   => __( 'Tab Name', 'wp-bing-search' ),
					'columns' => 12,
				),
				array(
					'type'    => 'url',
					'field'   => 'apiendpoint',
					'label'   => __( 'Bing API Endpoint', 'wp-bing-search' ),
					'description'   => printf(__( 'If the Bing API Endpoint field is empty, the default Bing api endpoint will be used: %1$s', 'wp-bing-search' ),'<code>https://api.cognitive.microsoft.com/bingcustomsearch/v7.0/search</code>'),
					'columns' => 12,
				),
				array(
					'type'    => 'text',
					'field'   => 'customconfig',
					'label'   => __( 'Custom Configuration ID', 'wp-bing-search' ),
					'columns' => 12,
				),
				array(
					'type'    => 'select',
					'field'   => 'mkt',
					'label'   => __( 'Market', 'wp-bing-search' ),
					'columns' => 2,
					'choices' => wp_bing_search::get_search_mkts(),
				),
				array(
					'type'    => 'number',
					'field'   => 'count',
					'label'   => __( 'Results per page', 'wp-bing-search' ),
					'columns' => 3,
				),
				array(
					'type'    => 'select',
					'field'   => 'safesearch',
					'label'   => __( 'SafeSearch', 'wp-bing-search' ),
					'columns' => 2,
					'choices' => wp_bing_search::get_safesearch_options(),
				),
				array(
					'type'    => 'text',
					'field'   => 'api_key',
					'label'   => __( 'API Key', 'wp-bing-search' ),
					'columns' => 12,
				),
			),
		)
	);
