<?php

require_once './interfaces/IApiUse.php';
require_once './models/Product.php';

class ProductController implements IApiUse
{

    public static function Add($request, $response, $args)
    {
        $parameters = $request->getParsedBody();

        $productName = $parameters['nombre'];
        $productDescription = $parameters['descripcion'];
        $productType = $parameters['tipo'];
        $productPrice = $parameters['precio'];
        $productStock = $parameters['stock'];

        $prod = new Product();
        $prod->name = $productName;
        $prod->description = $productDescription;
        $prod->productType = $productType;
        $prod->price = $productPrice;
        $prod->stock = $productStock;
        $prod->active = true;

        Product::create($prod);

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');    }

    public static function Get($request, $response, $args)
    {
        $id = $args['id'];
        $product = Product::getOne($id);
        $payload = json_encode($product);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');    }

    public static function GetAll($request, $response, $args)
    {
        $productList = Product::getAll();
        $payload = json_encode(array("listaProductos" => $productList));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Delete($request, $response, $args)
    {
        $id = $args['id'];

        if (Product::getOne($id)) {
            Product::delete($id);
            $payload = json_encode(array("mensaje" => "Producto borrado con exito"));
        } else {

            $payload = json_encode(array("mensaje" => "ID no coincide con un Producto"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Update($request, $response, $args)
    {
        $id = $args['id'];

        $productAux = Product::getOne($id);

        if ($productAux != false) {
            $parmeters = $request->getParsedBody();

            $updated = false;
            if (isset($parmeters['nombre'])) {
                $updated = true;
                $productAux->name = $parmeters['nombre'];
            }
            if (isset($parmeters['descripcion'])) {
                $updated = true;
                $productAux->description = $parmeters['descripcion'];
            }
            if (isset($parmeters['tipo'])) {
                $updated = true;
                $productAux->productType = $parmeters['tipo'];
            }
            if (isset($parmeters['precio'])) {
                $updated = true;
                $productAux->price = $parmeters['precio'];
            }
            if (isset($parmeters['stock'])) {
                $updated = true;
                $productAux->stock = $parmeters['stock'];
            }

            if ($updated) {
                Product::update($productAux);
                $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "Producto no modificar por falta de campos"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "ID no coinciden con ningun Producto"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}