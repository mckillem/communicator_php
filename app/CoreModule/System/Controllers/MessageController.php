<?php

namespace App\CoreModule\System\Controllers;

use App\CoreModule\System\Models\MessageManager;
use App\CoreModule\Users\Models\UserManager;

class MessageController extends Controller
{
	protected Controller $controller;

	function index(array $parameters): void
	{
		$messageManager = new MessageManager();

		$this->getMessages();

		$this->data['messages'] = $messageManager->getAllMessages();

		if (!empty($_POST) && $_POST['text'])
		{
				$messageManager->sendMessage($_POST);

				$this->redirect();
		}
//			try
//			{
//				$data = $form->getData();
//			} catch (UserException $e)
//			{
//				$this->addMessage($e->getMessage());
//			}
//		}

		$this->view = 'index';
	}

//	private function getMessageForm(): Form
//	{
//		$form = new Form('message');
//		$form->addTextBox('username', 'Komu', true);
//		$form->addTextArea('text', 'Zpráva', true);
//		$form->addButton('submit', 'Odeslat');
//
//		return $form;
//	}

	public function logout(): void
	{
		echo 'nnnnn';
		$userManager = new UserManager();
		$userManager->logout();
		$this->redirect('prihlaseni');
	}
}