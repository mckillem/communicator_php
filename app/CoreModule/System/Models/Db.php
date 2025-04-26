<?php

namespace App\CoreModule\System\Models;

use PDO;
use PDOException;

class Db
{
//	todo: proč statika? abych se vyhl nutnosti použit konstruktor?
	private static PDO $connection;

//	todo: k čemu?
	private static array $settings = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
		PDO::ATTR_EMULATE_PREPARES => false,
	);

	public static function connect(#[\SensitiveParameter] string $host, #[\SensitiveParameter] string $user, #[\SensitiveParameter] string $password, string $database): void
	{
		if (!isset(self::$connection)) {
			self::$connection = @new PDO(
				"mysql:host=$host;dbname=$database",
				$user,
				$password,
				self::$settings
			);
		}
	}

	public static function getAllMessages(): array
	{
		$sql = self::$connection->prepare(
			'
			SELECT *
			FROM `message`
			ORDER BY `message_id` DESC
		'
		);
		$sql->execute();

		return $sql->fetchAll(self::$connection::FETCH_ASSOC);
	}

	public static function sendMessage(array $message): void
	{
		try {
			$sql = self::$connection->prepare("
insert into message set
		text = :text,
		createdBy = :createdBy,
		createdFor = :createdFor
		");
			$sql->execute(array(
				':text' => $message['text'],
				':createdBy' => $message['createdBy'],
				':createdFor' => $message['createdFor']
			));

//				echo "Record updated successfully";
		} catch(PDOException $e) {
//				echo "<br>" . $e->getMessage();
		}
	}

	public static function getUserByEmail(string $email): ?array
	{
		$sql = self::$connection->prepare(
			"SELECT *
			FROM user
			WHERE email = :email"
		);
		$sql->execute(array(
			':email' => $email
		));

		return $sql->fetch(self::$connection::FETCH_ASSOC);
	}

	public static function getAllUsers(): array
	{
		$sql = self::$connection->prepare(
			'
			SELECT *
			FROM `user`
		'
		);
		$sql->execute();

		return $sql->fetchAll(self::$connection::FETCH_ASSOC);
	}

}