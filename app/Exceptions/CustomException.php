<?php

namespace App\Exceptions;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class CustomException extends Exception {
    
    public function render(): JsonResponse {
        return Controller::createJsonResponse($this->toArray(), $this->code);
    }

    public function toArray() {
        return ['error' => $this->message];
    }
}