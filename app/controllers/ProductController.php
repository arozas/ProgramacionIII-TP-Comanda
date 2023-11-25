<?php

require_once './interfaces/IApiUse.php';
require_once './models/Product.php';
require_once './services/ProductService.php';

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

        ProductService::create($prod);

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');    }

    public static function Get($request, $response, $args)
    {
        $id = $args['id'];
        $product = ProductService::getOne($id);
        $payload = json_encode($product);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');    }

    public static function GetAll($request, $response, $args)
    {
        $productList = ProductService::getAll();
        $payload = json_encode(array("listaProductos" => $productList));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Delete($request, $response, $args)
    {
        $id = $args['id'];

        if (ProductService::getOne($id)) {
            ProductService::delete($id);
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

        $productAux = ProductService::getOne($id);

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
                ProductService::update($productAux);
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

    public static function DownloadFile($request, $response, $args)
    {
        $productList = ProductService::getAll();

        $stream = fopen('php://temp', 'w+');
        foreach ($productList as $p) {
            fputcsv($stream, get_object_vars($p));
        }

        $response = $response->withHeader('Content-Type', 'text/csv');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="product_list.csv"');
        $response = $response->withHeader('Pragma', 'no-cache');
        $response = $response->withHeader('Expires', '0');
        $response = $response->withBody(new \Slim\Psr7\Stream($stream));
        return $response;
    }

    public static function LoadFile($request, $response, $args)
    {
        $CSVFile = $request->getUploadedFiles()['csv'];
        $stream = $CSVFile->getStream();
        $content = ($stream)->getContents();
        $lines = explode("\n", $content);

        $productoList = [];
        foreach ($lines as $l) {
            $data = str_getcsv($l);
            if (empty(trim($l))) {
                break;
            }
            echo($data[0]);
            echo($data[0]);
            if (ProductService::NameValidation($data[0]) != null || !ProductService::ProductTypeValidation($data[2])) {
                throw new Exception("Fallo en la carga por validacion de datos");
            }
            $product = new Product();
            $product->name = $data[0];
            $product->description = $data[1];
            $product->productType = $data[2];
            $product->price = $data[3];
            $product->stock = $data[4];
            $productoList[] = $product;
        }

        ProductService::createList($productoList);
        $payload = json_encode(array("mensaje" => "Archivo cargado con exito"));


        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

}