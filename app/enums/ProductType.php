<?php
enum ProductType: string
{
    case FOOD = 'comida';
    case BEER = 'cerveza';
    case DRINK = 'bebida';
    case DESSERT = 'postre';

    public function getStringValue(): string
    {
        return $this->value;
    }
}
