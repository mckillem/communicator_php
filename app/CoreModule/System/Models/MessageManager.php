<?php

namespace app\CoreModule\System\models;

class MessageManager
{
	public function returnMessage(string $id): array|false
	{
		return \ItNetwork\Db::queryOne('
			SELECT *
			FROM `message`
			JOIN `message_user` on `message`.`message_id` = ?
			JOIN `user` on `message_user`.`user_id`
			where `message_user`.`user_id` = `user`.`user_id`
		', array($id));
	}

	public function getAllMessages(): false|array
	{
		return \ItNetwork\Db::queryAll("SELECT * FROM `message`");
	}

	public function sendMessage(array $data): void
	{
		$message = [
			'text' => $data['text'],
			'createdAt' => new \DateTime()->format('Y-m-d H:i:s'),
			'deletedAt' => new \DateTime()->format('Y-m-d H:i:s'),
			'deliveredAt' => new \DateTime()->format('Y-m-d H:i:s'),
			'readAt' => new \DateTime()->format('Y-m-d H:i:s'),
//			todo: vložit ze ssesion?
			'createdBy' => 1,
		];

		\ItNetwork\Db::insert('message', $message);

		$message_user = [
			'message_id' => \ItNetwork\Db::getLastId(),
			'user_id' => $this->getUserByEmail($data['username'])['user_id'],
		];

		\ItNetwork\Db::insert('message_user', $message_user);
	}

	public function getUserByEmail(string $email): array
	{
		return \ItNetwork\Db::queryOne('SELECT `user_id` FROM `user` WHERE `email` = ?', array($email));
	}

//	public function getMessageById(string $id): int
//	{
//		return \ItNetwork\Db::queryOne('message',  ['message_id' => $id]);
//	}
}