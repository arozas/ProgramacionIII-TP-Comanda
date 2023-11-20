<?php
require_once './models/dto/ProductDto.php';

class Product
{
    public $id;
    public $name;
    public $description;
    public $productType;
    public $price;
    public $stock;
    public $active;
    public $modifiedDate;


    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            return null;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            echo "No existe " . $property;
        }
    }
}