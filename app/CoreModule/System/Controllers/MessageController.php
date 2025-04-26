<?php

namespace App\CoreModule\System\Controllers;

use App\CoreModule\System\Models\MessageManager;
use App\CoreModule\Users\Models\UserManager;

class MessageController extends Controller
{
	function index(array $parameters): void
	{
		$messageManager = new MessageManager();
		$userManager = new UserManager();

		$this->data['messages'] = $messageManager->getAllMessages();
		$this->data['users'] = $userManager->getAllUsers();
		$this->data['loggedUser'] = UserManager::$user;

		if (!empty($_POST) && $_POST['text'])
		{
				$messageManager->sendMessage($_POST);

				$this->redirect();
		}

		$this->view = 'index';
	}
}