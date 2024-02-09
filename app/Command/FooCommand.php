<?php

namespace Otomaties\WooCommerceExtraCheckoutSteps\Command;

use Otomaties\WooCommerceExtraCheckoutSteps\Command\Contracts\CommandContract;

class FooCommand implements CommandContract
{
    
    public const COMMAND_NAME = 'woocommerce-extra-checkout-steps foo';

    public const COMMAND_DESCRIPTION = 'Example command';

    public const COMMAND_ARGUMENTS = [
        [
            'type' => 'assoc',
            'name' => 'bar',
            'description' => 'Enter a value for bar',
            'optional' => false,
        ]
    ];

    public function __construct()
    {
    }

    /**
     * Run below command to activate:
     *
     * wp vrd sync handle
     */
    public function handle(array $args, array $assocArgs): void
    {
        $defaultAssocArgs = [
            'article-limit' => null,
            'skip-images' => false,
        ];
        $assocArgs = array_merge($defaultAssocArgs, $assocArgs);

        \WP_CLI::success('foo: ' . $assocArgs['bar']);
    }
}
