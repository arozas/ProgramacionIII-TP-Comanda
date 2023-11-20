<?php

class AuthMiddleware
{
    private $userType;

    public function __construct($userType)
    {
        $this->userType = $userType;
    }

    public function __invoke($request, $handler)
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['token'];

        AuthJWT::TokenVerifcation($token);
        $payload = AuthJWT::GetData($token);

        // Validar el tipo de usuario
        if ('socio' == $payload->rol || $this->userType == $payload->rol) {
            echo(ucfirst($this->userType) . " logueado:");
            return $handler->handle($request);
        }

        throw new Exception("Token no válido para el tipo de usuario especificado");
    }
}