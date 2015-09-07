<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

/**
 * Product presenter
 */
class ProductPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	public function renderDefault()
	{

	}
}