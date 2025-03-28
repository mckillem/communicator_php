<?php

namespace app\controllers;

abstract class Controller
{
	abstract function process(array $parameters): void;
}