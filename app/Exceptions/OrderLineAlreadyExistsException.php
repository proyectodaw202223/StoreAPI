<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use App\Models\OrderLine;

class OrderLineAlreadyExistsException extends CustomException {

    private function __construct(string $message, int $code) {
        $this->message = $message;
        $this->code = $code;
    }

    public static function makeNewFromOrderLine(OrderLine $existingOrderLine) {
        return new OrderLineAlreadyExistsException(
            "The order line with the item ".$existingOrderLine->itemId." already exists.",
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function makeNewFromArray(array $existingOrderLine) {
        return new OrderLineAlreadyExistsException(
            "The order line with the item ".$existingOrderLine['itemId']." already exists.",
            Response::HTTP_BAD_REQUEST
        );
    }
}
