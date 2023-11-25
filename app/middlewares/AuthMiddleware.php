<?php

use Firebase\JWT\ExpiredException;

class AuthMiddleware
{
    private $allowedRoutes = [];

    public function __construct()
    {
        $config = require(__DIR__ . '/../config.php');
        try {
            $userType = AuthMiddleware::getUserType();
            $this->allowedRoutes = $config['user_types'][$userType]['allowed_routes'];
        }
        catch(ExpiredException $expiredException)
        {
            echo('$expiredException');
            echo('Para operar debe loguerase de nuevo.');
        }
        $loginRoute = 'POST:/app/login/';
        $this->allowedRoutes[] = $loginRoute;
    }

    public function __invoke($request, $handler)
    {
        $requestedPath = $request->getUri()->getPath();
        $requestedMethod = $request->getMethod();

        $routeWithMethod = $requestedMethod . ':' . $requestedPath;

        if ($this->isRouteAllowed($routeWithMethod)) {

            if ($requestedMethod === 'POST' && $requestedPath === '/app/login/') {
                return $handler->handle($request);
            }

            $cookies = $request->getCookieParams();
            $token = $cookies['token'];

            AuthJWT::TokenVerifcation($token);
            $payload = AuthJWT::GetData($token);

            if ($payload->rol == AuthMiddleware::getUserType()) {
                return $handler->handle($request);
            }

            throw new Exception("Token no válido para el tipo de usuario especificado");
        }

        throw new Exception("Ruta no permitida para el tipo de usuario especificado");
    }

    /*
    public function __construct()
    {
        $config = require(__DIR__ . '/../config.php');
        $userType = AuthMiddleware::getUserType();
        $this->allowedRoutes = $config['user_types'][$userType]['allowed_routes'];
    }

    public function __invoke($request, $handler)
    {
        $requestedPath = $request->getUri()->getPath();
        $requestedMethod = $request->getMethod();

        echo($requestedMethod);
        echo($requestedPath);
        // Verifica si la ruta es el inicio de sesión y permite el acceso sin token
        if ($requestedMethod === 'POST' && $requestedPath === '/app/login/') {
            return $handler->handle($request);
        }

        $routeWithMethod = $requestedMethod . ':' . $requestedPath;

        if ($this->isRouteAllowed($routeWithMethod)) {
            $cookies = $request->getCookieParams();
            $token = $cookies['token'];

            AuthJWT::TokenVerifcation($token);
            $payload = AuthJWT::GetData($token);

            if ($payload->rol == AuthMiddleware::getUserType()) {
                echo(ucfirst(AuthMiddleware::getUserType()) . " logueado:");
                return $handler->handle($request);
            }

            throw new Exception("Token no válido para el tipo de usuario especificado");
        }

        throw new Exception("Ruta no permitida para el tipo de usuario especificado");
    }*/

    private function isRouteAllowed($routeWithMethod)
    {
        $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE'];

        list($requestedMethod, $requestedPath) = explode(':', $routeWithMethod);

        if (!in_array($requestedMethod, $allowedMethods)) {
            return false;
        }

        foreach ($this->allowedRoutes as $allowedRoute) {
            list($allowedMethod, $allowedPath) = explode(':', $allowedRoute);

            if ($requestedMethod === $allowedMethod && $this->matchesRoute($requestedPath, $allowedPath)) {
                return true;
            }
        }

        return false;
    }

    private function matchesRoute($requestedPath, $allowedPath)
    {
        $requestedParts = explode('/', trim($requestedPath, '/'));
        $allowedParts = explode('/', trim($allowedPath, '/'));

        if (count($requestedParts) !== count($allowedParts)) {
            return false;
        }

        foreach ($requestedParts as $key => $part) {
            if ($allowedParts[$key] !== $part && strpos($allowedParts[$key], '{') === false) {
                return false;
            }
        }

        return true;
    }

    public static function getUserId()
    {
        try {
            $cookies = $_COOKIE;
            $token = $cookies['token'];

            $payload = AuthJWT::GetData($token);

            return $payload->id;
        } catch (Exception $e) {
            throw new Exception("No se pudo obtener el tipo de usuario.");
        }
    }

    public static function getUserName()
    {
        try {
            $cookies = $_COOKIE;
            $token = $cookies['token'];

            $payload = AuthJWT::GetData($token);

            return $payload->usuario;
        } catch (Exception $e) {
            throw new Exception("No se pudo obtener el tipo de usuario.");
        }
    }

    public static function getUserType()
    {
        try {
            $cookies = $_COOKIE;

            if (isset($cookies['token'])) {
                $token = $cookies['token'];

                try {
                    $payload = AuthJWT::GetData($token);
                    return $payload->rol;
                } catch (ExpiredException $expiredException) {
                    throw new Exception("El token ha expirado.");
                }
            } else {
                throw new Exception("La cookie 'token' no está presente.");
            }
        } catch (Exception $e) {
            throw new Exception("No se pudo obtener el tipo de usuario. " . $e->getMessage());
        }
    }


    /*
    public static function getUserType()
    {
        try {
            $cookies = $_COOKIE;
            $token = $cookies['token'];

            $payload = AuthJWT::GetData($token);

            return $payload->rol;
        } catch (Exception $e) {
            throw new Exception("No se pudo obtener el tipo de usuario.");
        }
    }
    */


}