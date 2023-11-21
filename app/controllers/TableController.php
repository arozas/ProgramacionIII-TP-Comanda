<?php
require_once './interfaces/IApiUse.php';
require_once './models/Table.php';
require_once './services/TableService.php';
class TableController implements IApiUse
{
    public static function Add($request, $response, $args)
    {
        $dummyObject = null;
        TableService::create($dummyObject);
        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Get($request, $response, $args)
    {
        $id = $args['id'];
        $table = TableService::getOne($id);
        $payload = json_encode($table);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function GetAll($request, $response, $args)
    {
        $tableList = TableService::getAll();
        $payload = json_encode(array("listaMesas" => $tableList));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Delete($request, $response, $args)
    {
        $id = $args['id'];
        if (TableService::getOne($id)) {
            TableService::delete($id);
            $payload = json_encode(array("mensaje" => "mesa borrada con exito"));
        } else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Mesa"));
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ManageBill($request, $response, $args)
    {
        $orderID = $args['orderId'];
        $table = TableService::getTableByOrderId($orderID);

        if ($table) {
            $bill = TableService::getBill($orderID);

            if ($table->status == TableStatus::PAYING->getStringValue() || $table->status == TableStatus::PAYED->getStringValue()) {
                if ($table->status == TableStatus::PAYED) {
                    $payload = json_encode(array("mensaje" => "La mesa ya ha sido pagada y no se puede cobrar nuevamente."));
                } else {
                    $allDelivered = OrderService::verifiedOrderByTable($table->id, $orderID, OrderStatus::DELIVERED->getStringValue());

                    if ($allDelivered) {
                        $statusMessage = "";
                        if ($table->status == TableStatus::PAYING->getStringValue()) {
                            $table->status = TableStatus::PAYED->getStringValue();
                            $orders = OrderService::getOrdersByTable($table->id, $orderID);
                            foreach ($orders as $order)
                            {
                                $order->status = OrderStatus::PAYED->getStringValue();
                                OrderService::update($order);
                            }
                            $statusMessage = "pagó";
                        } else {
                            $table->status = TableStatus::PAYING->getStringValue();
                            $statusMessage = "tiene una cuenta de";
                        }

                        TableService::update($table);
                        $payload = json_encode(array("mensaje" => "La mesa " . $bill[0]['tableID'] . " " . $statusMessage . ": $" . $bill[0]['Total']));
                    } else {
                        $payload = json_encode(array("mensaje" => "La Mesa no tiene todos los pedidos entregados o ya se encuentra pagada, no se puede completar la operación."));
                    }
                }
            } else {
                $payload = json_encode(array("mensaje" => "La Mesa no está en proceso de pago o ya ha sido pagada."));
            }
        } else {
            $payload = json_encode(array("mensaje" => "El código de pedido no coincide con ninguna mesa."));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Update($request, $response, $args)
    {
        $id = $args['id'];

        $table = TableService::getOne($id);

        if ($table != false) {
            $parameters = $request->getParsedBody();

            $updated = false;
            if (isset($parameters['estado'])) {

                $updated = true;
                $table->status = $parameters['estado'];
            }

            if ($updated) {
                TableService::update($table);
                $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "Mesa no modificada por falta de campos"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Mesa"));
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

}