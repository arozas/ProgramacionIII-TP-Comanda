<?php

class OrderDTO
{
    public $id;
    public $orderID;
    public $tableID;
    public $productID;
    public $clientName;
    public $tableImage;
    public $status;
    public $orderTime;
    public $preparationTime;
    public $servedTime;

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