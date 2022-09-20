<?php

namespace WPGraphQL\WooCommerce\ProductSubscriptions;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Mutation\Cart_Mutation;

class ProductSubscriptionAddToCart {
	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'addToCartProductSubscription',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'productId' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => 'Product ID of the bundle to add to the cart',
			],
			'cart_item'  => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => 'Quantity of the subscription',
			],
      'variationId' => array(
				'type'        => 'Int',
				'description' => __( 'Cart item product variation database ID or global ID', 'woographql-product-subscriptions' ),
			),
				'quantity'  => [
				'type'        => 'Int',
				'description' => 'Quantity of the bundle',
			],
			
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'cartItem' => [
				'type'    => 'CartItem',
				'resolve' => function ( $payload ) {
					$item = \WC()->cart->get_cart_item( $payload['key'] );

					return $item;
				},
			],
			'cart'     => Cart_Mutation::get_cart_field( true ),
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function ( $input ) {
			Cart_Mutation::check_session_token();

			// Retrieve product database ID if relay ID provided.
			if ( empty( $input['productId'] ) ) {
				throw new UserError( 'No product ID provided' );
			}

			if ( ! \wc_get_product( $input['productId'] ) ) {
				throw new UserError( 'No product found matching the ID provided' );
			}

			if ( ! function_exists( 'WCS_ATT' ) ) {
				throw new UserError( 'Class WCS_ATT does not exist. Ensure that the Product Bundle plugin is active.' );
			}

			// Add item to cart and get item key.
			$cart_item_key = WCS_ATT()->cart->add_cart_item_data(
        $input['cart_item'],
				$input['productId'],
				$input['variation_id'],
				$input['quantity'] ? $input['quantity'] : 1,
			);

			if ( empty( $cart_item_key ) ) {
				throw new UserError( 'Failed to add cart item. Please check input.' );
			}

			if ( is_wp_error( $cart_item_key ) ) {
				// if ( $cart_item_key->error_data['woocommerce_bundle_configuration_invalid']['notices'] ) {
				// 	$notice = end( $cart_item_key->error_data['woocommerce_bundle_configuration_invalid']['notices'] );

				// 	// Bail if notice is not available
				// 	if ( empty( $notice['notice'] ) ) {
				// 		throw new UserError( $cart_item_key->get_error_message() );
				// 	}

				// 	// There is not filterable way to alter the error message. Let's hack this instead.
				// 	$message_offset = strpos( $notice['notice'], "There is not enough stock " );

				// 	// Remove the wc_notice <a>cart</a> text. All we want is the product and stock values
				// 	if ( ! empty( $message_offset ) ) {
				// 		throw new UserError( html_entity_decode( substr( $notice['notice'], $message_offset ) ) );
				// 	}
				// }

				throw new UserError( $cart_item_key->get_error_message() );
			}

			// Return payload.
			return [ 'key' => $cart_item_key ];
		};
	}
}

add_action( 'graphql_register_types', function () {
	ProductSubscriptionAddToCart::register_mutation();
} );