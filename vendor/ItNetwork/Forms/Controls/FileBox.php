<?php

namespace ItNetwork\Forms\Controls;

use ItNetwork\Forms\FormControl;
use ItNetwork\HtmlBuilder;
use ItNetwork\UserException;

class FileBox extends FormControl
{
	/**
	 * Inicializuje instanci
	 * @param string $name Název pole
	 * @param bool $required Zda je nahrání souboru povinné
	 * @param bool $multiple Zda lze vybrat více souborů
	 * @param string $label Popisek pole
	 * @param array $htmlParams HTML parametry
	 */
	public function __construct(string $name, private bool $required, bool $multiple, string $label = '', array $htmlParams = array())
	{
		parent::__construct($name, $label, $htmlParams);
		if ($required)
			$this->htmlParams['required'] = 'required';
		if ($multiple) {
			$this->htmlParams['multiple'] = 'multiple';
			$this->htmlParams['name'] .= '[]';
		}
	}

	/**
	 * Vrátí data z kontrolky
	 * @throws UserException
	 * @return array Data
	 */
	public function getData(): array
	{
		if ($this->required && empty($_FILES[$this->name]['name'][0]))
			throw new UserException('Je nutné vybrat soubor.');

		return !empty($_FILES[$this->name]['name']) ? array($this->name => $_FILES[$this->name]) : array($this->name => '');
	}

	/**
	 * Nastaví data kontrolce, v tomto případě se nenastavuje nic
	 * @param string $key Klíč
	 * @param string $value hodnota
	 */
	public function setData(string $key, string $value): void
	{
		// prázdné
	}

	/**
	 * Vrátí HTML kód kontrolky
	 * @param bool $isPostBack Zda byl odeslaný formulář
	 * @return mixed
	 */
	public function renderControl(bool $isPostBack): string
	{
		$this->htmlParams['type'] = 'file';
		$builder = new HtmlBuilder();
		$builder->addElement('input', $this->htmlParams);

		return $builder->render();
	}
}