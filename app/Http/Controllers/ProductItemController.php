<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use Exception;

use App\Models\ProductItem;
use App\Exceptions\CustomException;
use App\Exceptions\UnexpectedErrorException;

class ProductItemController extends Controller
{
    public function getById(int $id): JsonResponse {
        try {
            return $this->getItemById($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getItemById(int $id): JsonResponse {
        $item = ProductItem::findByIdOrFail($id);

        return parent::createJsonResponse($item, Response::HTTP_OK);
    }

    public function create(Request $request): JsonResponse {
        return $this->createItem($request);
        try {
            return $this->createItem($request);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function createItem(Request $request): JsonResponse {
        $requestData = $request->all();
        $item = ProductItem::createItem($requestData);

        return parent::createJsonResponse($item, Response::HTTP_OK);
    }

    public function update(Request $request, int $itemId): JsonResponse {
        try {
            return $this->updateItem($request, ProductItem::findById($itemId));
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function updateItem(Request $request, ProductItem $item): JsonResponse {
        $requestData = $request->all();
        $item = ProductItem::updateItem($requestData, $item);

        return parent::createJsonResponse($item, Response::HTTP_OK);
    }

    public function delete(int $itemId): JsonResponse {
        try {
            return $this->deleteItem(ProductItem::findById($itemId));
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function deleteItem(ProductItem $item): JsonResponse {
        ProductItem::deleteItem($item);

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
