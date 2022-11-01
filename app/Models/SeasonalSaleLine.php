<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonalSaleLine extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The Item associated with a SeasonalSaleLine,
     * this variable is meant to be used to parse 
     * SeasonalSaleLine data alongside its Item to json
     * when a SeasonalSaleLine resource is requested to the API.
     * 
     * @var Item
     */
    private Item $item;

    public function getItem() {
        return $this->item;
    }

    public function setItem(Item $item) {
        $this->item = $item;
    }
}
