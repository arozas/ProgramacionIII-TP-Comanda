<?php

class SurveyController implements IApiUse
{

    public static function Add($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigoMesa = $parametros['codigoMesa'];
        $codigoPedido = $parametros['codigoPedido'];
        $puntuacionMesa = $parametros['puntuacionMesa'];
        $puntuacionRestaurante = $parametros['puntuacionRestaurante'];
        $puntuacionMozo = $parametros['puntuacionMozo'];
        $puntuacionCocinero = $parametros['puntuacionCocinero'];
        $experiencia = $parametros['experiencia'];

        $survey = new Survey();
        $survey->tableID = $codigoMesa;
        $survey->orderID = $codigoPedido;
        $survey->tableRating = $puntuacionMesa;
        $survey->restoRating = $puntuacionRestaurante;
        $survey->waiterRating = $puntuacionMozo;
        $survey->cookerRating = $puntuacionCocinero;
        $survey->comments = $experiencia;

        $orderTableVerified = OrderService::verifiedOrderByTable($codigoMesa, $codigoPedido, OrderStatus::PAYED->getStringValue());

        if ($orderTableVerified) {

            SurveyService::create($survey);

            $payload = json_encode(array("mensaje" => "Encuesta registrada con exito"));
        } else {
            $payload = json_encode(array("mensaje" => "Para registrar la encuesta el cliente tiene que terminar y pagar el pedido."));

        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');    }

    public static function Get($request, $response, $args)
    {
        $id = $args['id'];
        $survey = SurveyService::getOne($id);
        $payload = json_encode($survey);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function GetAll($request, $response, $args)
    {
        $surveyList = SurveyService::getAll();
        $payload = json_encode($surveyList);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Delete($request, $response, $args)
    {
        // TODO: Implement Delete() method.
    }

    public static function Update($request, $response, $args)
    {
        // TODO: Implement Update() method.
    }
}