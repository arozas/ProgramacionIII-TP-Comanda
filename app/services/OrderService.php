<?php

require_once './models/dto/OrderDTO.php';
require_once './enums/OrderStatus.php';

class OrderService implements IPersistance
{
    public static function create($order)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("INSERT INTO orders (orderID, tableImage, tableID, productID, clientName, status, orderTime, modifiedDate, active) 
                                             VALUES (:orderID, :tableImage, :tableID, :productID, :clientName, :status, :orderTime, :modifiedDate, true)");
        $request->bindValue(':orderID', $order->orderID, PDO::PARAM_STR);
        $request->bindValue(':tableImage', $order->tableImage, PDO::PARAM_STR);
        $request->bindValue(':tableID', $order->tableID, PDO::PARAM_INT);
        $request->bindValue(':productID', $order->productID, PDO::PARAM_INT);
        $request->bindValue(':clientName', $order->clientName, PDO::PARAM_STR);
        $request->bindValue(':status', OrderStatus::PENDING->getStringValue(), PDO::PARAM_STR);
        $date = new DateTime(date("d-m-Y"));
        $time = (new DateTime())->format('H:i:s');
        $request->bindValue(':orderTime', $time, PDO::PARAM_STR);
        $request->bindValue(':modifiedDate', date_format($date, 'Y-m-d H:i:s'));
        $request->execute();
        return $DAO->getLastId();
    }

    public static function getAll()
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT id,
                                                    orderID,
                                                    tableImage,
                                                    tableID,
                                                    productID,
                                                    clientName,
                                                    status,
                                                    orderTime,
                                                    preparationTime,
                                                    servedTime
                                                                 FROM orders
                                                                 WHERE active = true");
        $request->execute();
        return $request->fetchAll(PDO::FETCH_CLASS, 'OrderDTO');
    }

    public static function getOne($id)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT id, orderID, tableImage, tableID, productID, clientName, status, orderTime, preparationTime, servedTime FROM orders WHERE id = :id AND active = true");
        $request->bindValue(':id', $id, PDO::PARAM_INT);
        $request->execute();
        return $request->fetchObject('OrderDTO');
    }

    public static function update($order)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("UPDATE orders 
                                             SET orderID = :orderID,
                                                 tableImage = :tableImage,
                                                 tableID = :tableID,
                                                 productID = :productID,
                                                 clientName = :clientName,
                                                 status = :status,
                                                 orderTime = :orderTime,
                                                 preparationTime = :preparationTime,
                                                 servedTime = :servedTime,
                                                 modifiedDate = :modifiedDate
                                             WHERE id = :id AND active = true");
        $request->bindValue(':id', $order->id, PDO::PARAM_INT);
        $request->bindValue(':orderID', $order->orderID, PDO::PARAM_STR);
        $request->bindValue(':tableImage', $order->tableImage, PDO::PARAM_STR);
        $request->bindValue(':tableID', $order->tableID, PDO::PARAM_STR);
        $request->bindValue(':productID', $order->productID, PDO::PARAM_STR);
        $request->bindValue(':clientName', $order->clientName, PDO::PARAM_STR);
        $request->bindValue(':status', $order->status, PDO::PARAM_STR);
        $request->bindValue(':orderTime', $order->orderTime, PDO::PARAM_STR);
        $request->bindValue(':preparationTime', $order->preparationTime, PDO::PARAM_STR);
        $request->bindValue(':servedTime', $order->servedTime, PDO::PARAM_STR);
        $date = new DateTime(date("d-m-Y"));
        $request->bindValue(':modifiedDate', date_format($date, 'Y-m-d H:i:s'));
        $request->execute();
    }

    public static function delete($id)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("UPDATE orders 
                                             SET modifiedDate = :modifiedDate, 
                                                 active = false 
                                             WHERE id = :id");
        $date = new DateTime(date("d-m-Y"));
        $request->bindValue(':id', $id, PDO::PARAM_STR);
        $request->bindValue(':modifiedDate', date_format($date, 'Y-m-d H:i:s'));
        $request->execute();
    }

    public static function getLastId($tableId)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT orderID FROM orders WHERE tableId = :tableID");
        $request->bindValue(':tableID', $tableId, PDO::PARAM_STR);
        $request->execute();

        return $request->fetchColumn();
    }

    public static function getOrdersByStatus($productType, $status)
    {
        $DAO = DataAccessObject::getInstance();
        if($productType == null)
        {
            $request = $DAO->prepareRequest("SELECT * FROM orders WHERE status = :status AND active = true");
            $request->bindValue(':status', $status, PDO::PARAM_STR);
        }else{
            $request = $DAO->prepareRequest("SELECT * FROM orders WHERE productID IN (SELECT id FROM products WHERE productType = :productType) AND status = :status AND active = true");
            $request->bindValue(':productType', $productType, PDO::PARAM_STR);
            $request->bindValue(':status', $status, PDO::PARAM_STR);
        }
        $request->execute();

        return $request->fetchAll(PDO::FETCH_CLASS, 'Order');
    }

    public static function verifiedOrderByTable($tableId, $orderId, $status)
    {
        $orders = self::getOrdersByTable($tableId, $orderId);

        if (empty($orders)) {
            return false;
        }

        foreach ($orders as $order) {
            if ($order->status != $status) {
                return false;
            }
        }

        return true;
    }

    public static function getOrdersByTable($tableID, $orderId)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT * FROM orders WHERE orderID = :orderId AND tableID = :tableId AND active = true");
        $request->bindValue(':tableId', $tableID, PDO::PARAM_STR);
        $request->bindValue(':orderId', $orderId, PDO::PARAM_STR);
        $request->execute();

        return $request->fetchAll(PDO::FETCH_CLASS, 'Order');
    }

    public static function getOneOrderByStatus($id, $productType, $status)
    {
        $DAO = DataAccessObject::getInstance();

        if($productType == null)
        {
            $request = $DAO->prepareRequest("SELECT id, orderID, tableImage, tableID, productID, clientName, status, orderTime, preparationTime, servedTime FROM orders WHERE status = :status AND id = :id AND Active = true");
            $request->bindValue(':id', $id, PDO::PARAM_INT);
            $request->bindValue(':status', $status, PDO::PARAM_STR);
        }else{
            $request = $DAO->prepareRequest("SELECT id, orderID, tableImage, tableID, productID, clientName, status, orderTime, preparationTime, servedTime FROM orders WHERE productID IN (SELECT id FROM products WHERE productType = :productType) AND status = :status AND id = :id AND Active = true");
            $request->bindValue(':id', $id, PDO::PARAM_INT);
            $request->bindValue(':productType', $productType, PDO::PARAM_STR);
            $request->bindValue(':status', $status, PDO::PARAM_STR);
        }
        $request->execute();
        return $request->fetchObject('OrderDTO');
    }
}