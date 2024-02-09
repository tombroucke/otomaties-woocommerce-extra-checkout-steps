<?php

namespace Otomaties\WooCommerceExtraCheckoutSteps\Command\Contracts;

interface CommandContract
{
    public function handle(array $args, array $assocArgs): void;
}
