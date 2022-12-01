<?php

namespace App\Enums;

enum OrderStatus: string {
    case CREATED = "Creado";
    case PAID = "Pagado";
    case IN_MANAGEMENT = "En Gestión";
    case SENT = "Enviado";
    case CANCELED = "Cancelado";
}
