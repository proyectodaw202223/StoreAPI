<?php

namespace App\Models;

use App\Exceptions\InvalidUpdateException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use DateTime;
use Exception;

use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UnexpectedErrorException;
use App\Exceptions\UpdateConflictException;

class SeasonalSaleLine extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createSeasonalSaleLinesFromArray(array $saleLinesData, int $saleId): array {
        $createdSaleLines = [];
        $saleItems = [];
        
        DB::beginTransaction();

        foreach ($saleLinesData as $lineData) {
            if (array_search($lineData['itemId'], $saleItems) !== false)
                throw new ResourceAlreadyExistsException();
            
            $lineData['seasonalSaleId'] = $saleId;
            $saleLine = self::createSaleLine($lineData);

            array_push($createdSaleLines, $saleLine);
            array_push($saleItems, $saleLine->itemId);
        }

        DB::commit();

        return $createdSaleLines;
    }

    public static function createSaleLine(array $saleLineData): SeasonalSaleLine {
        $saleLineData = self::unsetItemFromSeasonalSaleLinesData($saleLineData);
        self::validateSaleLineDataOnCreate($saleLineData);
        $saleLine = SeasonalSaleLine::create($saleLineData);

        return $saleLine;
    }

    private static function unsetItemFromSeasonalSaleLinesData(array $saleLineData): array {
        if (isset($saleLineData['productItem']))
            unset($saleLineData['productItem']);

        return $saleLineData;
    }

    public static function validateSaleLineDataOnCreate(array $saleLineData): void {
        self::validateRequiredDataIsSetOnCreate($saleLineData);
    }

    private static function validateRequiredDataIsSetOnCreate(array $saleLineData): void {
        if (!isset($saleLineData['seasonalSaleId']) ||
            !isset($saleLineData['itemId']) ||
            !isset($saleLineData['discountPercentage'])) {
            throw new InvalidUpdateException();
        }
    }

    public static function updateSeasonalSaleLinesFromArray(array $saleLinesData, int $saleId): array {
        $updatedSaleLines = [];
        $saleItems = [];
        
        DB::beginTransaction();

        foreach ($saleLinesData as $lineData) {
            if (array_search($lineData['itemId'], $saleItems) !== false)
                throw new ResourceAlreadyExistsException();
            
            if (isset($lineData['id']))
                $saleLine = self::updateSaleLine($lineData);
            else
                $saleLine = self::createSaleLine($lineData);

            array_push($updatedSaleLines, $saleLine);
            array_push($saleItems, $saleLine->itemId);
        }

        self::deleteSeasonalSaleLinesWhereNotIn($updatedSaleLines, $saleId);

        DB::commit();

        return $updatedSaleLines;
    }

    public static function updateSaleLine(array $saleLineData): SeasonalSaleLine {
        $saleLine = self::findById($saleLineData['id']);

        $saleLineData = self::unsetItemFromSeasonalSaleLinesData($saleLineData);
        self::validateSaleLineDataOnUpdate($saleLineData, $saleLine);
        $saleLine->update($saleLineData);

        return $saleLine;
    }

    public static function validateSaleLineDataOnUpdate(
        array $saleLineData, SeasonalSaleLine $saleLine): void {
        self::validateRequiredDataIsSetOnUpdate($saleLineData);
        self::validateUpdateConflict($saleLineData, $saleLine);
    }

    private static function validateRequiredDataIsSetOnUpdate(array $saleLineData): void {
        if (!isset($saleLineData['id']) ||
            !isset($saleLineData['seasonalSaleId']) ||
            !isset($saleLineData['itemId']) ||
            !isset($saleLineData['discountPercentage']) ||
            !isset($saleLineData['updated_at'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateUpdateConflict(
        array $saleLineData, SeasonalSaleLine $saleLine): void {
        $currentUpdatedAt = new DateTime($saleLine['updated_at']);
        $requestUpdatedAt = new DateTime($saleLineData['updated_at']);

        if ($currentUpdatedAt > $requestUpdatedAt) {
            throw new UpdateConflictException();
        }

    }

    public static function findById(int $id): SeasonalSaleLine {
        $saleLine = DB::table('seasonal_sale_lines')->find($id);

        return SeasonalSaleLine::hydrate([$saleLine])[0];
    }

    public static function deleteSeasonalSaleLinesWhereNotIn(array $saleLines, int $saleId): void {
        $lineIds = [];

        foreach ($saleLines as $line)
            array_push($lineIds, $line->id);

        try {
            DB::table('seasonal_sale_lines')
                ->where('seasonalSaleId', '=', $saleId)
                ->whereNotIn('id', $lineIds)
                ->delete();
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
        }
    }

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

        $seasonalSaleLine = SeasonalSaleLine::hydrate([$seasonalSaleLine[0]])[0];

        return $seasonalSaleLine;
    }

    public static function findSaleLinesBySeasonalSaleId(int $saleId): array {
        $saleLines = DB::table('seasonal_sale_lines')
            ->where('seasonalSaleId', '=', $saleId)
            ->get();

        return SeasonalSaleLine::hydrate($saleLines->toArray())->all();
    }

    public static function existsSaleLineByItemId(int $itemId): bool {
        $saleLine = DB::table('seasonal_sale_lines')
            ->where('itemId', '=', $itemId)
            ->first();

        return ($saleLine) ? true : false;
    }

    public static function appendItemsToSeasonalSaleLinesArray(array $saleLines): array {
        foreach ($saleLines as $line) {
            $line->appendItem();
        }

        return $saleLines;
    }

    public function appendItem(): void {
        $item = ProductItem::findById($this->itemId);
        $item->appendProduct();
        $this->productItem = $item;
    }
}
