<?php

namespace ItNetwork\Forms\Controls;

use ItNetwork\Forms\FormControl;
use ItNetwork\HtmlBuilder;

class TextArea extends FormControl
{
	/**
	 * @var string Zadaný text
	 */
	private string $text = '';

	/**
	 * Vrátí HTML kód kontrolky
	 * @param bool $isPostBack Zda byl odeslán formulář
	 * @return string HTML kód
	 */
	public function renderControl(bool $isPostBack): string
	{
		$value = ($isPostBack && $this->sentDataKeyExists($this->name)) ? $this->getSentData($this->name) : $this->text;

		$builder = new HtmlBuilder();
		$builder->addValueElement('textarea', $value, $this->htmlParams);

		return $builder->render();
	}

	/**
	 * Nastaví zadaný text
	 * @param string $text Text
	 * @return TextArea $this textArea
	 */
	public function setText(string $text): TextArea
	{
		$this->text = $text;

		return $this;
	}

	/**
	 * Nastaví data kontrolce
	 * @param string $key Klíč, zde se nevyužívá
	 * @param string $text Hodnota
	 */
	public function setData(string $key, string $text): void
	{
		$this->text = $text;
	}
}