<?php

namespace ItNetwork\Forms\Controls;

use ItNetwork\Forms\FormControl;
use ItNetwork\HtmlBuilder;

class CheckBox extends FormControl
{
	/**
	 * @var bool Zda je zaškrtnutý
	 */
	private bool $checked;
	/**
	 * @var string Titulek vedle CheckBoxu
	 */
	private string $title;

	/**
	 * Inicializuje instanci
	 * @param string $name Název
	 * @param string $label Titulek vedle CheckBoxu
	 * @param array $htmlParams HTML parametry
	 */
	public function __construct(string $name, string $label = '', array $htmlParams = array())
	{
		$this->title = $label;
		$htmlParams['type'] = 'checkbox';
		$htmlParams['value'] = 1;
		parent::__construct($name, '&nbsp;', $htmlParams);
	}

	/**
	 * Vyrenderuje CheckBox
	 * @param bool $isPostBack Zda byl odeslaný formulář
	 * @return string Výsledné HTML
	 */
	public function renderControl(bool $isPostBack): string
	{
		if (($isPostBack) && ($this->sentDataKeyExists($this->name) && $this->getSentData($this->name)) || ((!$isPostBack) && ($this->checked)))
			$this->htmlParams['checked'] = 'checked';

		$builder = new HtmlBuilder();
		$builder->addElement('input', $this->htmlParams);
		// Label za checkboxem
		$builder->addValueElement('label', $this->title, array(
			'for' => $this->htmlParams['id'],
		));

		return $builder->render();
	}

	/**
	 * Nastaví zda má být CheckBox zaškrtnutý
	 * @param bool $checked Zda je CheckBox zaškrtnutý
	 * @return $this CheckBox CheckBox
	 */
	public function setChecked(bool $checked): CheckBox
	{
		$this->checked = $checked;

		return $this;
	}

	/**
	 * Vrátí data z CheckBoxu
	 * @return array Data
	 */
	public function getData(): array
	{
		return array($this->name => (int)($this->sentDataKeyExists($this->name) && $this->getSentData($this->name)));
	}

	/**
	 * Nastaví data CheckBoxu
	 * @param string $key Klíč
	 * @param bool $checked Zda má být zaškrtnutý
	 */
	public function setData(string $key, $checked): void
	{
		$this->checked = (bool)$checked;
	}
}