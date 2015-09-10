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
    	$this->template->tax = $this->taxes->getActiveTax();
    	$this->template->currencies = $this->currencies->getAll();
    }

    /*Tax settings*/
    public function createComponentTaxSettings()
    {
    	$form = new Form;

    	$taxes = $this->taxes->getAllTaxesAsArray();

    	$select = $form->addSelect('tax', 'Dane', $taxes)
	    		->setAttribute('class', 'form-control');

	    $form->addSubmit("edit", "Zmeniť")
	    	 ->getControlPrototype()->class("btn btn-primary");

	    $form->onSuccess[] = array($this, "taxSettingsSucceeded");

	    return $form;
    }

    public function taxSettingsSucceeded($form, $values)
	{
		$taxId = $this->getParameter('id');
		$this->taxes->setActive($values);
		$this->flashMessage('Nastavenie dane bolo zmenené');
		$this->redirect("Homepage:");
	}
}
