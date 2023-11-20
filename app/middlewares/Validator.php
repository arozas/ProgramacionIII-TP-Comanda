<?php

require_once './services/UserService.php';

class Validator
{

    public static function NewUserValidation($request, $handler)
    {
        $parametros = $request->getParsedBody();

        $username = $parametros['usuario'];
        $userType = $parametros['tipoUsuario'];
        if (UserService::UserTypeValidation($userType) && UserService::UserNameValidation($username) == null) {
            return $handler->handle($request);
        }

        throw new Exception("Error en la creacion del Usuario");
    }

    public static function FileValidation($request, $handler)
    {
        $uploadedFiles = $request->getUploadedFiles();

        if (isset($uploadedFiles['csv'])) {

            if (preg_match('/\.csv$/i', $uploadedFiles['csv']->getClientFilename()) == 0){
                throw new Exception("Debe ser un archivo CSV");
            }

            return $handler->handle($request);
        }

        throw new Exception("Error no se recibio el archivo");
    }
}