<?php

require_once './interfaces/IApiUse.php';
require_once './models/Order.php';
require_once './services/OrderService.php';

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

        $table =TableService::getOne($tableId);

        if($table != false)
        {
            if ($table->status == TableStatus::CLOSE->getStringValue())
            {
                $order->orderID = TableService::generateCode(5);
                $table->status = TableStatus::WAITING->getStringValue();
                TableService::update($table);
            } else {
                $order->orderID = OrderService::getLastId($tableId);
            }

            OrderService::create($order);

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
        $order = OrderService::getOne($id);
        $payload = json_encode($order);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function GetAll($request, $response, $args)
    {
        $userType = AuthMiddleware::getUserTypeFromToken($request);
        $orderList = [];

        switch ($userType) {
            case UserType::COOKER->getStringValue():
                $orderList = OrderService::getFoodOrders();
                break;

            case UserType::BREWER->getStringValue():
                $orderList = OrderService::getBeerOrders();
                break;

            case UserType::BARTENDER->getStringValue():
                $orderList = OrderService::getDrinkOrders();
                break;

            default:
                $orderList = OrderService::getAll();
                break;
        }

        $payload = json_encode(array("listaPedidos" => $orderList));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function Delete($request, $response, $args)
    {
        $parameters = $request->getParsedBody();

        $orderId = $parameters['pedidoId'];
        OrderService::delete($orderId);

        $payload = json_encode(array("mensaje" => "pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');    }

    public static function Update($request, $response, $args)
    {

        $id = $args['id'];

        $orderAux = OrderService::getOne($id);

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
                OrderService::update($orderAux);
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