<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use Exception;

use App\Exceptions\CustomException;
use App\Exceptions\UnexpectedErrorException;
use App\Models\ProductItemImage;

class ProductItemImageController extends Controller
{
    public function getAll(): JsonResponse{
        try {
            return $this->getAllImages();
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    public function getAllImages(): JsonResponse{
        $images = ProductItemImage::findAllImages();
        
        return parent::createJsonResponse($images, Response::HTTP_OK);
    }

    public function create(Request $request): JsonResponse {
        try {
            return $this->createImage($request);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    public function createImage(Request $request): JsonResponse {
        $this->validate($request, [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        $image = ProductItemImage::createImage($request);

        return parent::createJsonResponse($image, Response::HTTP_CREATED);
    }

    public function delete(int $itemImageId): JsonResponse {
        try {
            return $this->deleteImage($itemImageId);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    public function deleteImage(int $itemImageId): JsonResponse {
        $image = ProductItemImage::findByIdOrFail($itemImageId);
        ProductItemImage::deleteImage($image);

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
