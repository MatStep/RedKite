<?php

namespace App\AdminModule\Presenters;

use Nette;
use App\Model;

/**
 * Homepage presenter
 */
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
		
	}

}