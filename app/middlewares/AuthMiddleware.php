<?php

class AuthMiddleware
{

    public static function AuthUser($request, $handler)
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['token'];

        AuthJWT::TokenVerifcation($token);
        $payload = AuthJWT::GetData($token);

        if ($payload->rol == 'socio') {
            return $handler->handle($request);
        }

        if ($payload->rol == 'socio' || $payload->rol == 'mozo') {
            return $handler->handle($request);
        }

        if ($payload->rol == 'socio' || $payload->rol == 'cocinero' || $payload->rol == 'cervecero' || $payload->rol == 'bartender' || $payload->rol == 'candybar') {
            return $handler->handle($request);
        }

        throw new Exception("Token no valido");
    }
}