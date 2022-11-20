<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeasonalSale extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function findById(int $id): SeasonalSale {
        $seasonalSale = DB::table('seasonal_sales')->find($id);

        return SeasonalSale::hydrate([$seasonalSale])[0];
    }
}
