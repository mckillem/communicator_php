<?php

namespace App\CoreModule\Users\Controllers;

use App\CoreModule\Users\Models\UserManager;
use App\CoreModule\System\Controllers\Controller;

class LoginController extends Controller
{
	public function index(array $parameters): void
	{
		if (!empty($_POST) && $_POST['email'] && $_POST['password'])
		{
//			try
//			{
				$userManager = new UserManager();
				$userManager->login($_POST['email'], $_POST['password']);
//				$this->addMessage('Byl jste úspěšně přihlášen.');
				$this->redirect();
//			}
//			catch (UserException $error)
//			{
//				$this->addMessage($error->getMessage());
//			}
		}

		$this->view = 'index';
	}
}