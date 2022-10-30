<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonalSale extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * An array of SeasonalSaleLines associated with a SeasonalSale,
     * this variable is meant to be used to parse 
     * SeasonalSale data alongside its SeasonalSaleLines to json 
     * when a SeasonalSale resource is requested to the API.
     * 
     * @var Array
     */
    private Array $seasonalSaleLines;

    public function getSeasonalSaleLines() {
        return $this->seasonalSaleLines;
    }

    public function setSeasonalSaleLines(Array $seasonalSaleLines) {
        $this->seasonalSaleLines = $seasonalSaleLines;
    }
}
