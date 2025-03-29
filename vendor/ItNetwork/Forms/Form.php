<?php

namespace ItNetwork\Forms;

use ItNetwork\Forms\Controls\CheckBox;
use ItNetwork\Forms\Controls\CheckList;
use ItNetwork\Forms\Controls\DateTimePicker;
use ItNetwork\Forms\Controls\FileBox;
use ItNetwork\Forms\Controls\InputBox;
use ItNetwork\Forms\Controls\ListBox;
use ItNetwork\Forms\Controls\RadioGroup;
use ItNetwork\Forms\Controls\TextArea;
use ItNetwork\HtmlBuilder;
use ItNetwork\UserException;
use ItNetwork\Utility\DateTimeUtility;

class Form
{
	/**
	 * @var array Kolekce kontrolek formuláře
	 */
	private array $controls = array();
	/**
	 * @var bool Zda byl formulář odeslaný
	 */
	private bool $isPostBack = false;
	/**
	 * @var array Kolekce tlačítek
	 */
	private array $buttons = array();
	/**
	 * @var array Kolekce vyrenderovaných kontrolek
	 */
	private array $rendered = array();
	/**
	 * @var bool Zda se mají generovat klientské validace
	 */
	public bool $validateClient = true;
	/**
	 * @var bool Zda se má spouštět serverová validace
	 */
	public bool $validateServer = true;
	/**
	 * @var bool Zda jsou hodnoty ve formuláři validní
	 */
	private bool $valid = false;
	/**
	 * @var ?array Mapa klíčů formuláře na jeho kontrolky
	 */
	private ?array $keymap = null;
	/**
	 * @var bool Zda je ve formuláři kontrolka pro nahrávání souborů, používá se při generování enctype
	 */
	private bool $hasFile = false;

	/**
	 * Inicializuje formulář
	 * @param string $formName Název formuláře
	 */
	public function __construct(public string $formName, private Method $method = Method::Post, private bool $inline = false)
	{
		$formNameBox = $this->addHiddenBox('form-name', $this->formName);
		$this->isPostBack = $formNameBox->sentDataKeyExists('form-name') && $formNameBox->getSentData('form-name') == $this->formName;
	}

	public function getSentButton(): ?string
	{
		foreach ($this->buttons as $button) {
			if ($button->sentDataKeyExists($button->name))
				return $button->name;
		}

		return null;
	}

	/**
	 * Přidá kontrolku do formuláře
	 * @param FormControl $control Kontrolka
	 * @param bool $required Zda je kontrolka povinná
	 */
	private function addControl(FormControl $control, bool $required = false): FormControl
	{
		if ($required)
			$control->addRequiredRule();
		$control->setMethod($this->method);
		$this->controls[$control->htmlParams['name']] = $control;

		return $control;
	}

	/**
	 * Přidá do formuláře InputBox určitého typu
	 * @param string $name Název inputu
	 * @param string $type Typ
	 * @param ?string $pattern Regulární výraz pro kontrolu typu na serveru
	 * @param string $label Popisek
	 * @param bool $required Zda je pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return InputBox InputBox
	 */
	private function addInputBox(string $name, string $type, ?string $pattern, string $label, bool $required = false, array $htmlParams = array()): InputBox
	{
		$textBox = new InputBox($name, $type, $label, $htmlParams);
		if ($pattern)
			$textBox->addPatternRule($pattern, false, true);

		return $this->addControl($textBox, $required);
	}

	/**
	 * Přidá do formuláře textové pole
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return InputBox Textové pole
	 */
	public function addTextBox(string $name, string $label, bool $required = false, array $htmlParams = array()): InputBox
	{
		return $this->addInputBox($name, 'text', null, $label, $required, $htmlParams);
	}

	/**
	 * Přidá do formuláře skryté pole
	 * @param string $name Název pole
	 * @param string $text Text v poli
	 * @param bool $required Zda je pole povinné
	 * @return InputBox Skryté pole
	 */
	public function addHiddenBox(string $name, string $text = '', bool $required = false): InputBox
	{
		return $this->addInputBox($name, 'hidden', null, '', $required)
			->setText($text);
	}

	/**
	 * Přidá do formuláře pole pro zadání emailu
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return InputBox InputBox
	 */
	public function addEmailBox(string $name, string $label, bool $required = false, array $htmlParams = array()): InputBox
	{
		return $this->addInputBox($name, 'email', FormControl::PATTERN_EMAIL, $label, $required, $htmlParams);
	}

	/**
	 * Přidá do formuláře pole na URL adresu
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return InputBox InputBox
	 */
	public function addUrlBox(string $name, string $label, bool $required = false, array $htmlParams = array()): InputBox
	{
		return $this->addInputBox($name, 'url', FormControl::PATTERN_URL, $label, $required, $htmlParams);
	}

	/**
	 * Přidá do formuláře pole pro zadání celého čísla
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return InputBox InputBox
	 */
	public function addNumberBox(string $name, string $label, bool $required = false, array $htmlParams = array()): InputBox
	{
		return $this->addInputBox($name, 'number', FormControl::PATTERN_INTEGER, $label, $required, $htmlParams);
	}

	/**
	 * Přidá do formuláře pole pro heslo
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return InputBox InputBox
	 */
	public function addPasswordBox(string $name, string $label, bool $required = false, array $htmlParams = array()): InputBox
	{
		return $this->addInputBox($name, 'password', '', $label, $required, $htmlParams)
			->addPasswordRule();
	}

	/**
	 * Přidá do formuláře tlačítko
	 * @param string $name Název pole
	 * @param string $text Text na tlačítku
	 * @param array $htmlParams HTML parametry
	 * @return InputBox InputBox
	 */
	public function addButton(string $name, string $text, array $htmlParams = array()): InputBox
	{
		$button = $this->addInputBox($name, 'submit', '', '', false, $htmlParams)
			->setText($text);
		$this->buttons[$name] = $button;

		return $button;
	}

	/**
	 * Přidá do formuláře zaškrtávací pole
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $checked Zda má být ve výchozím stavu zaškrtnutý
	 * @param array $htmlParams HTML parametry
	 * @return CheckBox CheckBox
	 */
	public function addCheckBox(string $name, string $label, bool $checked = false, array $htmlParams = array()): CheckBox
	{
		return $this->addControl(new CheckBox($name, $label, $htmlParams))
			->setChecked($checked);
	}

	/**
	 * Přidá do formuláře pole pro víceřádkový text
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je vyplnění pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return TextArea TextArea
	 */
	public function addTextArea(string $name, string $label, bool $required = false, array $htmlParams = array()): TextArea
	{
		return $this->addControl(new TextArea($name, $label, $htmlParams), $required);
	}

	/**
	 * Přidá do formuláře pole pro výběr data a času
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je vyplnění pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return DateTimePicker DateTimePicker
	 */
	public function addDateTimePicker(string $name, string $label, bool $required = false, array $htmlParams = array()): DateTimePicker
	{
//		todo: yyyy má být spíš rrrr. česky a stejně v další metodě
		$htmlParams['placeholder'] = 'dd.mm.yyyy hh:mm';

		return $this->addControl(new DateTimePicker($name, DateTimeUtility::DATETIME_FORMAT, $label, $htmlParams), $required)
			->addDateTimeRule();
	}

	/**
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je vyplnění pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return DateTimePicker DateTimePicker
	 */
	public function addDatePicker(string $name, string $label, bool $required = false, array $htmlParams = array()): DateTimePicker
	{
		$htmlParams['placeholder'] = 'dd.mm.yyyy';

		return $this->addControl(new DateTimePicker($name, DateTimeUtility::DATE_FORMAT, $label, $htmlParams), $required)
			->addDateRule();
	}

	/**
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je vyplnění pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return DateTimePicker DateTimePicker
	 */
	public function addTimePicker(string $name, string $label, bool $required = false, array $htmlParams = array()): DateTimePicker
	{
		$htmlParams['placeholder'] = 'hh:mm';

		return $this->addControl(new DateTimePicker($name, DateTimeUtility::TIME_FORMAT, $label, $htmlParams), $required)
			->addTimeRule();
	}

	/**
	 * Přidá do formuláře pole pro výběr souboru
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je vyplnění pole povinné
	 * @param ?string $accept Maska pro typy souborů
	 * @param bool $multiple Zda lze vbybrat více souborů
	 * @param array $htmlParams HTML parametry
	 * @return FileBox FileBox
	 */
	public function addFileBox(string $name, string $label, bool $required = false, ?string $accept = null, bool $multiple = false, array $htmlParams = array()): FileBox
	{
		if ($accept)
			$htmlParams['accept'] = $accept;
		$this->hasFile = true;

		return $this->addControl(new FileBox($name, $required, $multiple, $label, $htmlParams));
	}

	/**
	 * Přidá do formuláře rozbalovací nabídku
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je pole povinné
	 * @param array $htmlParams HTML parametry
	 * @return ListBox ListBox
	 */
	public function addComboBox(string $name, string $label, bool $required = false, array $htmlParams = array()): ListBox
	{
		$comboBox = new ListBox($name, $required, false, $label, $htmlParams);
		$this->addControl($comboBox, $required);

		return $comboBox;
	}

	/**
	 * Přidá do formuláře výběr ze seznamu hodnot
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $required Zda je pole povinné
	 * @param bool $multiple Zda jde vybrat více hodnot
	 * @param array $htmlParams HTML parametry
	 * @return ListBox ListBox
	 */
	public function addListBox(string $name, string $label, bool $required = false, bool $multiple = false, array $htmlParams = array()): ListBox
	{
		if (!$multiple)
			$htmlParams['size'] = 4;
		$comboBox = new ListBox($name, $required, $multiple, $label, $htmlParams);
		$this->addControl($comboBox, $required);

		return $comboBox;
	}

	/**
	 * Přidá do formuláře pole s několika souvisejícími CheckBoxy
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $horizontal Zda chceme položky řadit vedle sebe nebo pod sebe
	 * @param array $htmlParams HTML parametry
	 * @return CheckList CheckList
	 */
	public function addCheckList(string $name, string $label, bool $horizontal = false, array $htmlParams = array()): CheckList
	{
		$checkList = new CheckList($name, $label, $htmlParams);
		$checkList->setHorizontal($horizontal);
		$this->addControl($checkList);

		return $checkList;
	}

	/**
	 * Přidá do formuláře skupinu RadioButtonů
	 * @param string $name Název pole
	 * @param string $label Popisek
	 * @param bool $horizontal Zda chceme položky řadit vedle sebe nebo pod sebe
	 * @param array $htmlParams HTML parametry
	 * @return RadioGroup RadioGroup
	 */
	public function addRadioGroup(string $name, string $label, bool $horizontal = false, array $htmlParams = array()): RadioGroup
	{
		$radioGroup = new RadioGroup($name, $label, $htmlParams);
		$radioGroup->setHorizontal($horizontal);
		$this->addControl($radioGroup, true);

		return $radioGroup;
	}

	/**
	 * Vrátí HTML kód celého formuláře
	 * @throws FormException
	 * @return string HTML kód celého formuláře
	 */
	public function render(): string
	{
		$s = $this->renderStartForm();
		$s .= $this->renderControls();
		$s .= $this->renderButtons();
		$s .= $this->renderEndForm();

		return $s;
	}

	/**
	 * Vrátí HTML kód začátku formuláře
	 * @return string HTML kód začátku formuláře
	 */
	public function renderStartForm(): string
	{
		$params = array(
			'method' => $this->method->name,
			'id' => $this->formName,
			'class' => 'fancyform',
		);
		if ($this->inline)
			$params['class'] .= ' inline-form';

		if ($this->hasFile)
			$params['enctype'] = 'multipart/form-data';

		$builder = new HtmlBuilder();
		$builder->startElement('form', $params);

		foreach ($this->controls as $control) {
			if ($control instanceof InputBox && $control->type == 'hidden') {
				$builder->addValue($control->render($this->validateClient, $this->isPostBack()), true);
				$this->rendered[$control->name] = $control;
			}
		}

		return $builder->render();
	}

	/**
	 * Vrátí HTML kód konce formuláře
	 * @return string HTML kód
	 */
	public function renderEndForm(): string
	{
		$builder = new HtmlBuilder();
		$builder->endElement('form');

		return $builder->render();
	}

	/**
	 * @param string $name
	 * @return string HTML kód formulářové kontrolky
	 */
//	todo: změněno na public, aby fungovalo zobrazení objednávky. Je to tak správně?
	public function renderControl(string $name): string
	{
		$this->rendered[$name] = $this->controls[$name];

		return $this->controls[$name]->render($this->validateClient, $this->isPostBack);
	}

	/**
	 * Vrátí pole kontrolek podle pole názvů kontrolek
	 * @throws FormException
	 * @return array Kontrolky
	 * @param array $names Názvy kontrolek
	 */
	private function getControlsToRender(array $names = array()): array
	{
		// Při nezadaném parametru chceme renderovat vše
		if (!$names)
			$names = array_keys($this->controls);
		else {
			// Kontrola neexistujícíh názvů
			$diff = array_diff($names, array_keys($this->controls));
			if ($diff)
				throw new FormException('Ve formuláři neexistuje: ' . implode(', ', $diff));
		}
		// Odečtení tlačítek a již vyrenderovaných
		$controls = array_diff_key($this->controls, $this->buttons, $this->rendered);

		// Výběr jen kontrolek s názvy, které chceme renderovat
		return array_intersect_key($controls, array_flip($names));
	}

	/**
	 * Vrátí HTML kód určitých kontrolek nebo všech kontrolek pokud nezadáme parametry
	 * @throws FormException
	 * @return string HTML kód vyrenderovaných kontrolek
	 * @param string ...$names Názvy kontrolek (libovolný počet argumentů)
	 */
	public function renderControls(string ...$names): string
	{
		$controls = $this->getControlsToRender(func_get_args());

		$builder = new HtmlBuilder();
		foreach ($controls as $control) {
			$builder->startElement('div', array(
				'class' => 'form-component',
			));
			$builder->addValueElement('label', $control->label, array(
				'for' => $control->htmlParams['id'],
			), $control->label === '&nbsp;');
			$builder->addValue($this->renderControl($control->htmlParams['name']), true);
			$builder->startElement('div', array(
				'class' => 'clear',
			));
			$builder->endElement();
			$builder->endElement();
		}

		return $builder->render();
	}

	/**
	 * Vrátí HTML kód tlačítek
	 * @return string HTML kód
	 */
	public function renderButtons(): string
	{
		if (!$this->buttons)
			return '';
		$builder = new HtmlBuilder();
		$builder->startElement('div', array(
			'class' => 'form-buttons',
		));
		foreach ($this->buttons as $name => $button) {
			$builder->addValue($this->renderControl($name), true);
		}
		$builder->endElement();

		return $builder->render();
	}

	/**
	 * Vrátí data z vybraných formulářových kontrolek. Při volání bez parametrů vrátí všechna data.
	 * @throws UserException
	 * @return array Data z kontrolek
	 * @param string ...$names Názvy kontrolek (libovolný počet argumentů)
	 */
	public function getData(string ...$names): array
	{
		if (!$this->valid)
			$this->checkValidity();
		$keys = func_get_args();
		if (!$keys)
			$keys = array_keys($this->controls);
		$controls = array_intersect_key($this->controls, array_flip($keys));
		$controls = array_diff_key($controls, $this->buttons);
		$controls = array_diff_key($controls, array_flip(array('form-name')));
		$data = array();
		foreach ($controls as $control) {
			$controlData = $control->getData();
			foreach ($controlData as $key => $value) {
				$data[$key] = $value;
			}
		}

		return $data;
	}

	/**
	 * Zvaliduje $_POST podle pravidel a vygeneruje chybové hlášky
	 * @throws UserException
	 * @return void Zda je formulář validní
	 */
	private function checkValidity(): void
	{
		if ($this->validateServer) {
			$messages = array();
			foreach ($this->controls as $control) {
				try {
					$control->checkValidity();
				} catch (UserException $e) {
					$messages[] = $e->getMessage();
				}
			}
			if (!empty($messages))
				throw new UserException(implode("\n", $messages));
		}
		$this->valid = true;
	}

	/**
	 * Vrátí mapu klíčů odesílaných formulářem do POST na kontrolky
	 * @return array Mapa klíčů na kontrolky
	 */
	private function getKeymap(): array
	{
		if ($this->keymap !== null)
			return $this->keymap;
		$keymap = array();
		foreach ($this->controls as $control) {
			$keys = $control->getKeys();
			foreach ($keys as $key) {
				$keymap[$key] = $control;
			}
		}
		$this->keymap = $keymap;

		return $keymap;
	}

	/**
	 * Nastaví data kontrolkám ve formuláři
	 * @param array $data Data
	 */
	public function setData(array $data): void
	{
		$keymap = $this->getKeymap();

		foreach ($data as $key => $value) {
			if (isset($keymap[$key]))
				$keymap[$key]->setData($key, $value);
		}
	}

	/**
	 * Vrátí zda byl formulář odeslán
	 * @return bool Zda byl formulář odeslán
	 */
	public function isPostBack(): bool
	{
		return $this->isPostBack;
	}

}