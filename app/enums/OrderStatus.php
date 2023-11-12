<?php
enum OrderStatus: string
{
    case PENDING = 'Pendiente';
    case PAYED = 'Pagado';
    case PREPARACION = 'En preparacion';
    case READY = 'Listo para servir';
    case DELIVERED = 'Entregado';

    public function getStringValue(): string
    {
        return $this->value;
    }
}
