<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class PrintPriceDtf extends Model
{
    protected $table = 'print_prices_dtf';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'min_qty',
        'max_qty',
        'markup_pct',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];
}
