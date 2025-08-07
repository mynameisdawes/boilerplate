<?php

namespace Vektor\Shop\Contracts;

interface InstanceIdentifier
{
    /**
     * Get the unique identifier to load the Cart from.
     *
     * @param null|mixed $options
     *
     * @return int|string
     */
    public function getInstanceIdentifier($options = null);

    /**
     * Get the unique identifier to load the Cart from.
     *
     * @param null|mixed $options
     *
     * @return int|string
     */
    public function getInstanceGlobalDiscount($options = null);
}
