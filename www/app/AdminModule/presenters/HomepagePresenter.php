<?php

namespace App\AdminModule\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
	/**@var Nette\Database\Context*/
	private $database;

	/**Construct database*/
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function renderDefault()
	{
		if(!$this->getUser()->isLoggedIn()) {
			$this->flashMessage('Pred vstupom do Admin panela je potrebné sa prihlásiť');
			$this->redirect(':Homepage:');
		}
	}

}