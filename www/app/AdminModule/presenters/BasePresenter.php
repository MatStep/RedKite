<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form;

/**
 * Admin Base presenter
 */
class BasePresenter extends \App\Presenters\BasePresenter
{
	private $taxes;

	private $currencies;

	public function inject(Model\TaxManager $taxes, Model\CurrencyManager $currencies)
	{
		$this->taxes = $taxes;
		$this->currencies = $currencies;
	}

	public function startup()
    {
    	parent::startup();
    	$user = $this->getUser();
    	if(!$user->isLoggedIn()) {
    		$this->flashMessage('Pre vstup do administrácie je potrebné sa prihlásiť');
    		$this->redirect(':Homepage:');
    	}

    	$this->template->taxes = $this->taxes->getAll();
    	$this->template->currencies = $this->currencies->getAll();
    }
}
