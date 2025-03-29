<?php

namespace ItNetwork\Forms\Controls;

use DateTime;
use ItNetwork\Forms\FormControl;
use ItNetwork\HtmlBuilder;
use ItNetwork\UserException;
use ItNetwork\Utility\DateTimeUtility;
class DateTimePicker extends FormControl
{
	/**
	 * @var string Zadaná hodnota
	 */
	private string $value = '';

	/**
	 * Inicializuje instanci
	 * @param string $name Název kontrolky
	 * @param string $format Formát data/času
	 * @param string $label Popisek
	 * @param array $htmlParams HTML parametru
	 */
	public function __construct(string $name, private string $format, string $label = '', array $htmlParams = array())
	{
//		todo: https://www.itnetwork.cz/diskuze/php/formularovy-framework-v-php/php-tutorial-formularovy-framework-datetimepicker
		if ($format == DateTimeUtility::DATE_FORMAT)
			$this->addClass('form-datepicker');
		parent::__construct($name, $label, $htmlParams);
	}

	/**
	 * Vrátí HTML kód kontrolky
	 * @param bool $isPostBack Zda byl formulář odeslán
	 * @return string HTML kód
	 */
	public function renderControl(bool $isPostBack): string
	{
		$value = ($isPostBack && $this->sentDataKeyExists($this->name)) ? $this->getSentData($this->name) : $this->value;

		$this->htmlParams['value'] = $value;
//		todo: V renderovací metodě je zajímavé asi jen to, že picker renderujeme jako
//pouhý input typu text. Podpora pro zadávání data a času je
//totiž v prohlížečích ještě stále nedostatečná, a hlavně
//nestandardizovaná.
//Zdroj: https://www.itnetwork.cz/php/formularovy-framework-v-php/php-tutorial-formularovy-framework-datetimepicker
		$this->htmlParams['type'] = 'text';

		$builder = new HtmlBuilder();
		$builder->addElement('input', $this->htmlParams);

		return $builder->render();
	}

	/**
	 * Vrátí data v kontrolce
	 * @throws UserException
	 * @return array Data
	 */
	public function getData(): array
	{
		try {
			return $this->sentDataKeyExists($this->name) && $this->getSentData($this->name) ? array($this->name => DateTimeUtility::parseDateTime($this->getSentData($this->name), $this->format)) : array();
		} catch (\InvalidArgumentException $ex) {
			throw new UserException($ex->getMessage());
		}
	}

	/**
	 * Nastaví zadanou hodnotu
	 * @param string $value Hodnota
	 * @return DateTimePicker $this DateTimePicker
	 */
	public function setValue(string $value): DateTimePicker
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * Nastaví data kontrolce
	 * @param string $key Klíč se zde nevyužívá
	 * @param string $value Zadaná hodnota
	 */
	public function setData(string $key, string $value): void
	{
		if ($this->format == DateTimeUtility::TIME_FORMAT)
			$date = DateTime::createFromFormat(DateTimeUtility::DB_TIME_FORMAT, $value);
		else if ($this->format == DateTimeUtility::DATE_FORMAT)
			$date = DateTime::createFromFormat(DateTimeUtility::DB_DATE_FORMAT, $value);
		else
			$date = DateTime::createFromFormat(DateTimeUtility::DB_DATETIME_FORMAT, $value);
//todo: podmínka tu nedává smysl
		if ($date)
			$this->value = $date->format($this->format);
	}
}
