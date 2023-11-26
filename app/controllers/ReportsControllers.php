<?php

require_once './services/ReportsService.php';

class ReportsControllers
{
    public static function GetLoginByEmployee($request, $response, $args)
    {
        $UserId = $args['id'];
        $survey = ReportsService::getLoginByEmployee($UserId);
        $payload = json_encode($survey);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}