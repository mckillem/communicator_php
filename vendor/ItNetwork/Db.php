<?php

namespace ItNetwork;

use ItNetwork\Utility\ArrayUtility;
use PDO;

/**
 * Wrapper pro snadnější práci s databází s použitím PDO a automatickým
 * zabezpečením parametrů (proměnných) v dotazech.
 */
class Db {
	/**
	 * @var PDO Databázové spojení
	 */
	private static PDO $connection;

	/**
	 * @var array Výchozí nastavení ovladače
	 */
	private static array $settings = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
		PDO::ATTR_EMULATE_PREPARES => false,
	);

	/**
	 * Připojí se k databázi pomocí daných údajů
	 * @param string $host Hostitel
	 * @param string $user Uživatelské jméno
	 * @param string $password Heslo
	 * @param string $database Název databáze
	 */
	public static function connect(#[\SensitiveParameter] string $host, #[\SensitiveParameter] string $user, #[\SensitiveParameter] string $password, #[\SensitiveParameter] string $database): void
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

	/**
	 * Spustí dotaz a vrátí z něj první řádek
	 * @param string $query Dotaz
	 * @param array $parameters Parametry
	 * @return array|false Pole výsledku nebo false
	 */
	public static function queryOne(string $query, array $parameters = array()): false|array
	{
		$statement = self::$connection->prepare($query);
		$statement->execute($parameters);

		return $statement->fetch();
	}

	/**
	 * Spustí dotaz a vrátí z něj všechny řádky
	 * @param string $query Dotaz
	 * @param array $parameters Parametry
	 * @return array|false Pole výsledků nebo false
	 */
	public static function queryAll(string $query, array $parameters = array()): false|array
	{
		$statement = self::$connection->prepare($query);
		$statement->execute($parameters);

		return $statement->fetchAll();
	}

	/**
	 * Spustí dotaz a vrátí z něj první sloupec prvního řádku
	 * @param string $query Dotaz
	 * @param array $parameters Parametry
	 * @return string|false První hodnota výsledku dotazu nebo false
	 */
	public static function querySingle(string $query, array $parameters = array()): false|string
	{
		$statement = self::queryOne($query, $parameters);

		return $statement ? $statement[0] : false;
	}

	/**
	 * Spustí dotaz a vrátí počet ovlivněných řádků
	 * @param string $query Dotaz
	 * @param array $parameters Parametry
	 * @return int Počet ovlivněných řádků
	 */
	public static function query(string $query, array $parameters = array()): int
	{
		$statement = self::$connection->prepare($query);
		$statement->execute($parameters);

		return $statement->rowCount();
	}

	/**
	 * Vloží do tabulky nový řádek jako data z asociativního pole
	 * @param string $table Název tabulky
	 * @param array $parameters Asociativní pole s daty
	 * @return int Počet ovlivněných řádků
	 */
	public static function insert(string $table, array $parameters = array()): int
	{
		return self::query("INSERT INTO `$table` (`".
			implode('`, `', array_keys($parameters)).
			"`) VALUES (".str_repeat('?,', sizeOf($parameters)-1).
			"?)", array_values($parameters));
	}

	/**
	 * Změní řádek v tabulce tak, aby obsahoval data z asociativního pole
	 * @param string $table Název tabulky
	 * @param array $values Asociativní pole s daty
	 * @param string $condition Část SQL dotazu s podmínkou včetně WHERE
	 * @param array $parameters Parametry dotazu
	 * @return int Počet ovlivněných řádků
	 */
	public static function update(string $table, array $values, string $condition, array $parameters = array()): int
	{
		return self::query("UPDATE `$table` SET `".
			implode('` = ?, `', array_keys($values)).
			"` = ? " . $condition,
			array_merge(array_values($values), $parameters));
	}

	/**
	 * @return string Vrací ID posledně vloženého záznamu
	 */
	public static function getLastId(): string
	{
		return self::$connection->lastInsertId();
	}

//	todo: phpdocs a typy proměných
	public static function queryPairs($query, $keyColumn, $valueColumn, $parameters = array()): array
	{
		return ArrayUtility::mapPairs(self::queryAll($query, $parameters), $keyColumn, $valueColumn);
	}

	public static function insertAll($table, $parameters = array()): int
	{
		$params = array();
		$query = rtrim("INSERT INTO `$table` (`".
			implode('`, `', array_keys($parameters[0])).
			"`) VALUES " . str_repeat('(' . str_repeat('?,', sizeOf($parameters[0])-1)."?), ", sizeOf($parameters)), ', ');
		foreach ($parameters as $rows)
		{
			$params = array_merge($params, array_values($rows));
		}

		return self::query($query, $params);
	}

	/**
	 * Započne transakci
	 */
	public static function beginTransaction(): void
	{
		self::$connection->beginTransaction();
	}

	/**
	 * Dokončí transakci
	 */
	public static function commit(): void
	{
		self::$connection->commit();
	}

	/**
	 * Stornuje transakci
	 */
	public static function rollBack(): void
	{
		self::$connection->rollBack();
	}
}