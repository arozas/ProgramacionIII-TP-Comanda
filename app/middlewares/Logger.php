<?php
require_once './services/UserService.php';
class Logger
{
    public static function LogOperacion($request, $response, $next)
    {
        $retorno = $next($request, $response);
        return $retorno;
    }

    public static function LoginValidation($request, $handler)
    {
        $parameters = $request->getParsedBody();
        $user = $parameters['usuario'];
        $password = $parameters['clave'];
        $userAux = UserService::getUser($user);

        if ($userAux != false && password_verify($password, $userAux->password)) {
            return $handler->handle($request);
        }

        throw new Exception("Usuario y/o clave erroneos");
    }
}