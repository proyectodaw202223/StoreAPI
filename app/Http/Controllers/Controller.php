<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Returns a JSON response with the given object and http status code.
     * 
     * @param object $object The given object with which the response will be created.
     * @param int $httpStatusCode The given http status code of the response, OK (200) by default.
     * @return JsonResponse A response with the given object and http status code.
     */
    public static function createJsonResponse(
        $object, $httpStatusCode = Response::HTTP_OK): JsonResponse {
        return response()->json($object, $httpStatusCode);
    }
}
