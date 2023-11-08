<?php

interface IApiUse
{
    public static function Add($request, $response, $args);
    public static function Get($request, $response, $args);
    public static function GetAll($request, $response, $args);
    public static function Delete($request, $response, $args);
    public static function Update($request, $response, $args);
}