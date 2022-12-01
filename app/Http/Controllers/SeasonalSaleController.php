<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use Exception;

use App\Models\SeasonalSale;
use App\Exceptions\CustomException;
use App\Exceptions\UnexpectedErrorException;

class SeasonalSaleController extends Controller
{
    public function getById(int $id): JsonResponse {
        try {
            return $this->getSaleById($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getSaleById(int $id): JsonResponse {
        $sale = SeasonalSale::findByIdOrFail($id);
        $sale->appendSaleLines();

        return parent::createJsonResponse($sale, Response::HTTP_OK);
    }

    public function getAll(): JsonResponse {
        try {
            return $this->getAllSales();
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getAllSales(): JsonResponse {
        $sales = SeasonalSale::findAllSales();
        $sales = SeasonalSale::appendLinesToSeasonalSalesArray($sales);

        return parent::createJsonResponse($sales, Response::HTTP_OK);
    }

    public function create(Request $request): JsonResponse {
        try {
            return $this->createSale($request);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function createSale(Request $request): JsonResponse {
        $requestData = $request->all();
        $sale = SeasonalSale::createSale($requestData);

        return parent::createJsonResponse($sale, Response::HTTP_OK);
    }

    public function update(Request $request, SeasonalSale $sale): JsonResponse {
        try {
            return $this->updateSale($request, $sale);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function updateSale(Request $request, SeasonalSale $sale): JsonResponse {
        $requestData = $request->all();
        $sale = SeasonalSale::updateSale($requestData, $sale);

        return parent::createJsonResponse($sale, Response::HTTP_OK);
    }

    public function delete(SeasonalSale $sale): JsonResponse {
        try {
            return $this->deleteSale($sale);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function deleteSale(SeasonalSale $sale): JsonResponse {
        SeasonalSale::deleteSale($sale);

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
