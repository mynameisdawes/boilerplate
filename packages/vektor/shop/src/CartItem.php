<?php

namespace Vektor\Shop;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Vektor\Shop\Calculation\DefaultCalculator;
use Vektor\Shop\Contracts\Buyable;
use Vektor\Shop\Contracts\Calculator;
use Vektor\Shop\Exceptions\InvalidCalculatorException;

/**
 * @property mixed discount
 * @property float discountTotal
 * @property float priceTarget
 * @property float priceNet
 * @property float priceTotal
 * @property float subtotal
 * @property float taxTotal
 * @property float tax
 * @property float total
 * @property float priceTax
 */
class CartItem implements Arrayable, Jsonable
{
    /**
     * The rowID of the cart item.
     *
     * @var string
     */
    public $rowId;

    /**
     * The ID of the cart item.
     *
     * @var int|string
     */
    public $id;

    /**
     * The quantity for this cart item.
     *
     * @var float|int
     */
    public $qty;

    /**
     * The name of the cart item.
     *
     * @var string
     */
    public $name;

    /**
     * The price without TAX of the cart item.
     *
     * @var float
     */
    public $price;

    /**
     * The type of the cart item.
     *
     * @var string
     */
    public $type;

    /**
     * The weight of the product.
     *
     * @var float
     */
    public $weight;

    /**
     * The options for this cart item.
     *
     * @var array|CartItemOptions
     */
    public $options;

    /**
     * The attributes for this cart item.
     *
     * @var array|CartItemAttributes
     */
    public $attributes;

    /**
     * The tax rate for the cart item.
     *
     * @var float|int
     */
    public $taxRate = 0;

    /**
     * The cart instance of the cart item.
     *
     * @var null|string
     */
    public $instance;

    /**
     * The FQN of the associated model.
     *
     * @var null|string
     */
    private $associatedModel;

    /**
     * The discount rate for the cart item.
     *
     * @var float
     */
    private $discountRate = 0;

    /**
     * CartItem constructor.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     * @param float      $weight
     * @param null|mixed $type
     */
    public function __construct($id, $name, $price, $type = null, $weight = 0, array $options = [], array $attributes = [])
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Please supply a valid identifier.');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Please supply a valid name.');
        }
        if (strlen($price) < 0 || !is_numeric($price)) {
            throw new \InvalidArgumentException('Please supply a valid price.');
        }
        if (strlen($weight) < 0 || !is_numeric($weight)) {
            throw new \InvalidArgumentException('Please supply a valid weight.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->price = floatval($price);
        $this->type = $type;
        $this->weight = floatval($weight);
        $this->options = new CartItemOptions($options);
        $this->attributes = new CartItemAttributes($attributes);
        $this->rowId = $this->generateRowId($id, $options);
    }

    /**
     * Get an attribute from the cart item or get the associated model.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
        $decimals = config('shop.format.decimals', 2);

        switch ($attribute) {
            case 'model':
                if (isset($this->associatedModel)) {
                    return with(new $this->associatedModel())->find($this->id);
                }

                // no break
            case 'modelFQCN':
                if (isset($this->associatedModel)) {
                    return $this->associatedModel;
                }

                // no break
            case 'weightTotal':
                return round($this->weight * $this->qty, $decimals);
        }

        $class = new \ReflectionClass(config('shop.calculator', DefaultCalculator::class));
        if (!$class->implementsInterface(Calculator::class)) {
            throw new InvalidCalculatorException('The configured Calculator seems to be invalid. Calculators have to implement the Calculator Contract.');
        }

        return call_user_func($class->getName().'::getAttribute', $attribute, $this);
    }

    /**
     * Returns the formatted weight.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function weight($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->weight, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted price without TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function price($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->price, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted price with discount applied.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function priceTarget($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->priceTarget, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted price with TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function priceTax($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->priceTax, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted subtotal.
     * Subtotal is price for whole CartItem without TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function subtotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->subtotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted total.
     * Total is price for whole CartItem with TAX.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->total, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted tax.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->tax, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted tax.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function taxTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->taxTotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted discount.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function discount($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->discount, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted total discount for this cart item.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function discountTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->discountTotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Returns the formatted total price for this cart item.
     *
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    public function priceTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->priceTotal, $decimals, $decimalPoint, $thousandSeperator);
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param float|int $qty
     */
    public function setQuantity($qty)
    {
        if (empty($qty) || !is_numeric($qty)) {
            throw new \InvalidArgumentException('Please supply a valid quantity.');
        }

        $this->qty = $qty;
    }

    /**
     * Update the cart item from a Buyable.
     */
    public function updateFromBuyable(Buyable $item)
    {
        $this->id = $item->getBuyableIdentifier($this->options);
        $this->name = $item->getBuyableDescription($this->options);
        $this->price = $item->getBuyablePrice($this->options);
    }

    /**
     * Update the cart item from an array.
     */
    public function updateFromArray(array $attributes)
    {
        $this->id = Arr::get($attributes, 'id', $this->id);
        $this->qty = Arr::get($attributes, 'qty', $this->qty);
        $this->name = Arr::get($attributes, 'name', $this->name);
        $this->price = Arr::get($attributes, 'price', $this->price);
        $this->type = Arr::get($attributes, 'type', $this->type);
        $this->weight = Arr::get($attributes, 'weight', $this->weight);
        $this->options = new CartItemOptions(Arr::get($attributes, 'options', $this->options));
        $this->attributes = new CartItemAttributes(Arr::get($attributes, 'attributes', $this->attributes));

        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }

    /**
     * Associate the cart item with the given model.
     *
     * @param mixed $model
     *
     * @return CartItem
     */
    public function associate($model)
    {
        $this->associatedModel = is_string($model) ? $model : get_class($model);

        return $this;
    }

    /**
     * Set the tax rate.
     *
     * @param float|int $taxRate
     *
     * @return CartItem
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Set the discount rate.
     *
     * @param float|int $discountRate
     *
     * @return CartItem
     */
    public function setDiscountRate($discountRate)
    {
        $this->discountRate = $discountRate;

        return $this;
    }

    /**
     * Set cart instance.
     *
     * @param null|string $instance
     *
     * @return CartItem
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * Create a new instance from a Buyable.
     *
     * @return CartItem
     */
    public static function fromBuyable(Buyable $item, array $options = [])
    {
        return new self($item->getBuyableIdentifier($options), $item->getBuyableDescription($options), $item->getBuyablePrice($options), $item->getBuyableWeight($options), $options);
    }

    /**
     * Create a new instance from the given array.
     *
     * @return CartItem
     */
    public static function fromArray(array $attributes)
    {
        $attributes_type = Arr::get($attributes, 'type', null);
        $attributes_options = Arr::get($attributes, 'options', []);
        $attributes_attributes = Arr::get($attributes, 'attributes', []);

        return new self($attributes['id'], $attributes['name'], $attributes['price'], $attributes_type, $attributes['weight'], $attributes_options, $attributes_attributes);
    }

    /**
     * Create a new instance from the given attributes.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     * @param mixed      $type
     * @param mixed      $weight
     *
     * @return CartItem
     */
    public static function fromAttributes($id, $name, $price, $type, $weight, array $options = [], array $attributes = [])
    {
        return new self($id, $name, $price, $type, $weight, $options, $attributes);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'rowId' => $this->rowId,
            'id' => $this->id,
            'name' => $this->name,
            'qty' => $this->qty,
            'price' => $this->price,
            'type' => $this->type,
            'weight' => $this->weight,
            'options' => is_object($this->options)
                ? $this->options->toArray()
                : $this->options,
            'attributes' => is_object($this->attributes)
                ? $this->attributes->toArray()
                : $this->attributes,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'subtotal' => $this->subtotal,
            'custom_price' => isset($this->custom_price) ? $this->custom_price : null,
            'display_price' => isset($this->display_price) ? $this->display_price : null,
            'display_subtotal' => isset($this->display_subtotal) ? $this->display_subtotal : null,
            'formatted' => isset($this->formatted) ? $this->formatted : null,
            'product' => isset($this->product) ? $this->product : null,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Getter for the raw internal discount rate.
     * Should be used in calculators.
     *
     * @return float
     */
    public function getDiscountRate()
    {
        return $this->discountRate;
    }

    /**
     * Generate a unique id for the cart item.
     *
     * @param string $id
     *
     * @return string
     */
    protected function generateRowId($id, array $options)
    {
        ksort($options);

        return md5($id.serialize($options));
    }

    /**
     * Get the formatted number.
     *
     * @param float  $value
     * @param int    $decimals
     * @param string $decimalPoint
     * @param string $thousandSeperator
     *
     * @return string
     */
    private function numberFormat($value, $decimals, $decimalPoint, $thousandSeperator)
    {
        if (is_null($decimals)) {
            $decimals = config('shop.format.decimals', 2);
        }

        if (is_null($decimalPoint)) {
            $decimalPoint = config('shop.format.decimal_point', '.');
        }

        if (is_null($thousandSeperator)) {
            $thousandSeperator = config('shop.format.thousand_separator', ',');
        }

        return number_format($value, $decimals, $decimalPoint, $thousandSeperator);
    }
}
