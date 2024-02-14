# Otomaties WooCommerce Extra Checkout Steps

This plugin provides a way to add extra steps to the checkout process

## Prerequisites
- PHP 8.x
- ACF PRO

## Installation
`composer require tombroucke/otomaties-events`

## Usage
1. Activate the plugin
2. Add one or more extra checkout pages e.g. checkout/billing, checkout/shipping, checkout/overview
3. Add these pages in the correct order in the "Extra Checkout Steps" options page.
4. Create a shortcode for each extra page. e.g.:
	```php
	add_shortcode('checkout_billing_information', function () {
		return view('shortcodes.checkout-personal-details', [
			'adminPostUrl' => admin_url('admin-post.php'),
            'data' => WooCommerceExtraCheckoutSteps()->make(\Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Steps::class)->find(get_the_ID())->getData(),
		])->toHtml();
	});
	```
5. Add the view for the shortcode. Make sure to add the fields for extra steps, and add a unique name to it (`WooCommerceExtraCheckoutSteps()->make('form-fields', ['name' => 'personal_details'])`).
	```blade
	@if(function_exists('WooCommerceExtraCheckoutSteps'))
	{!! woocommerce_output_all_notices() !!}

	<div>
		{!! woocommerce_checkout_login_form() !!}
	</div>

	<form method="POST" action="{!! $adminPostUrl !!}">
		<div class="mb-3">
			<label for="custom_field" class="form-label">Custom field</label>
			<input type="text" name="custom_field" class="form-control" id="custom_field" value="{!! $data['custom_field'] ?? '' !!}">
		</div>
		{!! wc_get_template('checkout/form-billing.php', array('checkout' => WC()->checkout)) !!}
		{!! WooCommerceExtraCheckoutSteps()->make('form-fields', ['name' => 'personal_details']) !!}
		<button type="submit" class="btn btn-primary">
			{!! __('Continue', 'text-domain') !!}
		</button>
	</form>
	@endif
	```
6. For each page: add some validation logic if needed:
	```php
	add_action('woocommerce_extra_checkout_steps_verify_fields', function($stepName, $currentStep, $nextStep, $steps) {
	if ($stepName === 'personal_details') {
			$fields = [
				'billing_first_name' => [
					'label' => __('First name', 'text-domain'),
					'validate' => [
						'required',
						[
							'function' => function ($name) {
								return strlen($name) > 2;
							},
							'message' => __('First name should be at least 2 letters', 'text-domain')
						]
					]
				],
				'billing_last_name' => [
					'label' => __('Last name', 'text-domain'),
					'validate' => ['required']
				],
				// ...
			];
	    $currentStep->verifyFields($fields);
	}
	}, 10, 4)
	```
7. The data is saved to the step in `WC()->session`. If needed, you can use a hook to save the data elsewhere:
	```php
	add_action('woocommerce_extra_checkout_steps_data_saved', function($callback, $data, $currentStep, $nextStep) {
		// save the data
	}, 10, 4)
	```
8. The default WooCommerce fields are saved by WooCommerce. Possibly, you need to save custom fields to your order. You can use the `woocommerce_checkout_order_processed` for this.
	```php
	add_action('woocommerce_checkout_order_processed', function ($orderId, $postedData, $order) {
		WooCommerceExtraCheckoutSteps()->make(\Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Steps::class)
			->each(function($step) {
				$stepData = $step->getData();
				$order->update_meta_data('custom_field', $stepData['custom_field']);
				$order->save();
			});
	});
	```
