<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use DateTime;
use Exception;

use App\Exceptions\NotFoundException;
use App\Exceptions\InvalidUpdateException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UpdateConflictException;

class SeasonalSale extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createSale(array $saleData): SeasonalSale {
        DB::beginTransaction();

        if (isset($saleData['lines'])) {
            $saleLinesData = $saleData['lines'];
            unset($saleData['lines']);
        }

        self::validateSaleDataOnCreate($saleData);
        $sale = SeasonalSale::create($saleData);

        if (isset($saleLinesData))
            $sale->lines = SeasonalSaleLine::createSeasonalSaleLinesFromArray($saleLinesData, $sale->id);

        DB::commit();

        return $sale;
    }

    public static function validateSaleDataOnCreate(array $saleData): void {
        self::validateRequiredDataIsSetOnCreate($saleData);
        self::validateDates($saleData);
    }

    private static function validateRequiredDataIsSetOnCreate(array $saleData): void {
        if (!isset($saleData['slogan']) ||
            !isset($saleData['validFromDateTime']) ||
            !isset($saleData['validToDateTime'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateDates(array $saleData): void {
        $validFromDateTime = new DateTime($saleData['validFromDateTime']);
        $validToDateTime = new DateTime($saleData['validToDateTime']);

        if ($validToDateTime <= $validFromDateTime) {
            throw new InvalidUpdateException();
        }
    }

    public static function updateSale(array $saleData, SeasonalSale $sale): SeasonalSale {
        DB::beginTransaction();

        if (isset($saleData['lines'])) {
            $saleLinesData = $saleData['lines'];
            unset($saleData['lines']);
        }

        self::validateSaleDataOnUpdate($saleData, $sale);
        $sale->update($saleData);

        if (isset($saleLinesData)) {
            $sale->lines = SeasonalSaleLine::updateSeasonalSaleLinesFromArray($saleLinesData, $sale->id);
        } else {
            SeasonalSaleLine::deleteSeasonalSaleLinesWhereNotIn([], $sale->id);
            $sale->lines = [];
        }

        DB::commit();

        return $sale;
    }

    public static function validateSaleDataOnUpdate(array $saleData, SeasonalSale $sale): void {
        self::validateRequiredDataIsSetOnUpdate($saleData);
        self::validateUpdateConflict($saleData, $sale);
        self::validateDates($saleData);
    }

    private static function validateRequiredDataIsSetOnUpdate(array $saleData): void {
        if (!isset($saleData['id']) ||
            !isset($saleData['slogan']) ||
            !isset($saleData['validFromDateTime']) ||
            !isset($saleData['validToDateTime']) ||
            !isset($saleData['updated_at'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateUpdateConflict(array $saleData, SeasonalSale $sale): void {
        $currentUpdatedAt = new DateTime($sale['updated_at']);
        $requestUpdatedAt = new DateTime($saleData['updated_at']);

        if ($currentUpdatedAt > $requestUpdatedAt) {
            throw new UpdateConflictException();
        }
    }

    public static function deleteSale(SeasonalSale $sale): void {
        try {
            $sale->delete();
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
        }
    }

    public static function findById(int $id): SeasonalSale {
        $seasonalSale = DB::table('seasonal_sales')->find($id);

        return SeasonalSale::hydrate([$seasonalSale])[0];
    }

    public static function findByIdOrFail(int $id): SeasonalSale {
        $seasonalSale = DB::table('seasonal_sales')->find($id);

        if (!$seasonalSale)
            throw new NotFoundException();

        return SeasonalSale::hydrate([$seasonalSale])[0];
    }

    public static function findAllSales(): array {
        $sales = DB::table('seasonal_sales')->get();

        return SeasonalSale::hydrate($sales->toArray())->all();
    }

    public static function appendLinesToSeasonalSalesArray(array $sales): array {
        foreach ($sales as $sale) {
            $sale->appendSaleLines();
        }

        return $sales;
    }

    public function appendSaleLines(): void {
        $saleLines = SeasonalSaleLine::findSaleLinesBySeasonalSaleId($this->id);
        $saleLines = SeasonalSaleLine::appendItemsToSeasonalSaleLinesArray($saleLines);
        $this->lines = $saleLines;
    }
}
