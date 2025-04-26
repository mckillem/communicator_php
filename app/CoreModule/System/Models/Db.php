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
			SELECT `message_id`, `text`
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

	public static function getTodoById(string $id): array
	{
		$sql = self::$connection->prepare(
			"SELECT todo_id, text
			FROM todo
			WHERE todo_id = :todo_id"
		);
		$sql->execute(array(
			':todo_id' => $id
		));

		return $sql->fetch(self::$connection::FETCH_ASSOC);
	}

	public static function getPageByUrl(string $parsedUrl): array|false
	{
		$sql = self::$connection->prepare(
			'SELECT *
			FROM page
			WHERE url = :url'
		);
		$sql->execute(array(
			':url' => $parsedUrl
		));

		return $sql->fetch(self::$connection::FETCH_ASSOC);
	}

	public static function getPageById(string $id): array|false
	{
		$sql = self::$connection->prepare(
			'SELECT *
			FROM page
			WHERE page_id = :page_id'
		);
		$sql->execute(array(
			':page_id' => $id
		));

		return $sql->fetch(self::$connection::FETCH_ASSOC);
	}

	public function getAllPages(): array
	{
		$sql = self::$connection->prepare(
			'
			SELECT *
			FROM `page`
		'
		);
		$sql->execute();

		return $sql->fetchAll(self::$connection::FETCH_ASSOC);
	}

	public function save(array $page, ?string $id = null): void
	{
		if ($id)
		{
			try {
				$sql = self::$connection->prepare(
					"update page set
            title = :title, 
            content = :content, 
            url = :url, 
            description = :description, 
            controller = :controller 
            WHERE page_id = :page_id"
				);
				$sql->execute(array(
					':title' => $page['title'],
					':content' => $page['content'],
					':url' => $page['url'],
					':description' => $page['description'],
					':controller' => $page['controller'],
					':page_id' => $id
				));
// todo: vytvořit systém zpráv, echo být nemůže kvůli hlavičce
//				echo "New record created successfully";
			} catch(PDOException $e) {
//				echo "<br>" . $e->getMessage();
			}
		} else {
			try {
				$sql = self::$connection->prepare(
					"
			insert into page set
            title = :title, 
            content = :content, 
            url = :url, 
            description = :description, 
            controller = :controller
            "
				);
				$sql->execute(array(
					':title' => $page['title'],
					':content' => $page['content'],
					':url' => $page['url'],
					':description' => $page['description'],
					':controller' => $page['controller']
				));

//				echo "Record updated successfully";
			} catch(PDOException $e) {
//				echo "<br>" . $e->getMessage();
			}
		}
	}
}