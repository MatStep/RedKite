<?php

namespace App\AdminModulePresenters;

use Nette,
	App\Model,	
    Nette\Application\UI\Form as Form,
    Nette\Utils\Image;

class AdminPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	public function renderDefault()
	{
		$user = $this->getUser();
		
		$admins = $this->userManager->getAdmins();

		$this->template->admins = $admins;
	}
}