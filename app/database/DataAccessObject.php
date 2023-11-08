<?php
class DataAccessObject
{
    private static $dataAccess;
    private $PDO;

    private function __construct()
    {
        try {
            $this->PDO = new PDO('mysql:host='.$_ENV['MYSQL_HOST'].';dbname='.$_ENV['MYSQL_DB'].';charset=utf8', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $this->PDO->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            print "Error: " . $e->getMessage();
            die();
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$dataAccess)) {
            self::$dataAccess = new DataAccessObject();
        }
        return self::$dataAccess;
    }

    public function prepareRequest($sql)
    {
        return $this->PDO->prepare($sql);
    }

    public function getLastId()
    {
        return $this->PDO->lastInsertId();
    }

    public function __clone()
    {
        trigger_error('ERROR: La clonación de este objeto no está permitida', E_USER_ERROR);
    }
}