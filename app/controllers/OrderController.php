<?php

require_once './interfaces/IApiUse.php';
require_once './models/Order.php';
require_once './services/OrderService.php';
require_once './enums/ProductType.php';
require_once './enums/OrderStatus.php';

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
        $userType = AuthMiddleware::getUserType($request);

        switch ($userType) {
            case UserType::COOKER->getStringValue():
                $order = OrderService::getOneOrderByStatus($id, ProductType::FOOD->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            case UserType::BREWER->getStringValue():
                $order = OrderService::getOneOrderByStatus($id, ProductType::BEER->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            case UserType::BARTENDER->getStringValue():
                $order = OrderService::getOneOrderByStatus($id, ProductType::DRINK->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            default:
                $order = OrderService::getOne($id);
                break;
        }

        if($order != false)
        {
            $payload = json_encode($order);
        } else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Orden de su sector o existente"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function GetAll($request, $response, $args)
    {
        $userType = AuthMiddleware::getUserType($request);
        $orderList = [];

        switch ($userType) {
            case UserType::COOKER->getStringValue():
                $orderList = OrderService::getOrdersByStatus(ProductType::FOOD->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            case UserType::BREWER->getStringValue():
                $orderList = OrderService::getOrdersByStatus(ProductType::BEER->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            case UserType::BARTENDER->getStringValue():
                $orderList = OrderService::getOrdersByStatus(ProductType::DRINK->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            case UserType::WAITER->getStringValue():
                $orderList = OrderService::getOrdersByStatus(null ,OrderStatus::READY->getStringValue());
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

    public static function PrepareOrder($request, $response, $args)
    {
        $id = $args['id'];
        $userType = AuthMiddleware::getUserType($request);

        switch ($userType) {
            case UserType::COOKER->getStringValue():
                $orderAux = OrderService::getOneOrderByStatus($id, ProductType::FOOD->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            case UserType::BREWER->getStringValue():
                $orderAux = OrderService::getOneOrderByStatus($id, ProductType::BEER->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            case UserType::BARTENDER->getStringValue():
                $orderAux = OrderService::getOneOrderByStatus($id, ProductType::DRINK->getStringValue(),OrderStatus::PENDING->getStringValue());
                break;

            default:
                $orderAux = OrderService::getOne($id);
                break;
        }

        if($orderAux != false)
        {
            $parameters = $request->getParsedBody();
            $updated = false;

            if(isset($parameters['preparationTime']))
            {
                $updated = true;
                $orderAux->preparationTime = $parameters['preparationTime'];
            }

            if ($updated) {
                $orderAux->status = OrderStatus::PREPARATION->getStringValue();
                OrderService::update($orderAux);
                $payload = json_encode(array("mensaje" => "Orden en preparación."));
            } else {
                $payload = json_encode(array("mensaje" => "La Orden no se puede modificar por falta de campos"));
            }
        }else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Orden de su sector o existente"));
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function CompletedOrder($request, $response, $args)
    {
        $id = $args['id'];
        $userType = AuthMiddleware::getUserType($request);
        $payload = json_encode(array("mensaje" => "Orden en terminada."));

        switch ($userType) {
            case UserType::COOKER->getStringValue():
                $orderAux = OrderService::getOneOrderByStatus($id, ProductType::FOOD->getStringValue(),OrderStatus::PREPARATION->getStringValue());
                $orderAux->status = OrderStatus::READY->getStringValue();
                break;

            case UserType::BREWER->getStringValue():
                $orderAux = OrderService::getOneOrderByStatus($id, ProductType::BEER->getStringValue(),OrderStatus::PREPARATION->getStringValue());
                $orderAux->status = OrderStatus::READY->getStringValue();
                break;

            case UserType::BARTENDER->getStringValue():
                $orderAux = OrderService::getOneOrderByStatus($id, ProductType::DRINK->getStringValue(),OrderStatus::PREPARATION->getStringValue());
                $orderAux->status = OrderStatus::READY->getStringValue();
                break;

            case UserType::WAITER->getStringValue():
                $orderAux = OrderService::getOneOrderByStatus($id, null,OrderStatus::READY->getStringValue());

                if ($orderAux != false) {
                    $tableId = $orderAux->tableID;
                    $orderId = $orderAux->orderID;
                    $table = TableService::getOne($tableId);
                    $orderAux->status = OrderStatus::DELIVERED->getStringValue();
                    OrderService::update($orderAux);
                    $allDelivered = OrderService::verifiedOrderByTable($tableId, $orderId, OrderStatus::DELIVERED->getStringValue());

                    if ($allDelivered) {
                        $table->status = TableStatus::SERVED->getStringValue();
                        TableService::update($table);

                        $payload = json_encode(array("mensaje" => "Todos los pedidos entregados. Estado de la mesa cambiado a comiendo."));
                    } else {
                        $payload = json_encode(array("mensaje" => "Orden entregada, pero algunos pedidos de la mesa aún no están listos."));
                    }
                } else {
                    $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Orden de su sector o existente"));
                }
                break;


            default:
                $orderAux = OrderService::getOne($id);
                break;
        }

        if($orderAux != false)
        {
            OrderService::update($orderAux);
        }else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Orden de su sector o existente"));
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function GetTableOrders($request, $response, $args)
    {

        $tableId = $args['mesaId'];
        $orderId = $args['orderId'];
        $order = OrderService::getOrdersByTable($tableId, $orderId);


        if($order != false)
        {
            $payload = json_encode($order);
        } else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Orden de su sector o existente"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}