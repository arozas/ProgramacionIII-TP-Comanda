<?php

class UserDTO
{
    public $id;
    public $user;
    public $password;
    public $userType;

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