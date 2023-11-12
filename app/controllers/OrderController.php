<?php

require_once './interfaces/IApiUse.php';
require_once './models/Order.php';

class OrderController implements IApiUse
{

    public static function Add($request, $response, $args)
    {
        $parameters = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $tableId = $parameters['idMesa'];
        $productId = $parameters['idProducto'];
        $clientName = $parameters['nombreCliente'];


        $order = new Order();
        $order->tableID = $tableId;

        $order->productID = $productId;
        $order->clientName = $clientName;
        if (isset($uploadedFiles['fotoMesa'])) {
            $targetPath = './img/' . date_format(new DateTime(), 'Y-m-d_H-i-s') . '_' . $clientName . '_Mesa_' . $tableId . '.jpg';
            $uploadedFiles['fotoMesa']->moveTo($targetPath);
            $order->tableImage = $targetPath;
        }

        $table =Table::getOne($tableId);

        if($table != false)
        {
            if ($table->status == TableStatus::CLOSE->getStringValue())
            {
                $order->orderID = Table::generateCode(5);
                $table->status = TableStatus::WAITING->getStringValue();
                Table::update($table);
            } else {
                $order->orderID = Order::getLastId($tableId);
            }

            Order::create($order);

            $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
        }else{
            $payload = json_encode(array("mensaje" => "Ingrese un código de mesa correcto"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Get($request, $response, $args)
    {
        $id = $args['id'];
        $order = Order::getOne($id);
        $payload = json_encode($order);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function GetAll($request, $response, $args)
    {
        $orderList = Order::getAll();
        $payload = json_encode(array("listaPedidos" => $orderList));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Delete($request, $response, $args)
    {
        $parameters = $request->getParsedBody();

        $orderId = $parameters['pedidoId'];
        Order::delete($orderId);

        $payload = json_encode(array("mensaje" => "pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');    }

    public static function Update($request, $response, $args)
    {

        $id = $args['id'];

        $orderAux = Order::getOne($id);

        if($orderAux != false)
        {
            $parameters = $request->getParsedBody();
            $updated = false;
            if(isset($parameters['productID']))
            {
                $updated = true;
                $orderAux->productID = $parameters['productID'];
            }
            if(isset($parameters['clientName']))
            {
                $updated = true;
                $orderAux->clientName = $parameters['clientName'];
            }
            if(isset($parameters['status']))
            {
                $updated = true;
                $orderAux->status = $parameters['status'];
            }
            if(isset($parameters['preparationTime']))
            {
                $updated = true;
                $orderAux->preparationTime = $parameters['preparationTime'];
            }

            if ($updated) {
                Order::update($orderAux);
                $payload = json_encode(array("mensaje" => "Orden modificada con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "La Orden no se puede modificar por falta de campos"));
            }
        }else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Orden"));
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}