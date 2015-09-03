<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

/**
 * Settings presenter
 */
class SettingsPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;
	
	/*Before render template redirect if user has no rights to enter the page*/
	public function beforeRender()
	{
		if(!$this->userManager->hasRole($this->getUser(), "admin")) {
			$this->flashMessage('Nemáte práva na vstup do sekcie nastavenia');
			$this->redirect('Homepage:');
		}
	}

	public function renderDefault()
	{

	}
}