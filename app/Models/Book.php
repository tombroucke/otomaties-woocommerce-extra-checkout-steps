<?php

namespace Otomaties\WooCommerceExtraCheckoutSteps\Models;

use Otomaties\WooCommerceExtraCheckoutSteps\Models\Abstracts\Post;

class Book extends Post
{
    public static function postType() : string
    {
        return 'book';
    }
}
