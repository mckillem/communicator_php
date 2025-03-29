<?php

namespace App\CoreModule\System\Controllers;

//use App\CoreModule\Articles\Models\ArticleManager;
use AllowDynamicProperties;
use App\CoreModule\System\Models\MessageManager;
use App\CoreModule\Users\Models\UserManager;

#[AllowDynamicProperties] class MessageController extends Controller
{

//	public function index(array $parameters): void
//	{
//		// Vytvoření instance modelu, který nám umožní pracovat s články
//		$articleManager = new ArticleManager();
//		$this->data['admin'] = UserManager::$user && UserManager::$user['admin'];
//
//		// Získání článku podle URL
//		$articleManager->loadArticle($parameters[0]);
//
//		// Pokud nebyl článek s danou URL nalezen, přesměrujeme na ChybaKontroler
//		if (!ArticleManager::$article)
//			$this->redirect('chyba');
//
//		// Volání vnořeného kontroleru
//		if (ArticleManager::$article['controller'])
//		{
//			$fullName = 'App\\' . ArticleManager::$article['controller'] . 'Controller';
//			$controller = new $fullName;
//			array_shift($parameters); // mezi parametry nepatří URL článku
//			$controller->callActionFromParams($parameters);
//			$this->data['controller'] = $controller;
//		}
//		else
//			$this->data['controller'] = null;
//
//		// Naplnění proměnných pro šablonu
//		$this->data['title'] = ArticleManager::$article['title'];
//		$this->data['content'] = ArticleManager::$article['content'];
//
//		// Nastavení šablony
//		$this->view = 'index';
//	}
	function process(array $parameters): void
	{
		$api = $_SERVER['REQUEST_METHOD'];
		$messageManager = new MessageManager();

		$this->getMessages();
		$this->data['text'] = $messageManager->getAllMessages();


//		$id = $parameters[1];
//		if ($api === 'GET') {
//			if (!empty($id)) {
//				$this->getMessageById($id, $messageManager);
//			}
//		}


		$this->view = 'index';

	}

	public function getMessageById(string $id, MessageManager $messageManager): void
	{
		echo json_encode($messageManager->returnMessage($id));
	}
}