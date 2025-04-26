<?php

use App\CoreModule\System\Controllers\RouterController;
use App\CoreModule\System\Models\Db;

session_start();

mb_internal_encoding("UTF-8");

function autoloader($class): void
{
	if (mb_strpos($class, 'App\\') !== false)
		$class = 'a' . ltrim($class, 'A');
	else
		$class = 'vendor\\' . $class;
	$path = str_replace('\\', '/', $class) . '.php';
	if (file_exists('../' . $path))
		include('../' . $path);
}

spl_autoload_register("autoloader");

Db::connect("database", "test", "test", "communicator_php_db");

$router = new RouterController();
$router->index(array($_SERVER['REQUEST_URI']));

$router->renderView();
