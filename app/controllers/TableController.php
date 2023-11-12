<?php
require_once './interfaces/IApiUse.php';
require_once './models/Table.php';
class TableController implements IApiUse
{
    public static function Add($request, $response, $args)
    {
        $dummyObject = null;
        Table::create($dummyObject);
        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Get($request, $response, $args)
    {
        $id = $args['id'];
        $table = Table::getOne($id);
        $payload = json_encode($table);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function GetAll($request, $response, $args)
    {
        $tableList = Table::getAll();
        $payload = json_encode(array("listaMesas" => $tableList));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Delete($request, $response, $args)
    {
        $id = $args['id'];
        if (Table::getOne($id)) {
            Table::delete($id);
            $payload = json_encode(array("mensaje" => "mesa borrada con exito"));
        } else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Mesa"));
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Update($request, $response, $args)
    {
        $id = $args['id'];

        $table = Table::getOne($id);

        if ($table != false) {
            $parameters = $request->getParsedBody();

            $updated = false;
            if (isset($parameters['estado'])) {

                $updated = true;
                $table->status = $parameters['estado'];
            }

            if ($updated) {
                Table::update($table);
                $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "Mesa no modificada por falta de campos"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ninguna Mesa"));
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');

    }
}