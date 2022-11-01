<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const HTTP_OK_CODE = 200;
    const HTTP_CREATED_CODE = 201;
    const HTTP_NO_CONTENT_CODE = 204;
    const HTTP_NOT_FOUND_CODE = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * Returns a JSON response with the given object and http status code.
     * 
     * @param object $object The given object with which the response will be created.
     * @param int $httpStatusCode The given http status code of the response, OK (200) by default.
     * @return Illuminate\Http\Response A response with the given object and http status code.
     */
    protected function createResponse($object, $httpStatusCode = self::HTTP_OK_CODE) {
        return response()->json($object, $httpStatusCode);
    }
}
