<?php

namespace ItNetwork;

use config\Settings;

/**
 * Pomocná třída, poskytující metody pro odeslání emailu
 */
class EmailSender
{
	/**
	 * Odešle email jako HTML, lze tedy používat základní HTML tagy a nové
	 * řádky je třeba psát jako <br /> nebo používat odstavce. Kódování je
	 * odladěno pro UTF-8.
	 * @throws UserException
	 * @param string $subject Předmět
	 * @param string $message Zpráva
	 * @param string $from Adresa odesílatele
	 * @param string $address Adresa
	 */
	public function send(string $address, string $subject, string $message, string $from): void
	{
		$header = "From: " . $from;
		$header .= "\nMIME-Version: 1.0\n";
		$header .= "Content-Type: text/html; charset=\"utf-8\"\n";
		if (Settings::$debug)
		{
			file_put_contents('files/emails/' . uniqid(), $message);
			return;
		}
		if (!mb_send_mail($address, $subject, $message, $header))
			throw new UserException('Email se nepodařilo odeslat.');
	}

	/**
	 * Zkontroluje, zda byl zadán aktuální rok jako antispam a odešle email
	 * @throws UserException
	 * @param string $address Adresa
	 * @param string $subject Předmět
	 * @param string $message Zpráva
	 * @param string $from Adresa odesílatele
	 * @param int $year Aktuální rok
	 */
	public function sendWithAntispam(int $year, string $address, string $subject, string $message, string $from): void
	{
		if ($year != date("Y"))
			throw new UserException('Chybně vyplněný antispam.');
		$this->send($address, $subject, $message, $from);
	}

}