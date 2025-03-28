<?php

namespace app\controllers;

use app\models\MessageManager;

class MessageController extends Controller
{
	function process(array $parameters): void
	{
		$api = $_SERVER['REQUEST_METHOD'];
		$messageManager = new MessageManager();

		$id = $parameters[1];
		if ($api === 'GET') {
			if (!empty($id)) {
				$this->getMessageById($id, $messageManager);
			}
		}
	}

	public function getMessageById(string $id, MessageManager $messageManager): void
	{
		echo json_encode($messageManager->returnMessage($id));
	}
}