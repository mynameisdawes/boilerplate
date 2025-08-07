<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductMarkup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_type',
        'min_qty',
        'max_qty',
        'markup_pct',
    ];

    protected $hidden = [
        'product_type',
    ];

    public static function getByType(string $type): Collection
    {
        return self::select('min_qty', 'max_qty', 'markup_pct', 'product_type')
            ->where('product_type', $type)
            ->get()
        ;
    }

    public static function grouped(): Collection
    {
        return self::select('min_qty', 'max_qty', 'markup_pct', 'product_type')
            ->get()
            ->groupBy('product_type')
        ;
    }
}
