<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class OrderLineAmountMismatchException extends CustomException {

    function __construct($orderLineAmount, $itemPrices) {
        $this->message = sprintf(
            "The amount of the order line %01.2f must be equal to the sum of the item prices %01.2f.",
            $orderLineAmount,
            $itemPrices
        );
        
        $this->code = Response::HTTP_NOT_FOUND;
    }
}
