<?php
require_once './middlewares/AuthMiddleware.php';
require_once './services/UserService.php';

class Logger
{
    public static function LoginValidation($request, $handler)
    {
        $parameters = $request->getParsedBody();
        $user = $parameters['usuario'];
        $password = $parameters['clave'];
        $userAux = UserService::getOneByUsername($user);

        if ($userAux != false && password_verify($password, $userAux->password)) {
            return $handler->handle($request);
        }

        throw new Exception("Usuario y/o clave erroneos");
    }

    public static function UserLogger($request, $handler)
    {
        $userId = AuthMiddleware::getUserId();
        $username = AuthMiddleware::getUserName();
        $timestamp = (new DateTime())->format('Y-m-d H:i:s');
        $requestMethod = $request->getMethod();
        $requestPath = $request->getUri()->getPath();

        $DAO = DataAccessObject::getInstance();
        $stmt = $DAO->prepareRequest("INSERT INTO request_log (user_id, username, timestamp, method, path) VALUES (:user_id, :username, :timestamp, :method, :path)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
        $stmt->bindParam(':method', $requestMethod, PDO::PARAM_STR);
        $stmt->bindParam(':path', $requestPath, PDO::PARAM_STR);
        $stmt->execute();

        $response = $handler->handle($request);

        return $response;

    }


}