<?php
function riothere_get_catalogue() {
	register_rest_route( 'riothere/v1', 'catalog', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $request ) {

			$params                = $request->get_params();
			$main_category_id      = Riothere_Catalog::get_category_id_by_slug( 'categories' );
			$sizes_category_id     = Riothere_Catalog::get_category_id_by_slug( 'sizes' );
			$designers_category_id = Riothere_Catalog::get_category_id_by_slug( 'designers' );
			$colour_category_id    = Riothere_Catalog::get_category_id_by_slug( 'colors' );
			$trend_category_id     = Riothere_Catalog::get_category_id_by_slug( 'trend' );

			$Category  = Riothere_Catalog::get_child_categories( $main_category_id );
			$Size      = Riothere_Catalog::get_child_categories( $sizes_category_id );
			$Designers = Riothere_Catalog::get_child_categories( $designers_category_id );
			$Colour    = Riothere_Catalog::formatColors( Riothere_Catalog::get_child_categories( $colour_category_id ) );
			$Trend     = Riothere_Catalog::get_child_categories( $trend_category_id );


			$price_filters = [
				[
					'min' => 500,
				],
				[
					'max' => 1500,
				],
				[
					'min' => 500,
					'max' => 1500,
				],
				[
					'min' => 1500,
					'max' => 3000,
				]
			];
			$Price         = [];

			foreach ( $price_filters as $price_filter ) {
				$price_filter['count'] = Riothere_Catalog::get_products_count( $price_filter );
				$Price[]               = $price_filter;
			}

			$category    = Riothere_Catalog::get_category_by_slug( $params['type'] );
			$subCategory = Riothere_Catalog::get_category_by_slug( $params['subtype'] );
			$l3type 	 = Riothere_Catalog::get_category_by_slug( $params['l3type']);
			$trend       = Riothere_Catalog::get_category_by_slug( $params['trend'] );
			$designers = array_map('Riothere_Catalog::get_category_by_slug', explode(',', $params['designer']));
			$sizes = array_map('Riothere_Catalog::get_category_by_slug', explode(',', $params['size']));
			$colours = array_map('Riothere_Catalog::get_category_by_slug', explode(',', $params['colour']));
			
			$result = [
				'category'             => $category,
				'subCategory'          => $subCategory,
				'l3type'          	   => $l3type,
				'designers'            => $designers,
				'sizes'            	   => $sizes,
				'colours'            	   => $colours,
				'trend'                => $trend,
				'initialFilterOptions' => [
					'Category'  => $Category,
					'Size'      => $Size,
					'Designers' => $Designers,
					'Colour'    => $Colour,
					'Trend'     => $Trend,
					'Price'     => $Price,
					'SortBy'    => [
						'DATE|DESC'  => [
							'name' => 'New In',
						],
						'DATE|ASC'   => [
							'name' => 'Oldest',
						],
						'PRICE|ASC'  => [
							'name' => 'Price (ASC)',
						],
						'PRICE|DESC' => [
							'name' => 'Price (DESC)',
						],
					],
				],
			];

			return rest_ensure_response( $result );
		}
	) );
}
add_action('rest_api_init', 'riothere_get_catalogue');
