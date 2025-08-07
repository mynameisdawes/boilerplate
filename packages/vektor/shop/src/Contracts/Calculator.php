<?php

namespace Vektor\Shop\Contracts;

use Vektor\Shop\CartItem;

interface Calculator
{
    public static function getAttribute(string $attribute, CartItem $cartItem);
}
