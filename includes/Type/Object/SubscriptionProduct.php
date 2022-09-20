<?php

namespace WPGraphQL\WooCommerce\ProductSubscriptions;

use WPGraphQL\WooCommerce\Type\WPObject\Product_Types;
use WPGraphQL\WooCommerce\Type\WPInterface\Product;

const TYPE_SUBSCRIPTION_PRODUCT = 'SubscriptionProduct';

/**
 * Register SubscriptionProduct Type
 */
add_action( 'graphql_register_types', function () {
	
	/**
	 * Register the subscription product fields
	 *
	 * @return array[]
	 */
  // private $offset_map = array(
	// 	'subscription_period'            => 'period',
	// 	'subscription_period_interval'   => 'interval',
	// 	'subscription_length'            => 'length',
	// 	'subscription_payment_sync_date' => 'sync_date',
	// 	'subscription_trial_period'      => 'trial_period',
	// 	'subscription_trial_length'      => 'trial_length',
	// 	'subscription_pricing_method'    => 'pricing_mode',
	// 	'subscription_discount'          => 'discount',
	// 	'subscription_regular_price'     => 'regular_price',
	// 	'subscription_sale_price'        => 'sale_price',
	// 	'subscription_price'             => 'price'
	// );
  function get_product_subscription_fields(): array {
		return [
			'period'        => [
				'type'        => 'String',
				'description' => __( 'Subscription period', 'woographql-product-subscriptions' ),
				'resolve'     => function ( $source ) {
					$period = $source->get_period();
					
					return ! empty( $period ) ? $period : null;
				},
			],
			'interval'        => [
				'type'        => 'String',
				'description' => __( 'Subscription interval', 'woographql-product-subscriptions' ),
				'resolve'     => function ( $source ) {
					$interval = $source->get_interval();

					return ! empty( $interval ) ? $interval : null;
				},
			],
			'length'                => [
				'type'        => 'String',
				'description' => __( 'Subscription length', 'woographql-product-subscriptions' ),
				'resolve'     => function ( $source ) {
					$length = $source->get_length();

					return ! empty( $length ) ? $length : null;
				},
			],
			'trial_period'             => [
				'type'        => 'String',
				'description' => __( 'Subscription Trial Period', 'woographql-product-subscriptions' ),
				'resolve' => function ( $source ) {
					$trial_period = $source->get_trial_period();

					return ! empty( $trial_period ) ? $trial_period : null;
				},
			],
			'trial_length' => [
				'type'        => 'String',
				'description' => __( 'Subscription Trial Length', 'woographql-product-subscriptions' ),
				'resolve' => function ( $source ) {
					$trial_length = $source->get_trial_length();

					return ! empty( $trial_length ) ? $trial_length : null;
				},
			],
      	'regular_price' => [
				'type'        => 'String',
				'description' => __( 'Subscription Regular Price', 'woographql-product-subscriptions' ),
				'resolve' => function ( $source ) {
					$regular_price = $source->get_regular_price();

					return ! empty( $regular_price ) ? $regular_price : null;
				},
			],
       	'discount' => [
				'type'        => 'String',
				'description' => __( 'Subscription Discount', 'woographql-product-subscriptions' ),
				'resolve' => function ( $source ) {
					$discount = $source->get_discount();

					return ! empty( $discount ) ? $discount : null;
				},
			],
      'price' => [
				'type'        => 'String',
				'description' => __( 'Subscription Price', 'woographql-product-subscriptions' ),
				'resolve' => function ( $source ) {
					$price = $source->get_discounted_price();

					return ! empty( $price ) ? $price : null;
				},
			],
		];
	}
	
	/**
	 * Register the Object Type
	 */
	register_graphql_object_type(
		TYPE_SUBSCRIPTION_PRODUCT,
		[
			'description' => __( 'A product subscription object', 'woographql-product-subscriptions' ),
			'interfaces'  => Product_Types::get_product_interfaces(),
			'fields'      =>
				array_merge(
					Product::get_fields(),
					// Product_Types::get_pricing_and_tax_fields(),
					// Product_Types::get_shipping_fields(),
					// Product_Types::get_inventory_fields(),
					get_product_subscription_fields(),
				),
		]
	);
	
	// add_filter( 'graphql_bundle_product_model_use_pricing_and_tax_fields', '__return_true' );
	// add_filter( 'graphql_bundle_product_model_use_inventory_fields', '__return_true' );
	// add_filter( 'graphql_bundle_product_model_use_virtual_data_fields', '__return_true' );
	// add_filter( 'graphql_bundle_product_model_use_variation_pricing_fields', '__return_false' );
	// add_filter( 'graphql_bundle_product_model_use_external_fields', '__return_false' );
	// add_filter( 'graphql_bundle_product_model_use_grouped_fields', '__return_false' );
	

} );

/**
 * Register SUBSCRIPTION enum so that input filters work
 */
add_filter( 'graphql_product_types_enum_values', function ( $values ) {
	$values['SUBSCRIPTION'] = [
		'value'       => 'subscription',
		'description' => __( 'A subscription product', 'woographql-product-subscriptions' ),
	];
	
	return $values;
} );

/**
 * Register our Product Subscription to WooGraphQL
 */
add_filter( 'graphql_woocommerce_product_types', function ( $product_types ) {
	$product_types['subscription'] = TYPE_SUBSCRIPTION_PRODUCT;
	
	return $product_types;
} );
