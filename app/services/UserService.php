<?php
require_once './interfaces/IPersistance.php';
require_once './Database/DataAccessObject.php';
require_once './enums/UserType.php';
require_once './models/User.php';
require_once './models/dto/UserDTO.php';


class UserService implements IPersistance
{
    public static function create($user)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("INSERT INTO users (user, password, userType, active, modifiedDate) VALUES (:user, :password, :userType, true,:modifiedDate)");
        $passHash = password_hash($user->password, PASSWORD_DEFAULT);
        $request->bindValue(':user', $user->user, PDO::PARAM_STR);
        $request->bindValue(':password', $passHash);
        $request->bindValue(':userType', $user->userType, PDO::PARAM_STR);
        $date = new DateTime(date("d-m-Y"));
        $request->bindValue(':modifiedDate', date_format($date, 'Y-m-d H:i:s'));
        $request->execute();

        return $DAO->getLastId();
    }

    public static function createList($list)
    {
        foreach ($list as $u) {
            User::create($u);
        }
    }

    public static function getAll()
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT id, user, password, userType FROM users WHERE active = true");
        $request->execute();

        return $request->fetchAll(PDO::FETCH_CLASS, 'UserDTO');
    }

    public static function getOne($id)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT id, user, password, userType FROM users WHERE id = :id AND active = true");
        $request->bindValue(':id', $id, PDO::PARAM_STR);
        $request->execute();

        return $request->fetchObject('UserDTO');
    }

    public static function update($user)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("UPDATE users SET user = :user, password = :password, userType = :userType, modifiedDate = :modifiedDate WHERE id = :id AND active = true");

        $request->bindValue(':user', $user->user, PDO::PARAM_STR);
        $request->bindValue(':password', $user->password, PDO::PARAM_STR);
        $request->bindValue(':userType', $user->userType, PDO::PARAM_STR);
        $request->bindValue(':id', $user->id, PDO::PARAM_INT);
        $date = new DateTime(date("d-m-Y"));
        $request->bindValue(':modifiedDate', date_format($date, 'Y-m-d H:i:s'));
        $request->execute();
    }

    public static function delete($id)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("UPDATE users SET modifiedDate = :modifiedDate, active = false WHERE id = :id");
        $date = new DateTime(date("d-m-Y"));
        $request->bindValue(':id', $id, PDO::PARAM_INT);
        $request->bindValue(':modifiedDate', date_format($date, 'Y-m-d H:i:s'));
        $request->execute();
    }

    public static function getUser($user)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT id, user, password, userType FROM users WHERE user = :user AND active = true");
        $request->bindValue(':user', $user, PDO::PARAM_STR);
        $request->execute();
        return $request->fetchObject('UserDTO');
    }


    public static function UserTypeValidation($userType)
    {
        if (   $userType != UserType::PARTNER->getStringValue()
            && $userType != UserType::BARTENDER->getStringValue()
            && $userType != UserType::BREWER->getStringValue()
            && $userType != UserType::COOKER->getStringValue()
            && $userType != UserType::WAITER->getStringValue()
            && $userType != UserType::CANDYBAR->getStringValue()) {
            return false;
        }
        return true;
    }

    public static function UserNameValidation($username)
    {
        $users = UserService::getAll();

        foreach ($users as $user) {
            if ($user->user == $username) {
                return $user;
            }
        }
        return null;
    }
}