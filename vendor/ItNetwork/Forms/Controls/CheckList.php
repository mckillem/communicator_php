<?php

namespace ItNetwork\Forms\Controls;

use ItNetwork\Forms\FormControl;
use ItNetwork\Forms\FormException;
use ItNetwork\HtmlBuilder;

class CheckList extends FormControl
{
	/**
	 * @var array Vybrané hodnoty
	 */
	private array $selectedValues = array();
	/**
	 * @var array Zakázané hodnoty, které nelze měnit
	 */
	private array $disabledValues = array();
	/**
	 * @var array Hodnoty k vybrání
	 */
	private array $values = array();

	/**
	 * Vrátí data z CheckListu tak, že jsou v nich i nezaškrtnuté položky s hodnotou 0
	 * @return array Data
	 */
	public function getData(): array
	{
		$empty = array_fill_keys(array_values($this->values), 0);

		return array_intersect_key($this->getSentData() + $empty, $empty);
	}

	/**
	 * Vrátí klíče položek
	 * @return array Klíče položek
	 */
	public function getKeys(): array
	{
		return array_values($this->values);
	}

	/**
	 * Nastaví horizontální orientaci položek
	 * @param bool $horizontal Pokud je true, jsou položky řazeny vedle sebe
	 * @return CheckList Kontrolka k dalšímu využití
	 */
	public function setHorizontal(bool $horizontal): CheckList
	{
		$class = $horizontal ? 'radio-horizontal' : 'radio-vertical';
		$this->addClass($class);

		return $this;
	}

	/**
	 * Nastaví možné hodnoty k výběru
	 * @param array $values Hodnoty jako asociativní pole, kde klíč je popisek a hodnota odesílaná hodnota
	 * @return CheckList Kontrolka k dalšímu využití
	 */
	public function setValues(array $values): CheckList
	{
		$this->values = $values;

		return $this;
	}

	/**
	 * Nastaví vybrané hodnoty
	 * @param array $values Vybrané hodnoty
	 * @return CheckList Kontrolka k dalšímu využití
	 */
	public function setSelectedValues(array $values): CheckList
	{
		$this->selectedValues = $values;

		return $this;
	}

	/**
	 * Nastaví zakázané hodnoty
	 * @param array $values Zakázané hodnoty
	 * @return CheckList Kontrolka k dalšímu využití
	 */
	public function setDisabledValues(array $values): CheckList
	{
		$this->disabledValues = $values;

		return $this;
	}

	/**
	 * Nastaví data pro jednu položku z CheckListu
	 * @throws FormException
	 * @param string $value Hodnota položky
	 * @param string $key Klíč položky
	 */
	public function setData(string $key, string $value): void
	{
		if (!in_array($key, $this->values))
			throw new FormException('Key ' . $key . ' does not exist in ' . $this->name . ' control');

		if ($value)
			$this->selectedValues[] = $key;
		else {
			$key = array_search($key, $this->selectedValues);
			if ($key !== false)
				unset($this->selectedValues[$key]);
		}
	}

	/**
	 * Vyrenderuje HTML položek v CheckListu
	 * @param HtmlBuilder $builder Instance HtmlBuilder
	 * @param bool $isPostBack Zda byl odeslaný formulář
	 */
	private function renderOptions(HtmlBuilder $builder, bool $isPostBack): void
	{
//		todo: spravit odřádkování případně závorky
		foreach ($this->values as $key => $value) {
			$params = array();
			$params['type'] = 'checkbox';
			$params['name'] = $value;
			$params['value'] = 1;
			$params['id'] = $this->htmlParams['id'] . $value;

			if (($isPostBack) && ($this->sentDataKeyExists($value) && $this->getSentData($value)) || ((!$isPostBack) && (in_array($value, $this->selectedValues))))
				$params['checked'] = 'checked';
			if (in_array($value, $this->disabledValues))
				$params['disabled'] = 'disabled';

			$builder->startElement('span');
			$builder->addElement('input', $params);
			$builder->addValueElement('label', $key, array(
				'for' => $params['id'],
			));
			$builder->endElement();
		}
	}

//	todo: https://www.itnetwork.cz/diskuze/php/formularovy-framework-v-php/php-tutorial-formularovy-framework-checklist

	/**
	 * Vrátí HTML kód CheckListu
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