<?php

namespace App\Enums;

enum ProductCategory {
    case Bisuteria;
    case Lana;
    case HamaBeads;
    case Personalizaciones;

    /**
     * Returns a string value to be displayed.
     * 
     * @return string A string value of the enum to be displayed.
     */
    public function getDisplayValue() {
        switch ($this->name) {
            case ProductCategory::Bisuteria:
                $displayValue = "Bisutería";
                break;

            case ProductCategory::Lana:
                $displayValue = $this->name;
                break;

            case ProductCategory::HamaBeads:
                $displayValue = "Hama - Beads";
                break;

            case ProductCategory::Personalizaciones:
                $displayValue = $this->name;
                break;
        }

        return $displayValue;
    }
}
