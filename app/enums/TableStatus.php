<?php
enum TableStatus: string
{
    case WAITING = 'Esperando Pedido';
    case SERVED = 'Comiendo';
    case PAYING = 'Pagando';
    case CLOSE = 'Cerrada';
    case DOWN = 'Baja';

    public function getStringValue(): string
    {
        return $this->value;
    }
}
