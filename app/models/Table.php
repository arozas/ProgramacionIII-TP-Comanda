<?php
require_once './enums/TableStatus.php';
class Table implements IPersistance
{
    public $id;
    public $status;

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

    public static function create($object)
    {
        $code = Table::generateCode(5);
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("INSERT INTO tables (id, status) VALUES (:id, :status)");
        $request->bindValue(':id', $code, PDO::PARAM_STR);
        $request->bindValue(':status', TableStatus::CLOSE->getStringValue(), PDO::PARAM_STR);
        $request->execute();
        return $DAO->getLastId();
    }

    public static function getAll()
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT id, status FROM tables WHERE status != :status");
        $request->bindValue(':status', TableStatus::DOWN->getStringValue(), PDO::PARAM_INT);
        $request->execute();

        return $request->fetchAll(PDO::FETCH_CLASS, 'Table');
    }

    public static function getOne($id)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT id, status FROM tables WHERE id = :id AND status != :status");
        $request->bindValue(':id', $id, PDO::PARAM_INT);
        $request->bindValue(':status', TableStatus::DOWN->getStringValue(), PDO::PARAM_INT);
        $request->execute();

        return $request->fetchObject('Table');
    }

    public static function update($table)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("UPDATE tables SET status = :status WHERE id = :id");
        $request->bindValue(':id', $table->id, PDO::PARAM_INT);
        $request->bindValue(':status', $table->status, PDO::PARAM_STR);
        $request->execute();
    }

    public static function delete($id)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("UPDATE tables SET status = :status WHERE id = :id");
        $request->bindValue(':id', $id, PDO::PARAM_INT);
        $request->bindValue(':status', TableStatus::DOWN->getStringValue(), PDO::PARAM_STR);
        $request->execute();
    }

    public static function generateCode($length)
    {
        $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $numbers = "0123456789";

        $code = "";

        $code .= $letters[rand(0, strlen($letters) - 1)];
        $code .= $numbers[rand(0, strlen($numbers) - 1)];

        for ($i = 2; $i < $length; $i++) {
            $characters = $letters . $numbers;
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        $code = str_shuffle($code);

        return $code;
    }
}