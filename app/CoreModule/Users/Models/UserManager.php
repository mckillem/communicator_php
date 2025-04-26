<?php

namespace App\CoreModule\Users\Models;

use App\CoreModule\System\Models\Db;

class UserManager
{
	public static ?array $user;

	public function login(string $email, string $password): void
	{
		$user = Db::getUserByEmail($email);

		if (!$user || !password_verify($password, $user['password']))
//			throw new UserException('Neplatný email nebo heslo.');
		unset($user['password']);
		$_SESSION['user'] = $user;
	}

	public function loadUser(): void
	{
		self::$user = $_SESSION['user'] ?? null;
	}
}