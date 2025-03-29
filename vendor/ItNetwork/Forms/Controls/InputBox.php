<?php

namespace ItNetwork\Forms\Controls;

use ItNetwork\Forms\FormControl;
use ItNetwork\HtmlBuilder;

class InputBox extends FormControl
{
	/**
	 * @var string Zadaný text
	 */
	private string $text = '';

	/**
	 * Inicializuje instanci
	 * @param string $name Název kontrolky
	 * @param string $type Typ inputu
	 * @param string $label Popisek
	 * @param array $htmlParams HTML parametry
	 */
	public function __construct(string $name, public string $type, string $label = '', array $htmlParams = array())
	{
		parent::__construct($name, $label, $htmlParams);
	}

	/**
	 * Vrátí HTML kód kontrolky
	 * @param bool $isPostBack Zda byl odeslán formulář (tedy zda jsou v superproměnné POST nějaká data)
	 * @return string HTML kód
	 */
	public function renderControl(bool $isPostBack): string
	{
		$value = ($isPostBack && $this->sentDataKeyExists($this->name) && $this->type != 'password') ? $this->getSentData($this->name) : $this->text;
		$this->htmlParams['value'] = $value;
		$this->htmlParams['type'] = $this->type;

		$builder = new HtmlBuilder();
		$builder->addElement('input', $this->htmlParams);

		return $builder->render();
	}

	/**
	 * Nastaví zadaný text
	 * @param string $text Text
	 * @return InputBox $this Kontrolka pro další použití
	 */
	public function setText(string $text): InputBox
	{
		$this->text = $text;

		return $this;
	}

	/**
	 * Nastaví kontrolce data
	 * @param string $key Klíč, zde se nepoužívá
	 * @param string $text Zadaný text
	 */
//	todo: mu tu být otazník a v podmínce $text?
	public function setData(string $key, ?string $text): void
	{
		if ($this->type != 'password' && $text)
			$this->text = $text;
	}
}
