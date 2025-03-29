<?php

declare(strict_types=1);

namespace ItNetwork\Utility;

use DOMDocument;
use DOMElement;
use DOMException;

class ArrayUtility
{
	/**
	 * Profiltruje klíče pole
	 * @param array $input Vstupní pole
	 * @param array $keys Pole povolených klíčů
	 * @return array Výsledné pole
	 */
	public static function filterKeys(array $input, array $keys): array
	{
		return array_intersect_key($input, array_flip($keys));
	}

	/**
	 * Profiltruje klíče pole tak, aby obsahovalo jen ty se zadaným prefixem
	 * @param string $prefix Prefix
	 * @param array $input Vstupní pole
	 * @return array Výsledné pole
	 */
	public static function filterKeysPrefix(string $prefix, array $input): array
	{
		$output = array();
		foreach ($input as $key => $value) {
			if (mb_strpos($key, $prefix) === 0)
				$output[$key] = $value;
		}

		return $output;
	}

	/**
	 * Namapuje pole řádků (asociativních polí) tak, že je výsledkem jedno asociativní pole, jehož klíče a hodnoty
	 * odpovídají určitým klíčům jednotlivých řádků
	 * @param array $rows Vstupní pole řádků
	 * @param string $keyKey Klíč řádku, který bude klíčem výstupního pole
	 * @param string $valueKey Klíč řádku, který bude hodnotou výstupního pole
	 * @return array Výsledné asociativní pole
	 */
	public static function mapPairs(array $rows, string $keyKey, string $valueKey): array
	{
		$pairs = array();
		foreach ($rows as $row) {
			$key = $row[$keyKey];
			// Kontrola kolizí klíče
			if (isset($pairs[$key])) {
				$i = 1;
				while (isset($pairs[$key . ' (' . $i . ')'])) {
					$i++;
				}
				$key .= ' (' . $i . ')';
			}
			$pairs[$key] = $row[$valueKey];
		}

		return $pairs;
	}

//	todo: má význam, aby $singleKey bylo pole? Dle mě to má být string

	/**
	 * Namapuje pole řádků (asociativních polí) tak, že je výsledkem jedno pole, do kterého jsou vložený hodnoty z řádků
	 * pod daným klíčem
	 * @param array $rows Vstupní pole řádků
	 * @param string $singleKey Název klíče, jehož hodnotu vkládáme do výstupního pole
	 * @return array Výstupní pole hodnot z řádků
	 */
	public static function mapSingles(array $rows, string $singleKey): array
	{
		$singles = array();
		foreach ($rows as $row) {
			$singles[] = $row[$singleKey];
		}

		return $singles;
	}

	/**
	 * Rekurzivně odstraní prefixy klíčů v poli
	 * @param string $prefix Prefix klíče k odstranění
	 * @param array $input Vstupní pole
	 * @return array Výsledné pole
	 */
	public static function removePrefix(string $prefix, array $input): array
	{
		$output = array();
		foreach ($input as $key => $value) {
			if (strpos($key, $prefix) === 0)
				$key = substr($key, mb_strlen($prefix));
			if (is_array($value))
				$value = self::removePrefix($prefix, $value);
			$output[$key] = $value;
		}

		return $output;
	}

	/**
	 * Rekurzivně přidá prefixy klíčům v poli
	 * @param string $prefix Prefix klíče k přidání
	 * @param array $input Vstupní pole
	 * @return array Výsledné pole
	 */
	public static function addPrefix(string $prefix, array $input): array
	{
		$output = array();
		foreach ($input as $key => $value) {
			$key = $prefix . $key;
			if (is_array($value))
				$value = self::addPrefix($prefix, $value);
			$output[$key] = $value;
		}

		return $output;
	}

	/**
	 * Převede velbloudí notaci klíčů pole na podtržítkovou
	 * @param array $inputArray Vstupní pole
	 * @return array Převedené pole
	 */
	public static function camelToSnake(array $inputArray): array
	{
		$outputArray = array();
		foreach ($inputArray as $key => $value) {
			$key = StringUtility::camelToSnake($key);
			if (is_array($value))
				$value = self::camelToSnake($value);
			$outputArray[$key] = $value;
		}

		return $outputArray;
	}

	/**
	 * Převede podtržítkovou notaci klíčů pole na velbloudí
	 * @param array $inputArray Vstupní pole
	 * @return array Převedené pole
	 */
	public static function snakeToCamel(array $inputArray): array
	{
		$outputArray = array();
		foreach ($inputArray as $key => $value) {
			$key = StringUtility::snakeToCamel($key);
			if (is_array($value))
				$value = self::snakeToCamel($value);
			$outputArray[$key] = $value;
		}

		return $outputArray;
	}

	/**
	 * Rekurzivně vloží hodnoty z pole jako podelementy do předaného DOMElementu
	 * @param array $input Vstupní pole
	 * @param DOMElement $parent Rodičovský DOMElement
	 * @return void DOMElement v $parent s přidanými podelementy
	 * @throws DOMException
	 */
	private static function xmlEncodeElement(array $input, DOMElement $parent): void
	{
		foreach ($input as $key => $value) {
			$element = $parent->ownerDocument->createElement($key);
			$parent->appendChild($element);
			if (is_array($value))
				self::xmlEncodeElement($value, $element);
			else {
				$text = $parent->ownerDocument->createTextNode($value);
				$element->appendChild($text);
			}
		}
	}

	/**
	 * Převede pole na XML
	 * @param array $input Vstupní pole
	 * @param string $root Název kořenového elementu, ve kterém jsou položky obalené
	 * @return string XML
	 * @throws DOMException
	 */
	public static function xmlEncode(array $input, string $root): string
	{
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;

		$rootElement = $doc->createElement($root);
		$doc->appendChild($rootElement);
		self::xmlEncodeElement($input, $rootElement);

		return $doc->saveXML();
	}

	/**
	 * Převede XML na pole
	 * @param string $xml XML jako textový řetězec
	 * @return array Pole
	 */
	public static function xmlDecode(string $xml): array
	{
		$simpleXMLElement = simplexml_load_string($xml);
		$json = json_encode($simpleXMLElement);

		return json_decode($json, true);
	}
}