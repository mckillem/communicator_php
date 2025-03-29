<?php

namespace App\CoreModule\System\Controllers;

//use App\CoreModule\Articles\Controllers\ArticleController;
//use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\System\Models\MessageManager;
use App\CoreModule\Users\Controllers\LoginController;
use App\CoreModule\Users\Models\UserManager;
//use App\EshopModule\Accounting\Models\SettingsManager;
//use App\EshopModule\Products\Models\CategoryManager;
//use App\EshopModule\Products\Models\OrderManager;
//use config\Settings;
use ItNetwork\UserException;
use ItNetwork\Utility\DateTimeUtility;

class RouterController extends Controller
{
	protected Controller $controller;

	function process(array $parameters): void
	{
		$parsedURL = $this->parseURL($parameters[0]);

//		try {
//			if (!empty($parsedURL)) {
//				if ($parsedURL[0] === "message") {
//					$this->controller = new MessageController();
//
//					$this->controller->process($parsedURL);
//				} else {
//					throw new \Exception('Invalid URL');
//				}
//			} else {
//				throw new \Exception('Invalid URL');
//			}
//		} catch (\Exception $e) {
//			echo $e->getMessage();
//		}

		$this->controller = new LoginController();
		$this->controller->process($parsedURL);
	}

	private function parseURL(string $url): array
	{
		$parsedURL = parse_url($url);
		$parsedURL["path"] = ltrim($parsedURL["path"], "/");
		$parsedURL["path"] = trim($parsedURL["path"]);

		return explode("/", $parsedURL["path"]);
	}

	/**
	 * Zpracuje dotaz na článek
	 * @throws UserException
	 * @param array $parameters Pole parametrů z URL adresy
	 */
	private function processArticleRequest(array $parameters): void
	{
		if (isset($_POST['search-phrase']))
		{
			$this->redirect('produkty?phrase=' . $_POST['search-phrase']);
		}

		// Volání controlleru
//		$this->controller = new ArticleController();
//		$this->controller = new LoginController();
		$this->controller = new MessageController();
//		$this->controller->index($parameters);
		$this->controller->process($parameters);


		$messageManager = new MessageManager();
//		$settingsManager = new SettingsManager();

//		$categoryManager = new CategoryManager();
		$admin = UserManager::$user && UserManager::$user['admin'];

		// Nastavení proměnných pro šablonu
//		$this->data['categories'] = $categoryManager->getCategories($admin);
//		$this->data['domain'] = Settings::$domain;
//		$this->data['title'] = ArticleManager::$article['title'];
//		$this->data['description'] = ArticleManager::$article['description'];
		$this->data['messages'] = $this->getMessages();
//		$this->data['settings'] = $settingsManager->getSettings(DateTimeUtility::dbNow());
//		$this->data['cart'] = $orderManager->getOrderSummary();

		// Nastavení hlavní šablony
		$this->view = 'layout';
	}

	/**
	 * Zpracuje dotaz na API
	 * @param array $parameters Pole parametrů z URL adresy
	 */
	private function processApiRequest(array $parameters): void
	{
		// Rozbití jmenných prostorů podle "-" a přidání "Controllers"
		$pieces = explode('-', array_shift($parameters));
		array_splice($pieces, count($pieces) - 1, 0, 'Controllers');

		$controllerPath = 'App\\' . implode('\\', $pieces);
		$controllerPath .= 'Controller';
		// Bezpečnostní kontrola cesty
		if (preg_match('/^[a-zA-Z0-9\\\\]*$/u', $controllerPath))
		{
			$controller = new $controllerPath(true);

			$controller->callActionFromParams($parameters, true);
			$controller->renderView();
		}
		else
			$this->redirect('error');
	}

	/**
	 * Naparsování URL adresy a vytvoření příslušného controlleru
	 * @throws UserException
	 * @param array $parameters Pod indexem 0 se očekává URL adresa ke zpracování
	 */
	public function index(array $parameters): void
	{
		$userManager = new UserManager();
		$userManager->loadUser();

		$parsedUrl = $this->parseUrl($parameters[0]);

		if (empty($parsedUrl[0]))
			$parsedUrl[0] = '';

		if ($parsedUrl[0] == 'api') // Zpracováváme požadavek na API
		{
			array_shift($parsedUrl); // Odstranění prvního parametru "api"
			$this->processApiRequest($parsedUrl);
		}
		else
			$this->processArticleRequest($parsedUrl);
	}
}