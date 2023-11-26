<?php

class ReportsService
{
    public static function getLoginByEmployee($Userid)
    {
        $DAO = DataAccessObject::getInstance();
        $request = $DAO->prepareRequest("SELECT user_id, username, DATE(timestamp) AS login_date, TIME(timestamp) AS login_time
                                         FROM request_log
                                         WHERE path = '/app/login' AND user_id = :user_id
                                         ORDER BY user_id, login_date, login_time");
        $request->bindValue(':user_id', $Userid, PDO::PARAM_STR);
        $request->execute();

        return $request->fetchAll(PDO::FETCH_ASSOC);
    }

}