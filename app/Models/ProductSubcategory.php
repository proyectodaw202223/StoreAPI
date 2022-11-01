<?php

namespace App\Models;

enum ProductSubcategory {
    case Colgantes;
    case Pendientes;
    case Pulseras;
    case Patucos;
    case Gorros;
    case Posavasos;
    case MarcosFotos;
    case Carteras;
    case Llaveros;

    /**
     * Returns a string value to be displayed.
     * 
     * @return string A string value of the enum to be displayed.
     */
    public function getDisplayValue() {
        switch ($this->name) {
            case ProductSubcategory::Colgantes:
                $displayValue = $this->name;
                break;

            case ProductSubcategory::Pendientes:
                $displayValue = $this->name;
                break;

            case ProductSubcategory::Pulseras:
                $displayValue = $this->name;
                break;
                
            case ProductSubcategory::Patucos:
                $displayValue = $this->name;
                break;
                
            case ProductSubcategory::Gorros:
                $displayValue = $this->name;
                break;
                
            case ProductSubcategory::Posavasos:
                $displayValue = $this->name;
                break;
                
            case ProductSubcategory::MarcosFotos:
                $displayValue = $this->name;
                break;
                
            case ProductSubcategory::Carteras:
                $displayValue = $this->name;
                break;
                
            case ProductSubcategory::Llaveros:
                $displayValue = $this->name;
                break;
        }
        
        return $displayValue;
    }
}
