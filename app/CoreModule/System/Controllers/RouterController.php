<?php

namespace App\CoreModule\System\Controllers;

use App\CoreModule\Users\Controllers\LoginController;
use App\CoreModule\Users\Models\UserManager;

class RouterController extends Controller
{
	protected Controller $controller;

	private function parseURL(string $url): array
	{
		$parsedURL = parse_url($url);
		$parsedURL["path"] = ltrim($parsedURL["path"], "/");
		$parsedURL["path"] = trim($parsedURL["path"]);

		return explode("/", $parsedURL["path"]);
	}

	public function index(array $parameters): void
	{
		$userManager = new UserManager();
		$userManager->loadUser();

		$parsedUrl = $this->parseUrl($parameters[0]);

		if (empty($parsedUrl[0]))
			$parsedUrl[0] = '';

		if ($parsedUrl[0] === 'logout')
		{
			unset($_SESSION['user']);
			$this->redirect();
		}

		if (UserManager::$user) {
			$this->controller = new MessageController();
		} else {
			$this->controller = new LoginController();
		}
		$this->controller->index($parameters);

		$this->view = 'layout';
	}
}