<?php

namespace App\CoreModule\System\Controllers;

use App\CoreModule\System\Models\MessageManager;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;

class MessageController extends Controller
{
	/**
	 * @throws UserException
	 */
	function process(array $parameters): void
	{
		$messageManager = new MessageManager();

		$this->getMessages();
		$this->data['text'] = $messageManager->getAllMessages();

		$form = $this->getMessageForm();
		$this->data['form'] = $form;

		$data = $form->getData();
		$messageManager->sendMessage($data);

		$this->view = 'index';
	}

	private function getMessageForm(): Form
	{
		$form = new Form('message');
		$form->addTextBox('username', 'Komu', true);
		$form->addTextArea('text', 'Zpráva', true);
		$form->addButton('submit', 'Odeslat');

		return $form;
	}
}