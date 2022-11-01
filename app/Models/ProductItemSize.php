<?php

namespace App\Models;

enum ProductItemSize {
    case S;
    case M;
    case L;

    /**
     * Returns a string value to be displayed.
     * 
     * @return string A string value of the enum to be displayed.
     */
    public function getDisplayValue() {
        switch ($this->name) {
            case ProductItemSize::S:
                $displayValue = $this->name;
                break;

            case ProductItemSize::M:
                $displayValue = $this->name;
                break;
                
            case ProductItemSize::L:
                $displayValue = $this->name;
                break;
        }

        return $displayValue;
    }
}
