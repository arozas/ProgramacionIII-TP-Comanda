<?php

class SurveyService implements IPersistance
{

    public static function create($object)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("INSERT INTO surveys (orderID, 
                                                                     tableId, 
                                                                     restoRating, 
                                                                     tableRating, 
                                                                     waiterRating, 
                                                                     cookerRating, 
                                                                     comments, 
                                                                     date) 
                                                        VALUES (:orderID, 
                                                                :tableID, 
                                                                :restoRating, 
                                                                :tableRating, 
                                                                :waiterRating,
                                                                :cookerRating,
                                                                :comments, 
                                                                :date)");
        $request->bindValue(':orderID', $object->orderID, PDO::PARAM_STR);
        $request->bindValue(':tableID', $object->tableID, PDO::PARAM_STR);
        $request->bindValue(':restoRating', $object->restoRating, PDO::PARAM_INT);
        $request->bindValue(':tableRating', $object->tableRating, PDO::PARAM_INT);
        $request->bindValue(':waiterRating', $object->waiterRating, PDO::PARAM_INT);
        $request->bindValue(':cookerRating', $object->cookerRating, PDO::PARAM_INT);
        $request->bindValue(':comments', $object->comments, PDO::PARAM_STR);
        $date = new DateTime(date("d-m-Y"));
        $request->bindValue(':date', date_format($date, 'Y-m-d H:i:s'));

        $request->execute();

        return $DAO->getLastId();
    }

    public static function getAll()
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT * FROM surveys");
        $request->execute();

        return $request->fetchAll(PDO::FETCH_CLASS, 'Survey');
    }

    public static function getOne($value)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT * FROM surveys WHERE id = :id");
        $request->bindValue(':id', $value, PDO::PARAM_STR);
        $request->execute();

        return $request->fetchAll(PDO::FETCH_CLASS, 'Survey');
    }

    public static function update($object)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("UPDATE surveys SET orderID = :orderID, tableID = :tableID, restoRating = :restoRating, tableRating = :tableRating, cookerRating = :cookerRating, comments = :comments WHERE id = :id AND active = true");
        $request->bindValue(':id', $object->id, PDO::PARAM_INT);
        $request->bindValue(':orderID', $object->orderID, PDO::PARAM_STR);
        $request->bindValue(':tableID', $object->tableID, PDO::PARAM_STR);
        $request->bindValue(':restoRating', $object->restoRating, PDO::PARAM_INT);
        $request->bindValue(':tableRating', $object->tableRating, PDO::PARAM_INT);
        $request->bindValue(':waiterRating', $object->waiterRating, PDO::PARAM_INT);
        $request->bindValue(':cookerRating', $object->cookerRating, PDO::PARAM_INT);
        $request->bindValue(':comments', $object->comments, PDO::PARAM_STR);
        $request->execute();    }

    public static function delete($object)
    {
        // TODO: Implement delete() method.
    }
}