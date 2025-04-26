<?php

namespace App\CoreModule\System\Models;

use App\CoreModule\Users\Models\UserManager;

class MessageManager
{
//	public function returnMessage(string $id): array|false
//	{
//		return Db::queryOne('
//			SELECT *
//			FROM `message`
//			JOIN `message_user` on `message`.`message_id` = ?
//			JOIN `user` on `message_user`.`user_id`
//			where `message_user`.`user_id` = `user`.`user_id`
//		', array($id));
//	}

	public function getAllMessages(): false|array
	{
		return Db::getAllMessages();
	}

	public function sendMessage(array $data): void
	{
		$message = [
			'text' => $data['text'],
//			'createdAt' => new \DateTime()->format('Y-m-d H:i:s'),
//			'deletedAt' => new \DateTime()->format('Y-m-d H:i:s'),
//			'deliveredAt' => new \DateTime()->format('Y-m-d H:i:s'),
//			'readAt' => new \DateTime()->format('Y-m-d H:i:s'),
//			todo: vložit ze ssesion?
			'createdBy' => UserManager::$user['user_id'],
			'createdFor' => 2,
		];

		Db::sendMessage($message);



//		$message_user = [
//			'message_id' => Db::getLastId(),
//			'user_id_from' => $this->getUserByEmail($data['username'])['user_id'],
//			'user_id_to' => $this->getUserByEmail($data['username'])['user_id'],
//		];
//
//		Db::insert('message_user', $message_user);
	}

//	public function getUserByEmail(string $email): array
//	{
//		return Db::queryOne('SELECT `user_id` FROM `user` WHERE `email` = ?', array($email));
//	}

//	public function getMessageById(string $id): int
//	{
//		return Db::queryOne('message',  ['message_id' => $id]);
//	}
}