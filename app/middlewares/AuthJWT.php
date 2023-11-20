<?php

use Firebase\JWT\JWT;

class AuthJWT
{
    private static $secretWord = '$AR0Z45$';
    private static $encryptType = ['HS256'];

    public static function TokenCreate($data)
    {
        $now = time();
        $payload = array(
            'iat' => $now,
            'exp' => $now + (60000),
            'aud' => self::Aud(),
            'data' => $data,
            'app' => "TP Comanda"
        );


        $rtn = array('token' => $payload, 'jwt' => JWT::encode($payload, self::$secretWord));
        return $rtn;
    }

    public static function TokenVerifcation($token)
    {
        if (empty($token)) {
            throw new Exception("El token está vacío.");
        }
        try {
            $deco = JWT::decode(
                $token,
                self::$secretWord,
                self::$encryptType
            );
        } catch (Exception $e) {
            throw $e;
        }
        if ($deco->aud !== self::Aud()) {
            throw new Exception("No es el usuario valido");
        }
    }


    public static function GetPayload($token)
    {
        if (empty($token)) {
            throw new Exception("El token esta vacio.");
        }
        return JWT::decode(
            $token,
            self::$secretWord,
            self::$encryptType
        );
    }

    public static function GetData($token)
    {
        return JWT::decode(
            $token,
            self::$secretWord,
            self::$encryptType
        )->data;
    }

    private static function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }

}