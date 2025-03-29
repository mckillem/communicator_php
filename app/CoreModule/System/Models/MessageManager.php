<?php

namespace app\CoreModule\System\models;

class MessageManager
{
	public function returnMessage(string $id): array|false
	{
		return \ItNetwork\Db::queryOne('
			SELECT *
			FROM `message`
			JOIN `message_item` on `order`.`order_id` = ?
			JOIN `item` on `message_item`.`item_id`
			where `message_item`.`item_id` = `item`.`item_id`
		', array($id));
	}

	public function getAllMessages()
	{
		// Připravit SQL dotaz
// Vybrat vše z tabulky "chat", řadit sestupně podle sloupce pro čas
//		$query = $pdo->prepare("SELECT * FROM `messages` ORDER BY `time` DESC");

// Vykonat připravený dotaz
//		$query->execute();

// Získat všechny řádky z dotazu
//		$messages = $query->fetchAll();

// Iterovat přes každý řádek a vypsat jej
//		foreach ($messages as $message) {
//			echo "<p>(" . date("d. m. H:i:s", $message["time"]) . ") <b>" . htmlspecialchars($message["username"]) . "</b>: " . htmlspecialchars($message["message"]) . "</p>";
//		}

		return \ItNetwork\Db::queryAll("SELECT * FROM `message`");
	}

	public function sendMessage(array $data): bool
	{
		// Ověření, jestli není jméno nebo zpráva prázdná
		if (!empty($_POST["username"]) && !empty($_POST["message"])) {

			// Inicializovat sezení
			session_start();

			// Vytvořit spojení s databází ze souboru db.php
			require "db.php";

			// Připravit SQL dotaz
			$query = $pdo->prepare("INSERT INTO `messages` (`username`, `message`, `time`) VALUES (?, ?, ?)");

			// Vykonat dotaz s parametry
			$query->execute([
				$_POST["username"],
				$_POST["message"],
				time() // Aktuální čas v unixovém formátu
			]);

			// Nastavit jméno do sezení pro zapamatování
			$_SESSION["username"] = htmlspecialchars($_POST["username"]);
		}

		return \ItNetwork\Db::insert('message', $data);
	}
}