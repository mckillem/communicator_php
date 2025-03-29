<?php

declare(strict_types=1);

namespace ItNetwork\Utility;

use DateTime;
use Exception;
use InvalidArgumentException;

/**
 * Třída pro formátování data a času
 */
class DateTimeUtility
{
	/**
	 * Formát datum a čas
	 */
	const string DATETIME_FORMAT = 'j.n.Y G:i:s';
	/**
	 * Formát datum
	 */
	const string DATE_FORMAT = 'j.n.Y';
	/**
	 * Formát čas
	 */
	const string TIME_FORMAT = 'G:i:s';
	/**
	 * Databázový formát datum a čas
	 */
	const string DB_DATETIME_FORMAT = 'Y-m-d H:i:s';
	/**
	 * Databázový formát datum
	 */
	const string DB_DATE_FORMAT = 'Y-m-d';
	/**
	 * Databázový formát čas
	 */
	const string DB_TIME_FORMAT = 'H:i:s';
	/**
	 * @var array České názvy měsíců
	 */
	private static array $months = array('ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');
	/**
	 * @var array Chybové hlášky
	 */
	private static array $errorMessages = array(
		self::DATE_FORMAT => 'Neplatné datum, zadejte ho prosím ve tvaru dd.mm.rrrr',
		self::TIME_FORMAT => 'Neplatný čas, zadejte ho prosím ve tvaru hh:mm, můžete dodat i vteřiny',
		self::DATETIME_FORMAT => 'Neplatné datum nebo čas, zadejte prosím hodnotu ve tvaru dd.mm.rrrr hh:mm, případně vteřiny',
	);
	/**
	 * @var array Slovník pro převod mezi českým a databázovým formátem
	 */
	private static array $formatDictionary = array(
		self::DATE_FORMAT => self::DB_DATE_FORMAT,
		self::DATETIME_FORMAT => self::DB_DATETIME_FORMAT,
		self::TIME_FORMAT => self::DB_TIME_FORMAT,
	);

	/**
	 * Vytvoří instanci DateTime z daného vstupu. Podporuje UNIX timestamp
	 * @param string $date Řetězec s datem, případně časem
	 * @return DateTime Instance DateTime
	 * @throws Exception
	 */
	public static function getDateTime(string $date): DateTime
	{
		if (ctype_digit($date))
			$date = '@' . $date;
		return new DateTime($date);
	}

	/**
	 * Zformátuje datum z libovolné stringové podoby
	 * @param string $date Datum ke zformátování
	 * @return string Zformátované datum
	 * @throws Exception
	 */
	public static function formatDate(string $date): string
	{
		$dateTime = self::getDateTime($date);
		return $dateTime->format('d.m.Y');
	}

	/**
	 * Zformátuje datum a čas z libovolné stringové podoby
	 * @param string $date Datum a čas ke zformátování
	 * @return string Zformátované datum
	 * @throws Exception
	 */
	public static function formatDateTime(string $date): string
	{
		$dateTime = self::getDateTime($date);
		return $dateTime->format('d.m.Y H:i:s');
	}

	/**
	 * Zformátuje instanci DateTime na formát např. "Dnes".
	 * @param DateTime $dateTime Instance DateTime
	 * @return string Zformátovaná hodnota
	 */
	private static function getPrettyDate(DateTime $dateTime): string
	{
		$now = new DateTime();
		if ($dateTime->format('Y') != $now->format('Y'))
			return $dateTime->format('j.n.Y');

		$dayMonth = $dateTime->format('d-m');
		$now->modify('-2 DAY');
		if ($dayMonth == $now->format('d-m'))
			return "Předevčírem";

		$now->modify('+1 DAY');
		if ($dayMonth == $now->format('d-m'))
			return "Včera";

		$now->modify('+1 DAY');
		if ($dayMonth == $now->format('d-m'))
			return "Dnes";

		$now->modify('+1 DAY');
		if ($dayMonth == $now->format('d-m'))
			return "Zítra";

		$now->modify('+1 DAY');
		if ($dayMonth == $now->format('d-m'))
			return "Pozítří";

		return $dateTime->format('j.') . self::$months[$dateTime->format('n') - 1];
	}

	/**
	 * Zformátuje datum z libovolné stringové podoby na tvar např. "Dnes"
	 * @param string $date Datum ke zformátování
	 * @return string Zformátované datum
	 * @throws Exception
	 */
	public static function prettyDate(string $date): string
	{
		return self::getPrettyDate(self::getDateTime($date));
	}

	/**
	 * Zformátuje datum a čas z libovolné stringové podoby na tvar např. "Dnes 15:21"
	 * @param string $date Datum ke zformátování
	 * @return string Zformátované datum
	 * @throws Exception
	 */
	public static function prettyDateTime(string $date): string
	{
		$dateTime = self::getDateTime($date);
		return self::getPrettyDate($dateTime) . $dateTime->format(' H:i:s');
	}

	/**
	 * Naparsuje české datum a čas podle formátu
	 * @param string $date Datum a čas
	 * @param string $format Formát
	 * @return string Datum a čas v databázovém formátu
	 * @throws InvalidArgumentException
	 */
	public static function parseDateTime(string $date, string $format = self::DATETIME_FORMAT): string
	{
		// V metodě nejprve k řetězci s datem přidáme 00 sekund v
		//případě, že je v něm jedna dvojtečka (uživatel zadává čas a nezadal
		//vteřiny).
		if (mb_substr_count($date, ':') == 1)
			$date .= ':00';
		// Smaže mezery před nebo za separátory
		$a = array('/([\.\:\/])\s+/', '/\s+([\.\:\/])/', '/\s{2,}/');
		$b = array('\1', '\1', ' ');
		$date = trim(preg_replace($a, $b, $date));
		// Smaže nuly před čísly
		$a = array('/^0(\d+)/', '/([\.\/])0(\d+)/');
		$b = array('\1', '\1\2');
		$date = preg_replace($a, $b, $date);
		// Vytvoří instanci DateTime, která zkontroluje zda zadané datum existuje
		$dateTime = DateTime::createFromFormat($format, $date);
		$errors = DateTime::getLastErrors();
		// Vyvolání chyby
		//Instanci DateTime sdělíme formát a předáme ji naše datum.
//Ona se ho pokusí naparsovat. Jakékoli chyby nám vrátí v poli pomocí metody
//getLastErrors(). Pokud se zde nějaké objeví, vyhodíme výjimku
//s hláškou podle formátu, v opačném případě vrátíme datum v
//příslušném databázovém formátu. Výjimku můžete poté chytat někde
//při zpracovávání údajů a zobrazit ji uživateli. Za tímto účelem by
//bylo ideální vytvořit si nějakou svou.
		if ($errors && $errors['warning_count'] + $errors['error_count'] > 0) {
			if (array_key_exists($format, self::$errorMessages))
				throw new InvalidArgumentException(self::$errorMessages[$format]);
			else
				throw new InvalidArgumentException('Neplatná hodnota');
		}
		// Návrat data v MySQL formátu
		return $dateTime->format(self::$formatDictionary[$format]);
	}

	/**
	 * Zjistí, zda je dané datum a čas validní
	 * @param string $date Datum a čas
	 * @param string $format Formát data a času
	 * @return bool Zda je hodnota validní
	 */
	public static function validDate(string $date, string $format = self::DATETIME_FORMAT): bool
	{
		try {
			self::parseDateTime($date, $format);
			return true;
		} catch (InvalidArgumentException $e) {}
		return false;
	}

	/**
	 * Vrací aktuální datum v DB podobě
	 * @return string Datum v DB podobě
	 */
	//		Jelikož MySQL neumí pří vkládání nového řádku u formátu
//DateTime dosadit aktuální datum/čas, používám často ještě
//následující metodu, která vrátí současné datum a čas v databázovém
//formátu:
	public static function dbNow()
	{
//		Můžete si o mě myslet, že jsem barbar, protože nepoužívám nativní
//funkci NOW(), ale tuto pihu na kráse mi několikanásobně
//vyváží výhody vložení řádku do databáze přímo z PHP pole, které mi
//mimochodem přijde přímo z formuláře, dotaz se sám generuje a ušetří to
//spoustu práce. Pokud jste pracovali s místním databázovým wrapperem nad
//PDO, tak víte o čem mluvím. Pokud ne, dostaneme se k tomu u motivace k
//formulářové knihovně.
		$dateTime = new DateTime();
		return $dateTime->format(self::DB_DATETIME_FORMAT);
	}
}