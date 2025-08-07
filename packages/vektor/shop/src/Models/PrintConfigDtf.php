<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class PrintConfigDtf extends Model
{
    protected $table = 'print_config_dtf';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'small_print_bound',
        'large_print_bound',
        'print_cost',
        'markup_pct',
        'single_print_prices',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'print_cost' => 'float',
        'single_print_prices' => 'array',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];
}
