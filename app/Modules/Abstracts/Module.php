<?php

namespace Otomaties\WooCommerceExtraCheckoutSteps\Modules\Abstracts;

use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\View;
use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Loader;
use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Assets;
use Otomaties\WooCommerceExtraCheckoutSteps\Plugin;

abstract class Module
{
    public function __construct(
        protected Loader $loader,
        protected View $view,
        protected Assets $assets,
        protected Plugin $plugin,
    ) {
    }

    abstract public function init();
}
