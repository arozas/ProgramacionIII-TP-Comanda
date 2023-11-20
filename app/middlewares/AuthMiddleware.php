<?php


class AuthMiddleware
{
    private $allowedRoutes = [];

    public function __construct()
    {
        $config = require(__DIR__ . '/../config.php');
        $userType = $this->getUserType();
        $this->allowedRoutes = $config['user_types'][$userType]['allowed_routes'];
    }

    public function __invoke($request, $handler)
    {
        $requestedPath = $request->getUri()->getPath();
        $requestedMethod = $request->getMethod();

        $routeWithMethod = $requestedMethod . ':' . $requestedPath;

        if ($this->isRouteAllowed($routeWithMethod)) {
            $cookies = $request->getCookieParams();
            $token = $cookies['token'];

            AuthJWT::TokenVerifcation($token);
            $payload = AuthJWT::GetData($token);

            if ($payload->rol == $this->getUserType()) {
                echo(ucfirst($this->getUserType()) . " logueado:");
                return $handler->handle($request);
            }

            throw new Exception("Token no válido para el tipo de usuario especificado");
        }

        throw new Exception("Ruta no permitida para el tipo de usuario especificado");
    }

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

    private function getUserType()
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

    public static function getUserTypeFromToken($request)
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['token'];

        AuthJWT::TokenVerifcation($token);
        $payload = AuthJWT::GetData($token);

        return $payload->rol;
    }
}