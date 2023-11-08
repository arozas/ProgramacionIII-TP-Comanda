<?php

require_once './interfaces/IApiUse.php';
require_once './models/User.php';

class UserController implements IApiUse
{
    public static function Add($request, $response, $args)
    {
        $parameters = $request->getParsedBody();
        $userName = $parameters['usuario'];
        $userType = $parameters['tipoUsuario'];
        $password = $parameters['clave'];

        $user = new User();
        $user->user = $userName;
        $user->password = $password;
        $user->userType = $userType;

        User::create($user);
        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Get($request, $response, $args)
    {

        $userID = $args['id'];
        $user = User::getOne($userID);
        $payload = json_encode($user);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }


    public static function GetAll($request, $response, $args)
    {

        $userList = User::getAll();
        $payload = json_encode(array("listaUsuario" => $userList));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Update($request, $response, $args)
    {

        $id = $args['id'];

        $user = User::getOne($id);

        if ($user != false) {
            $parameters = $request->getParsedBody();

            $updated = false;
            if (isset($parameters['usuario'])) {
                $updated = true;
                $user->user = $parameters['usuario'];
            }
            if (isset($parametros['clave'])) {
                $updated = true;
                $user->password = password_hash($parameters['clave'], PASSWORD_DEFAULT);
            }
            if (isset($parameters['tipoUsuario'])) {
                $updated = true;
                $user->userType = $parameters['tipoUsuario'];
            }

            if ($updated) {
                User::update($user);
                $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "Usuario no modificar por falta de campos"));
            }

        } else {
            $payload = json_encode(array("error" => "Usuario no existe"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Delete($request, $response, $args)
    {
        $userID = $args['id'];

        if (User::getOne($userID)) {

            User::delete($userID);
            $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
        } else {

            $payload = json_encode(array("mensaje" => "ID no coincide con un usuario"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}