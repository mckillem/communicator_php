<?php

namespace ItNetwork\Forms\Controls;

use ItNetwork\Forms\FormControl;
use ItNetwork\HtmlBuilder;

class ListBox extends FormControl
{
	/**
	 * @var array Vybrané hodnoty
	 */
	private array $selectedValues = array();
	/**
	 * @var array Zakázané hodnoty
	 */
	private array $disabledValues = array();
	/**
	 * @var array Hodnoty
	 */
	private array $values = array();

	/**
	 * Inicializuje instanci
	 * @param string $name Název kontrolky
	 * @param bool $required Zda je kontrolka povinná
	 * @param bool $multiple Zda může být vybráno více hodnot
	 * @param string $label Popisek
	 * @param array $htmlParams HTML parametry
	 */
	public function __construct(string $name, private bool $required, bool $multiple, string $label = '', array $htmlParams = array())
	{
		if ($required)
			$this->required = $required;
		parent::__construct($name, $label, $htmlParams);
		if ($multiple) {
			$this->htmlParams['multiple'] = 'multiple';
			$this->htmlParams['name'] .= '[]';
		}
	}

//	todo: https://www.itnetwork.cz/diskuze/php/formularovy-framework-v-php/php-tutorial-formularovy-framework-listbox-a-combobox

	/**
	 * Vrátí data z kontrolky formuláři
	 * @return array Data
	 */
	public function getData(): array
	{
		if (!isset($this->htmlParams['multiple']))
			return $this->sentDataKeyExists($this->name) ? array($this->name => $this->getSentData($this->name)) : array();
		if ($this->sentDataKeyExists($this->name))
			return array($this->name => array_values(array_intersect($this->getSentData($this->name), $this->values)));

		return array();
	}

	/**
	 * Nastaví velikost (počet položek v listu bez scrollbaru)
	 * @param int $size Velikost
	 * @return ListBox $this Kontrolka pro další zpracování
	 */
	public function setSize(int $size): ListBox
	{
		$this->htmlParams['size'] = $size;

		return $this;
	}

	/**
	 * Vrátí klíče pro formulář
	 * @return array Klíče
	 */
	public function getKeys(): array
	{
		return array($this->name);
	}

	/**
	 * Nastaví kontrolce data
	 * @param string $key Klíč, zde se nevyužívá
	 * @param array $values Vybrané hodnoty
	 */
	public function setData(string $key, $values): void
	{
		if (is_array($values))
			$this->setSelectedValues($values);
		else
			$this->setSelectedValue($values);
	}

	/**
	 * Vyrenderuje položky
	 * @param HtmlBuilder $builder instance HhtmlBuilderu
	 * @param bool $isPostBack Zda byl odeslaný formulář
	 */
	private function renderOptions(HtmlBuilder $builder, bool $isPostBack): void
	{
		foreach ($this->values as $key => $value) {
			$params = array(
				'value' => $value,
			);

			$values = array();
			if ($isPostBack && $this->sentDataKeyExists($this->name))
				$values = (is_array($this->getSentData($this->name)) ? $this->getSentData($this->name) : array($this->getSentData($this->name)));
			else if (!$isPostBack)
				$values = $this->selectedValues;
			if (in_array($value, $values))
				$params['selected'] = 'selected';
			if (in_array($value, $this->disabledValues))
				$params['disabled'] = 'disabled';

			$builder->addValueElement('option', $key, $params);
		}
	}

	/**
	 * Nastaví hodnoty
	 * @param array $values Asociativní pole hodnot, kde jsou klíče popisky
	 * @return ListBox Kontrolka pro další použití
	 */
	public function setValues(array $values): ListBox
	{
		$this->values = $values;

		return $this;
	}

	/**
	 * Nastaví vybrané hodnoty. Používá se pro select
	 * @param array $values Hodnoty
	 * @return ListBox Kontrolka pro další použití
	 */
	public function setSelectedValues(array $values): ListBox
	{
		$this->selectedValues = $values;

		return $this;
	}

	/**
	 * Nastaví zakázané hodnoty
	 * @param array $values Zakázané hodnoty
	 * @return ListBox Kontrolka pro další použití
	 */
	public function setDisabledValues(array $values): ListBox
	{
		$this->disabledValues = $values;

		return $this;
	}

	/**
	 * Nastaví vybranou hodnotu. Používá se pro ComboBox.
	 * @param string $value
	 * @return ListBox Kontrolka pro další použití
	 */
	public function setSelectedValue(string $value): ListBox
	{
		$this->selectedValues = array($value);

		return $this;
	}

	/**
	 * Vrátí HTML kód kontrolky
	 * @param bool $isPostBack Zda byl odeslán formulář
	 * @return string HTML kód
	 */
	public function renderControl(bool $isPostBack): string
	{
		$builder = new HtmlBuilder();

		$builder->startElement('select', $this->htmlParams, true);
		if ((!$this->required) && (!isset($this->htmlParams['multiple'])))
			$this->values = array('' => '') + $this->values;
		$this->renderOptions($builder, $isPostBack);
		$builder->endElement();

		return $builder->render();
	}
}