<?php

class Survey
{
    public $id;
    public $orderID;
    public $tableID;
    public $restoRating;
    public $tableRating;
    public $waiterRating;
    public $cookerRating;
    public $comments;
    public $date;

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