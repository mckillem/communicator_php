<?php

namespace ItNetwork\Forms\Controls;

use ItNetwork\Forms\FormControl;
use ItNetwork\HtmlBuilder;

/**
 * Kontrolka s Radiobuttony
 * @package System\Forms
 */
class RadioGroup extends FormControl
{
	/**
	 * @var array Hodnoty
	 */
	public array $values = array();
	/**
	 * @var string Vybrané hodnoty
	 */
	public string $selectedValue = '';
	/**
	 * @var bool Určuje, zda byla vybrána hodnota, protože jen ze samotné hodnoty to nepoznáme, někdo může vybrat
	 * '' nebo 0 nebo null
	 */
	private bool $selectedValueSet = false;

	/**
	 * Nastaví horizontální orientaci
	 * @param bool $horizontal Zda se mají možnosti řadit vedle sebe
	 * @return RadioGroup Kontrolka pro další využítí
	 */
	public function setHorizontal(bool $horizontal): RadioGroup
	{
		$class = $horizontal ? 'radio-horizontal' : 'radio-vertical';
		$this->addClass($class);

		return $this;
	}

	/**
	 * Nastaví data kontrolce
	 * @param string $key Klíč, zde se nepoužívá
	 * @param string $value Hodnota
	 */
	public function setData(string $key, string $value): void
	{
		$this->selectedValue = $value;
		$this->selectedValueSet = true;
	}

	/**
	 * Nastaví vybranou hodnotu
	 * @param string $value Vybraná hodnota
	 * @return RadioGroup Kontrolka pro další využítí
	 */
	public function setSelectedValue(string $value): RadioGroup
	{
		$this->selectedValue = $value;
		$this->selectedValueSet = true;

		return $this;
	}

	/**
	 * Nastaví hodnoty
	 * @param array $values Asociativní pole hodnot, kde klíče jsou jejich popisky
	 * @return RadioGroup Kontrolka pro další využítí
	 */
	public function setValues(array $values): RadioGroup
	{
		$this->values = $values;

		return $this;
	}

	/**
	 * Vyrenderuje možnosti
	 * @param HtmlBuilder $builder Instance HTML Builderu
	 * @param bool $isPostBack Zda byl formulář odeslán
	 */
	private function renderOptions(HtmlBuilder $builder, bool $isPostBack): void
	{
		if ((!$this->selectedValueSet) && (count($this->values))) {
			$values = array_values($this->values);
			$this->selectedValue = $values[0];
			$this->selectedValueSet = true;
		}
		$i = 0;
		foreach ($this->values as $key => $value) {
			$i++;
			$params = array(
				'name' => $this->name,
				'id' => $this->htmlParams['id'] . $i,
				'value' => $value,
				'type' => 'radio',
			);
			if (($isPostBack && $this->sentDataKeyExists($this->name) && ($this->getSentData($this->name) == $value))
				|| (!$isPostBack && $value == $this->selectedValue))
				$params['checked'] = 'checked';

			$builder->startElement('span');
			$builder->addElement('input', $params);
			$builder->addValueElement('label', $key, array(
				'for' => $this->htmlParams['id'] . $i,
			));
			$builder->endElement();
		}
	}

	/**
	 * Vrátí HTML kód kontrolky
	 * @param bool $isPostBack Zda byl odeslaný formulář
	 * @return string HTML kód
	 */
	public function renderControl(bool $isPostBack): string
	{
		$builder = new HtmlBuilder();

		$builder->startElement('div', $this->htmlParams, true);
		$this->renderOptions($builder, $isPostBack);
		$builder->endElement();

		return $builder->render();
	}
}