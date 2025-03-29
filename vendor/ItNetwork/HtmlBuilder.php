<?php

namespace ItNetwork;

class HtmlBuilder
{
	/**
	 * @var string Výsledné HTML
	 */
	private string $html = '';
	/**
	 * @var array Zásobník otevřených elementů
	 */
	private array $elementStack = array();

	/**
	 * Vyrenderuje HTML element a jeho HTML kód připojí k privátnímu řetězci
	 * @param string $name Název elementu
	 * @param array $htmlParams Pole HTML atributů a jejich hodnot
	 * @param bool $pair Zda je elemenent párový
	 */
	private function renderElement(string $name, array $htmlParams, bool $pair): void
	{
		$this->html .= '<' . htmlspecialchars($name);
		foreach ($htmlParams as $key => $value) {
			$this->html .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
		}
		if (!$pair)
			$this->html .= ' /';
		$this->html .= '>';
		if ($pair)
			array_push($this->elementStack, $name);
	}

	/**
	 * Vyrendruje jednoduchý nepárový element
	 * @param string $name Název elementu
	 * @param array $htmlParams Pole HTML atributů a jejich hodnot
	 */
	public function addElement(string $name, array $htmlParams = array()): void
	{
		$this->renderElement($name, $htmlParams, false);
	}

	/**
	 * Otevře párový element
	 * @param string $name Název
	 * @param array $htmlParams Pole HTML atributů a jejich hodnot
	 */
	public function startElement(string $name, array $htmlParams = array()): void
	{
		$this->renderElement($name, $htmlParams, true);
	}

	/**
	 * Přidá HTML kód a to buď do otevřeného elementu nebo klidně mimo něj.
	 * @param string $value Hodnota
	 * @param bool $doNotEscape Zda se má hodnota převést na entity či nikoli
	 */
	public function addValue(string $value, bool $doNotEscape = false): void
	{
		$this->html .= $doNotEscape ? $value : htmlspecialchars($value);
	}

	/**
	 * Uzavře poslední otevřený párový element nebo element s daným názvem.
	 * @param string $name Nepovinný název elementu
	 */
	public function endElement(string $name = ''): void
	{
		if (empty($name))
			$name = array_pop($this->elementStack);
		$this->html .= '</' . htmlspecialchars($name) . '>';
	}

	/**
	 * Otevře párový element, vloží do něj hodnotu a poté ho uzavře.
	 * @param string $name Název
	 * @param string $value Hodnota
	 * @param array $htmlParams Pole HTML atributů a jejich hodnot
	 * @param bool $doNotEscape Zda se má hodnota převést na entity či nikoli
	 */
	function addValueElement(string $name, string $value, array $htmlParams = array(), bool $doNotEscape = false): void
	{
		$this->startElement($name, $htmlParams, true);
		$this->addValue($value, $doNotEscape);
		$this->endElement();
	}

	/**
	 * Vrátí výsledný řetězec s HTML kódem
	 * @return string Výsledné HTML
	 */
	public function render(): string
	{
		return $this->html;
	}
}