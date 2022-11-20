<?php

namespace App\Models;

use App\Exceptions\UnexpectedErrorException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeasonalSaleLine extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function findByItemIdAndDateTime(int $itemId, string $dateTime): SeasonalSaleLine|null {
        $seasonalSaleLine = DB::select(
            DB::raw(
                "SELECT * FROM seasonal_sale_lines WHERE ".
                    "itemId = ? AND ".
                    "seasonalSaleId IN ( ".
                        "SELECT id FROM seasonal_sales WHERE ".
                            "validFromDateTime <= ? AND ".
                            "validToDateTime >= ? AND ".
                            "isCanceled = ? ".
                        ")"
            ),
            [$itemId, $dateTime, $dateTime, 0]
        );

        if (count($seasonalSaleLine) == 0)
            return null;

        if (count($seasonalSaleLine) > 1)
            throw new UnexpectedErrorException();

        $seasonalSaleLine = SeasonalSaleLine::hydrate($seasonalSaleLine)[0];

        return $seasonalSaleLine;
    }
}
