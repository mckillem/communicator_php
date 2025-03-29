<?php

declare(strict_types=1);

namespace ItNetwork;

use GdImage;

/**
 * Knihovna pro práci s obrázky
 */
class Image
{
	/**
	 * Obrázek typu PNG
	 */
	const int IMAGETYPE_PNG = IMAGETYPE_PNG;
	/**
	 * Obrázek typu GIF
	 */
	const int IMAGETYPE_GIF = IMAGETYPE_GIF;
	/**
	 * Obrázek typu JPEG
	 */
	const int IMAGETYPE_JPEG = IMAGETYPE_JPEG;
	/**
	 * @var resource Obrázek
	 */
	private $image;
	/**
	 * @var int Typ obrázku
	 */
	private int $imageType;
	/**
	 * @var int Šířka obrázku v pixelech
	 */
	private int $width;
	/**
	 * @var int Výška obrázku v pixelech
	 */
	private int $height;

	/**
	 * Inicializuje instanci obrázku
	 * @param string $filename Cesta k souboru, ze kterého se má obrázek načíst
	 */
	public function __construct(string $filename)
	{
		$imageSize = getimagesize($filename);
		$this->width = $imageSize[0];
		$this->height = $imageSize[1];
		$this->imageType = $imageSize[2];
		if ($this->imageType == self::IMAGETYPE_JPEG)
			$this->image = imagecreatefromjpeg($filename);
		elseif ($this->imageType == self::IMAGETYPE_GIF) {
			// Gify načítáme vždy v true color
			$image = imagecreatefromgif($filename);
			$this->image = $this->createBackground($this->getWidth(), $this->getHeight(), true);
			imagecopy($this->image, $image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
		} elseif ($this->imageType == self::IMAGETYPE_PNG) {
			$this->image = imagecreatefrompng($filename);
			imagealphablending($this->image, true); // Zapnutí alfakanálu
			imagesavealpha($this->image, true); // Ukládání alfakanálu
		}
	}

	/**
	 * Zjistí zda je daný soubor obrázek
	 * @param string $fileName Cesta k souboru
	 * @return bool True, pokud je daný soubor obrázek, jinak false
	 */
	public static function isImage(string $fileName): bool
	{
//		Metoda je statická, protože není vázána na konkrétní instanci
//obrázku. Vysoce paranoidní jedinci si do metody mohou doplnit ještě kontrolu
//obsahu souboru na výskyty sekvencí typu: <?php,
//exec, base64_decode atd. Nahrání obrázku s
//falešnou hlavičkou a PHP kódem uvnitř je oblíbenou praktikou hackerů,
//kteří se snaží pomocí dalších slabin na webu příponu následně
//změnit.
//		Pro ukládání obrázků a vůbec jakýchkoli souborů od
//uživatele platí jedna zásada: Nikdy neponechávejte původní
//příponu souboru! Mohlo by totiž dojít ke spuštění škodlivého
//kódu na serveru. Soubory ukládejte buď úplně bez přípony nebo ji doplňte
//podle typu obrázku.
//Zdroj: https://www.itnetwork.cz/php/knihovny/php-tutorial-dokonceni-knihovny-image-prace-s-obrazky
		$type = exif_imagetype($fileName);
		return ($type == self::IMAGETYPE_JPEG || $type == self::IMAGETYPE_GIF || $type == self::IMAGETYPE_PNG);
	}

	/**
	 * Vytvoří nový obrázek o daných rozměrech a vyplní ho buď průhlednou barvou nebo bílou.
	 * @param int $width Šířka obrázku
	 * @param int $height Výška obrázku
	 * @param bool $transparent Průhlednost
	 * @return GdImage|bool Obrázek nebo hodnotu false při chybě
	 */
	private function createBackground(int $width, int $height, bool $transparent = true): GdImage|bool
	{
		$image = imagecreatetruecolor($width, $height);
		if ($transparent) {
			imagealphablending($image, true);
			$color = imagecolorallocatealpha($image, 0, 0, 0, 127);
		} else
			$color = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $color);
		if ($transparent)
			imagesavealpha($image, true);
		return $image;
	}

	/**
	 * Vrátí typ obrázku
	 * @return int Typ obrázku
	 */
	public function getImageType(): int
	{
		return $this->imageType;
	}

	/**
	 * Vrátí šířku obrázku v pixelech
	 * @return int Šířka obrázku v pixelech
	 */
	public function getWidth(): int
	{
		return $this->width;
	}

	/**
	 * Vrátí výšku obrázku v pixelech
	 * @return int Výška obrázku v pixelech
	 */
	public function getHeight(): int
	{
		return $this->height;
	}

	/**
	 * Uloží obrázek do souboru
	 * @param string|null $filename Název souboru
	 * @param int $imageType Typ obrázku
	 * @param int $compression Komprese (pouze pro typ JPEG) v procentech
	 * @param bool $transparent Zda má mít GIF nastavený průhlednou barvu
	 * @param int|null $permissions Nastavení oprávnění pro soubor
	 * @return void
	 */
	public function save(string|null $filename, int $imageType = self::IMAGETYPE_JPEG, int $compression = 85, bool $transparent = true, int $permissions = null): void
	{
		if ($imageType == self::IMAGETYPE_JPEG) {
			$output = $this->createBackground($this->getWidth(), $this->getHeight(), false);
			imagecopy($output, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
			imagejpeg($output, $filename, $compression);
		} elseif ($imageType == self::IMAGETYPE_GIF) {
			$image = $this->createBackground($this->getWidth(), $this->getHeight(), true);
			if ($transparent) {
				$color = imagecolorallocatealpha($image, 0, 0, 0, 127);
				imagecolortransparent($image, $color);
			}
			imagecopyresampled($image, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
			imagegif($image, $filename);
		} elseif ($imageType == self::IMAGETYPE_PNG)
			imagepng($this->image, $filename);
		if ($permissions != null)
			chmod($filename, $permissions);
	}

	/**
	 * Vypíše obrázek na standardní výstup
	 * @param int $imageType Typ obrázku
	 * @param int $compression Komprese (pouze pro typ JPEG) v procentech
	 * @param bool $transparent Zda má mít GIF nastavený průhlednou barvu
	 * @return void
	 */
	public function output(int $imageType = self::IMAGETYPE_JPEG, int $compression = 85, bool $transparent = true): void
	{
		$this->save(null, $imageType, $compression, $transparent);
	}

	/**
	 * Změní velikost obrázku
	 * @param int $width Požadovaná šířka
	 * @param int $height Požadovaná výška
	 * @return void
	 */
	public function resize(int $width, int $height): void
	{
		$image = $this->createBackground($width, $height, true);
		imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * Změní velikost obrázku tak, aby měl požadovanou výšku. Poměr stran zůstane zachován.
	 * @param int $height Výška obrázku v pixelech.
	 * @return void
	 */
	public function resizeToHeight(int $height): void
	{
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize((int) $width, $height);
	}

	/**
	 * Změní velikost obrázku tak, aby měl požadovanou šířku. Poměr stran zůstane zachován.
	 * @param int $width Šířka obrázku v pixelech.
	 * @return void
	 */
	public function resizeToWidth(int $width): void
	{
		$ratio = $width / $this->getWidth();
		$height = $this->getHeight() * $ratio;
		$this->resize($width, (int) $height);
	}

	/**
	 * Změní velikost obrázku tak, aby se vešel do zadané délky hrany. Poměr stran zůstane zachován.
	 * @param int $edge Délka hrany obrázku v pixelech
	 * @return bool Zda se obrázek změnil nebo již měl požadovanou velikost
	 */
	public function resizeToEdge(int $edge): bool
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		if (($width > $edge) || ($height > $edge)) {
			if ($width > $height)
				$this->resizeToWidth($edge);
			else
				$this->resizeToHeight($edge);
			return true;
		}
		return false;
	}

	/**
	 * Změní velikost obrázku tak, aby měl minimální hranu o zadané délce. Poměr stran zůstane zachován. (Není součást lekce)
	 * @param int $edge Délka hrany obrázku v pixelech
	 * @return bool True, pokud se obrázek změnil nebo již měl požadovanou velikost, jinak false
	 */
	public function resizeToCoverEdge(int $edge): bool
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		if (!($width == $edge && $height >= $edge) || ($height == $edge && $width >= $edge)) {
			if ($width < $height)
				$this->resizeToWidth($edge);
			else
				$this->resizeToHeight($edge);
			return true;
		}
		return false;
	}

	/**
	 * Škáluje obrázek v daném poměru
	 * @param int $scale Poměr v procentech
	 * @return void
	 */
	public function scale(int $scale): void
	{
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getHeight() * $scale / 100;
		$this->resize($width, $height);
	}

	/**
	 * Ořízne obrázek na danou velikost. Řeže se od levého horního rohu.
	 * @param int $width Šířka obrázku
	 * @param int $height Výška obrázku
	 * @return void
	 */
	public function crop(int $width, int $height): void
	{
		$image = $this->createBackground($width, $height, true);
		imagecopy($image, $this->image, 0, 0, 0, 0, $width, $height);
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * Přidá do pravého dolního rohu obrázku vodoznak
	 * @param string $path Cesta k obrázku vodoznaku
	 * @param int $offset Šířka okraje mezi vodoznakem a hranou obrázku v pixelech
	 * @return void
	 */
	public function addWatermark(string $path, int $offset = 8): void
	{
		$watermark = imagecreatefrompng($path);
		$width = imagesx($watermark);
		$height = imagesy($watermark);
		imagecopy($this->image, $watermark, $this->getWidth() - $width - $offset, $this->getHeight() - $height - $offset, 0, 0, $width, $height);
	}
}