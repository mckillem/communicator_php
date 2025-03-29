<?php

namespace ItNetwork\Forms;

use ItNetwork\UserException;
use ItNetwork\Utility\DateTimeUtility;
use ItNetwork\Utility\StringUtility;

abstract class FormControl
{
	/**
	 * Regulární výraz pro URL
	 */
	const string PATTERN_URL = '(http|https)://.*';
	/**
	 * Regulární výraz pro celá čísla
	 */
	const string PATTERN_INTEGER = '[0-9]+';
	/**
	 * Reglární výraz pro email
	 */
	const string PATTERN_EMAIL = '[a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,4}$';
	/**
	 * @var array Kolekce validačních pravidel
	 */
	private array $rules = array();
	/**
	 * @var bool Zda je hodnota v kontrolce validní
	 */
	public bool $invalid;
	/**
	 * @var Method HTTP metoda pro čtení odeslaných dat
	 */
	private Method $method = Method::Post;

	/**
	 * Inicializuje instanci
	 * @param string $name Název kontrolky. Neobsahuje [] v případě multiple hodnot.
	 * @param string $label Popisek u kontrolky
	 * @param array $htmlParams HTML parametry kontrolky
	 */
	public function __construct(public string $name, public string $label = '', public array $htmlParams = array())
	{
		$this->htmlParams['name'] = $name;
		$this->htmlParams['id'] = $name;
	}

	/**
	 * Nastaví tooltip, což je v HTML parametr title
	 * @param string $toolTip Tooltip
	 * @return FormControl $this FormControl
	 */
	public function setTooltip(string $toolTip): FormControl
	{
		$this->htmlParams['title'] = $toolTip;

		return $this;
	}

	/**
	 * Přidá kontrolce CSS třídu
	 * @param string $class CSS třída
	 */
	public function addClass(string $class): void
	{
		if (isset($this->htmlParams['class']))
			$this->htmlParams['class'] .= ' ' . $class;
		else
			$this->htmlParams['class'] = $class;
	}

	/**
	 * Přidá validační pravidlo
	 * @param array $rule Pravidlo
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	private function addRule(array $rule, bool $validateClient, bool $validateServer): FormControl
	{
		$rule['validate_client'] = $validateClient;
		$rule['validate_server'] = $validateServer;
		$this->rules[] = $rule;

		return $this;
	}

	/**
	 * Přidá pravidlo pro povinné pole
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addRequiredRule(bool $validateClient = true, bool $validateServer = true): FormControl
	{
		return $this->addRule(array(
			'type' => Rules::Required,
			'message' => 'Povinné pole',
		), $validateClient, $validateServer);
	}

	/**
	 * Přidá pravidlo pro maximální délku
	 * @param int $maxLength Maximální délka hodnoty
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addMaxLengthRule(int $maxLength, bool $validateClient = true, bool $validateServer = true): FormControl
	{
		return $this->addRule(array(
			'type' => Rules::MaxLength,
			'max_length' => $maxLength,
			'message' => 'Maximální délka hodnoty je ' . $maxLength,
		), $validateClient, $validateServer);
	}

	/**
	 * Přidá pravidlo pro regulární výraz
	 * @param string $pattern Regulární výraz
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addPatternRule(string $pattern, bool $validateClient = true, bool $validateServer = true): FormControl
	{
		return $this->addRule(array(
			'type' => Rules::Pattern,
			'pattern' => $pattern,
			'message' => 'Hodnota má nesprávný formát',
		), $validateClient, $validateServer);
	}

	/**
	 * Přidá pravidlo pro minimální délku
	 * @param int $minLength Minimální délka
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addMinLengthRule(int $minLength, bool $validateClient = true, bool $validateServer = true): FormControl
	{
		return $this->addPatternRule('.{' . $minLength . ',}', $validateClient, $validateServer);
	}

	/**
	 * Přidá pravidlo pro heslo
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addPasswordRule(bool $validateClient = true, bool $validateServer = true): FormControl
	{
		$this->addMinLengthRule(6, $validateClient);

		return $this->addRule(array(
			'type' => Rules::Password,
			'message' => 'Heslo nesmí začínat nebo končit mezerou a musí být dlouhé alespoň 6 znaků.',
		), $validateClient, $validateServer);
	}

	/**
	 * Přidá pravidlo pro datum a čas
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addDateTimeRule(bool $validateClient = true, bool $validateServer = true): FormControl
	{
		$this->addPatternRule('[0-3]?[0-9]\.[0-1]?[0-9]\.[0-9]{4}\s[0-2]?[0-9]\:[0-5]?[0-9](\:[0-5]?[0-9])?');

		return $this->addRule(array(
			'type' => Rules::DateTime,
			'format' => DateTimeUtility::DATETIME_FORMAT,
			'message' => 'Hodnota musí být ve formátu: dd.mm.yyyy hh:mm(:ss)',
		), $validateClient, $validateServer);
	}

	/**
	 * Přidá pravidlo pro datum
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addDateRule(bool $validateClient = true, bool $validateServer = true): FormControl
	{
		$this->addPatternRule('[0-3]?[0-9]\.[0-1]?[0-9]\.[0-9]{4}');

		return $this->addRule(array(
			'type' => Rules::DateTime,
			'format' => DateTimeUtility::DATE_FORMAT,
			'message' => 'Hodnota musí být ve formátu: dd.mm.yyyy',
		), $validateClient, $validateServer);
	}

	/**
	 * Přidá pravidlo pro čas
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addTimeRule(bool $validateClient = true, bool $validateServer = true): FormControl
	{
		$this->addPatternRule('[0-2]?[0-9]\:[0-5]?[0-9](\:[0-5]?[0-9])?');

		return $this->addRule(array(
			'type' => Rules::DateTime,
			'format' => DateTimeUtility::TIME_FORMAT,
			'message' => 'Hodnota musí být ve formátu: hh:mm(:ss)',
		), $validateClient, $validateServer);
	}

	/**
	 * Přidá pravidlo pro povinný soubor
	 * @param bool $validateClient Zda validovat na klientovi
	 * @param bool $validateServer Zda validovat na serveru
	 * @return FormControl $this Kontrolka pro další zpracování
	 */
	public function addFileRequiredRule(bool $validateClient = true, bool $validateServer = true): FormControl
	{
		return $this->addRule(array(
			'type' => Rules::RequiredFile,
			'message' => 'Soubor je povinný',
		), $validateClient, $validateServer);
	}

	/**
	 * Přidá do HTML parametrů klientské validace
	 */
	public function addClientParams(): void
	{
		foreach ($this->rules as $rule) {
			if ($rule['validate_client']) {
				switch ($rule['type']) {
					case Rules::Required:
					case Rules::RequiredFile:
						$this->htmlParams['required'] = 'required';
						break;
					case Rules::MaxLength:
						$this->htmlParams['maxlength'] = $rule['max_length'];
						break;
					case Rules::Pattern:
						if (!isset($this->htmlParams['pattern']))
							$this->htmlParams['pattern'] = $rule['pattern'];
						break;
				}
			}
		}
	}

	/**
	 * Aplikuje na kontrolku dané validační pravidlo
	 * @param array $rule Pravidlo
	 * @return bool Zda pravidlo platí
	 */
	private function checkRule(array $rule): bool
	{
		$name = $this->name;
		switch ($rule['type']) {
			case Rules::Required:
				return $this->sentDataKeyExists($name) && (is_numeric($this->getSentData($name)) || $this->getSentData($name));
			case Rules::MaxLength:
				return !$this->sentDataKeyExists($name) || !$this->getSentData($name) || mb_strlen($this->getSentData($name)) <= $rule['max_length'];
			case Rules::Pattern:
				return !$this->sentDataKeyExists($name) || !$this->getSentData($name) || preg_match('~^' . $rule['pattern'] . '$~u', $this->getSentData($name));
			case Rules::RequiredFile:
				return isset($_FILES[$name]) && isset($_FILES[$name]['name']) && $_FILES[$name]['name'];
			case Rules::DateTime:
				return !$this->sentDataKeyExists($name) || !$this->getSentData($name) || DateTimeUtility::validDate($this->getSentData($name), $rule['format']);
			case Rules::Password:
//				todo: jak tedy?
//				return !$this->sentDataKeyExists($name) || !$this->getSentData($name) || ((trim($this->getSentData($name)) == $this->getSentData($name)) && (mb_strlen($this->getSentData($name)) >= 6));
//				return !isset($_POST[$name]) || !$_POST[$name] || ((StringUtility::removeAccents($_POST[$name]) == $_POST[$name]) && (mb_strlen($_POST[$name]) >= 6));
				return !$this->sentDataKeyExists($name) || !$this->getSentData($name) || ((StringUtility::removeAccents($this->getSentData($name)) === $this->getSentData($name)) && (mb_strlen($this->getSentData($name)) >= 6));
		}

		return false;
	}

	/**
	 * Zvaliduje hodnotu podle pravidel
	 * @throws UserException Pokud hodnota není validní
	 * @return bool TRUE, je-li hodnota validní
	 */
	public function checkValidity(): bool
	{
		foreach ($this->rules as $rule) {
			if (($rule['validate_server']) && (!$this->checkRule($rule))) {
				$this->invalid = true;
				$this->addClass('invalid');
				throw new UserException($rule['message']);
			}
		}

		return true;
	}

	/**
	 * Vrátí HTML kód kontrolky
	 * @param bool $isPostBack Zda byl odeslaný formulář
	 * @return string HTML kód
	 */
	protected abstract function renderControl(bool $isPostBack): string;

	/**
	 * Vrátí HTML kód kontrolky včetně klientských validací
	 * @param bool $validateClient Zda mají být přítomné klientské validace
	 * @param bool $isPostBack Zda byl odeslán formulář
	 * @return string HTML kód kontrolky
	 */
	public function render(bool $validateClient, bool $isPostBack): string
	{
		if ($validateClient)
			$this->addClientParams();

		return $this->renderControl($isPostBack);
	}

	/**
	 * Vrátí data z kontrolky pro další zpracování ve formuláři
	 * @return array Data
	 */
	public function getData(): array
	{
		return $this->sentDataKeyExists($this->name) ? array($this->name => $this->getSentData($this->name)) : array();
	}

	/**
	 * Vrátí klíče k datům v kontrolce
	 * @return array Klíče
	 */
	public function getKeys(): array
	{
		return array($this->name);
	}

	/**
	 * Nastaví kontrolce data
	 * @param string $key klíč
	 * @param string $value Hodnota
	 */
	public abstract function setData(string $key, string $value);

	/**
	 * Nastaví metodu, kterou byla odeslána data
	 * @param Method $method Metoda odeslání dat
	 */
	public function setMethod(Method $method): void
	{
		$this->method = $method;
	}

	/**
	 * Vrátí data odeslaná nastavenou HTTP metodou
	 * @param string $key Klíč, jehož data chceme zobrazit. Pokud není zadán, vrátí se celé pole.
	 * @return string|array Odeslaná data
	 */
	public function getSentData(string $key = ''): string|array
	{
		if ($key)
			return ($this->method == Method::Post) ? $_POST[$key] : $_GET[$key];

		return ($this->method == Method::Post) ? $_POST : $_GET;
	}

	/** Zjistí, zda byl v datech formuláře odeslaný určitý klíč
	 * @param string $key Klíč
	 * @return bool Zda byl klíč odeslaný formulářem či nikoli
	 */
	public function sentDataKeyExists(string $key): bool
	{
		return ($this->method == Method::Post) ? isset($_POST[$key]) : isset($_GET[$key]);
	}
}