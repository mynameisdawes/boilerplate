<?php

namespace Vektor\Shop\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PrintPriceScr extends Model
{
    protected $table = 'print_prices_scr';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'print_area',
        'min_qty',
        'max_qty',
        'underbase',
        'colour_1',
        'colour_2',
        'colour_3',
        'colour_4',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'underbase' => 'float',
        'colour_1' => 'float',
        'colour_2' => 'float',
        'colour_3' => 'float',
        'colour_4' => 'float',
    ];

    public static function grouped(): Collection
    {
        return self::select('min_qty', 'max_qty', 'underbase', 'colour_1', 'colour_2', 'colour_3', 'colour_4', 'print_area')
            ->get()
            ->makeHidden(['print_area'])
            ->groupBy('print_area')
        ;
    }
}
