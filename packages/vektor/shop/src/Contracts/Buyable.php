<?php

namespace Vektor\Shop\Contracts;

interface Buyable
{
    /**
     * Get the identifier of the Buyable item.
     *
     * @param null|mixed $options
     *
     * @return int|string
     */
    public function getBuyableIdentifier($options = null);

    /**
     * Get the description or title of the Buyable item.
     *
     * @param null|mixed $options
     *
     * @return string
     */
    public function getBuyableDescription($options = null);

    /**
     * Get the price of the Buyable item.
     *
     * @param null|mixed $options
     *
     * @return float
     */
    public function getBuyablePrice($options = null);

    /**
     * Get the weight of the Buyable item.
     *
     * @param null|mixed $options
     *
     * @return float
     */
    public function getBuyableWeight($options = null);
}
