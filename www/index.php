<?php

use app\controllers\RouterController;
use app\models\Db;

session_start();

mb_internal_encoding("UTF-8");

function autoloader(string $class): void
{
	if (str_ends_with($class, 'Controller')) {
		$controllerClass = str_replace("app\\controllers\\", "", $class);
		require('../app/controllers/' . $controllerClass . '.php');
	} else {
		$controllerClass = str_replace("app\\models\\", "", $class);
		require('../app/models/' . $controllerClass . '.php');
	}
}

spl_autoload_register("autoloader");

Db::connect("database", "test", "test", "communicator_php_db");

$router = new RouterController();
$router->process(array($_SERVER['REQUEST_URI']));

