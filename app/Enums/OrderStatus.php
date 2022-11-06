<?php

namespace App\Enums;

enum OrderStatus: string {
    case Creado = "Creado";
    case Pagado = "Pagado";
    case Gestion = "Gestion";
    case Enviado = "Enviado";
    case Cancelado = "Cancelado";

    /**
     * Returns a string value to be displayed.
     * 
     * @return string A string value of the enum to be displayed.
     */
    public function getDisplayValue() {
        switch ($this->name) {
            case OrderStatus::Creado:
                $displayValue = $this->name;
                break;

            case OrderStatus::Pagado:
                $displayValue = $this->name;
                break;

            case OrderStatus::Gestion:
                $displayValue = "En Gestión";
                break;

            case OrderStatus::Enviado:
                $displayValue = $this->name;
                break;

            case OrderStatus::Cancelado:
                $displayValue = $this->name;
                break;
        }

        return $displayValue;
    }
}
