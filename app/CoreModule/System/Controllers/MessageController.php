<?php

namespace App\CoreModule\System\Controllers;

use App\CoreModule\System\Models\MessageManager;

class MessageController extends Controller
{
	function index(array $parameters): void
	{
		$messageManager = new MessageManager();

		$this->data['messages'] = $messageManager->getAllMessages();

		if (!empty($_POST) && $_POST['text'])
		{
				$messageManager->sendMessage($_POST);

				$this->redirect();
		}

		$this->view = 'index';
	}
}