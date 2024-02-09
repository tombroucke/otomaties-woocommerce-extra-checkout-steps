<?php

namespace Otomaties\WooCommerceExtraCheckoutSteps\OptionsPages;

use Otomaties\WooCommerceExtraCheckoutSteps\OptionsPages\Abstracts\OptionsPage as AbstractsOptionsPage;
use Otomaties\WooCommerceExtraCheckoutSteps\OptionsPages\Contracts\OptionsPage;
use StoutLogic\AcfBuilder\FieldsBuilder;

class ExtraCheckoutStepsOptions extends AbstractsOptionsPage implements OptionsPage
{
    protected string $slug = 'woocommerce-extra-checkout-steps-settings';

    protected string $title = 'Extra Checkout Steps';

    protected string $menuTitle = 'Extra Checkout Steps';

    public function __construct()
    {
        $this->title = __('Extra Checkout Steps', 'woocommerce-extra-checkout-steps');
        $this->menuTitle = __('Extra Checkout Steps', 'woocommerce-extra-checkout-steps');
    }

    protected function fields(FieldsBuilder $fieldsBuilder) : FieldsBuilder
    {
        $fieldsBuilder
            ->addRepeater('extra_checkout_steps', [
                'label' => __('Extra checkout steps', 'woocommerce-extra-checkout-steps'),
                'default_value' => 'bar',
            ])
            ->addText('compact_title', [
                'label' => __('Compact title', 'woocommerce-extra-checkout-steps'),
            ])
            ->addPostObject('page', [
                'label' => __('Page', 'woocommerce-extra-checkout-steps'),
                'post_type' => ['page'],
                'multiple' => 0,
                'return_format' => 'id',
            ])
            ->endRepeater();
        return $fieldsBuilder;
    }
}
