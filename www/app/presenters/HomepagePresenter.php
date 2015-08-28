<?php

namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
	/**@var Nette\Database\Context*/
	private $database;
	/** @var \App\Model\UserManager @inject */
    public $userManager;

	/**Construct database*/
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function renderDefault()
	{
		$user = $this->getUser();
		$this->template->users = $this->database->table('user')
			->order('reg_date DESC');
		$this->template->user = $this->user;
	}

}
