<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use Exception;

use App\Models\Product;
use App\Exceptions\CustomException;
use App\Exceptions\UnexpectedErrorException;

class ProductController extends Controller
{
    public function getById(int $id): JsonResponse {
        try {
            return $this->getProductById($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getProductById(int $id): JsonResponse {
        $product = Product::findByIdOrFail($id);
        $product->appendItems();

        return parent::createJsonResponse($product, Response::HTTP_OK);
    }

    public function getAll(): JsonResponse {
        try {
            return $this->getAllProducts();
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getAllProducts(): JsonResponse {
        $products = Product::findAllProducts();
        $products = Product::appendItemsToProductsArray($products);

        return parent::createJsonResponse($products, Response::HTTP_OK);
    }

    public function getNew(int $limit): JsonResponse {
        try {
            return $this->getNewProducts($limit);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getNewProducts(int $limit): JsonResponse {
        if ($limit == 0) {
            $products = Product::findNewProducts();
        } else {
            $products = Product::findNewProductsLimit($limit);
        }

        $products = Product::appendItemsToProductsArray($products);

        return parent::createJsonResponse($products, Response::HTTP_OK);
    }

    public function getForSale(int $limit): JsonResponse {
        try {
            return $this->getProductsForSale($limit);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getProductsForSale(int $limit): JsonResponse {
        if ($limit == 0) {
            $products = Product::findProductsForSale();
        } else {
            $products = Product::findProductsForSaleLimit($limit);
        }
        
        $products = Product::appendItemsToProductsArray($products);

        return parent::createJsonResponse($products, Response::HTTP_OK);
    }

    public function create(Request $request): JsonResponse {
        try {
            return $this->createProduct($request);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function createProduct(Request $request): JsonResponse {
        $requestData = $request->all();
        $product = Product::createProduct($requestData);

        return parent::createJsonResponse($product, Response::HTTP_OK);
    }

    public function update(Request $request, Product $product): JsonResponse {
        try {
            return $this->updateProduct($request, $product);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function updateProduct(Request $request, Product $product): JsonResponse {
        $requestData = $request->all();
        $product = Product::updateProduct($requestData, $product);

        return parent::createJsonResponse($product, Response::HTTP_OK);
    }

    public function delete(Product $product): JsonResponse {
        try {
            return $this->deleteProduct($product);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function deleteProduct(Product $product): JsonResponse {
        Product::deleteProduct($product);

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
