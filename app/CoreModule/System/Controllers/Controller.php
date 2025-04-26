<?php

namespace App\CoreModule\System\Controllers;

use App\CoreModule\Users\Models\UserManager;
use ReflectionClass;

abstract class Controller
{
	protected array $data = array();
	protected string $view = "";

	public function redirect(string $url = ''): void
	{
		header("Location: /$url");
		header("Connection: close");
		exit;
	}

	public function renderView(): void
	{
		if ($this->view)
		{
			extract($this->data);
			extract($this->data, EXTR_PREFIX_ALL, "");

			$reflect = new ReflectionClass(get_class($this));

			$path = str_replace('Controllers', 'Views', str_replace('\\', '/', $reflect->getNamespaceName()));
			$controllerName = str_replace('Controller', '', $reflect->getShortName());
			$path = '../a' . ltrim($path, 'A') . '/' . $controllerName . '/' . $this->view . '.phtml';

			require($path);
		}
	}

	public function authUser(bool $admin = false): void
	{
		$user = UserManager::$user;
		if (!$user || ($admin && !$user['admin']))
		{
			$this->redirect('prihlaseni');
		}
	}
}