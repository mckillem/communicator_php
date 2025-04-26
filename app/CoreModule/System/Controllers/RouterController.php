<?php

namespace App\CoreModule\System\Controllers;

use App\CoreModule\Users\Controllers\LoginController;
use App\CoreModule\Users\Models\UserManager;

class RouterController extends Controller
{
	protected Controller $controller;

//	function process(array $parameters): void
//	{
//		$parsedURL = $this->parseURL($parameters[0]);
//
////		try {
////			if (!empty($parsedURL)) {
////				if ($parsedURL[0] === "message") {
////					$this->controller = new MessageController();
////
////					$this->controller->process($parsedURL);
////				} else {
////					throw new \Exception('Invalid URL');
////				}
////			} else {
////				throw new \Exception('Invalid URL');
////			}
////		} catch (\Exception $e) {
////			echo $e->getMessage();
////		}
//
//		$this->controller = new LoginController();
//		$this->controller->process($parsedURL);
//	}

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

//		if (UserManager::$user) {
			$this->controller = new MessageController();
//		} else {
//			$this->controller = new LoginController();
//		}
		$this->controller->index($parameters);

//		$this->data['messages'] = $this->getMessages();

		$this->view = 'layout';
	}
}