<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form;

/**
 * Currency presenter
 */
class CurrencyPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	private $currencies;

	private $values;

	private $id;
	
	public function __construct(Model\CurrencyManager $currencies)
	{
		$this->currencies = $currencies;
		$this->values = array("name" => "", "sign" => "", "rate" => "", "active" => "");
		$this->id = 0;
	}

	/*Before render template redirect if user has no rights to enter the page*/
	public function beforeRender()
	{
		if(!$this->userManager->hasRole($this->getUser(), "admin")) {
			$this->flashMessage('Nemáte práva na vstup do sekcie mien');
			$this->redirect('Homepage:');
		}
	}

	public function renderDefault()
	{
		$this->template->currencies = $this->currencies->getAll();
	}

	/*
	 * Currency form
	 */
	public function createComponentCurrencyForm()
	{
		$form = new Form;

		$form->addText("name", "Názov")
			 ->setRequired('Názov je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addText("sign", "Znak")
			 ->setRequired('Znak je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addText("rate", "Kurz")
			 ->setType('number')
			 ->setRequired('Kurz je povinný')
			 ->addRule(Form::FLOAT, "Kurz musí byť číslo")
			 ->getControlPrototype()->class("form-control");

		$form->addSubmit("add", "Pridať menu")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "currencyFormSucceeded");

		return $form;
	}

	public function currencyFormSucceeded($form, $values)
	{
		$adding = true;
		
		try {
			if ( isset($this->request->getParameters()['currencyId']) )
			{
				$currencyId = $this->getParameter('currencyId');
				$adding = false;
			}

			if ($adding)
			{
				$this->currencies->insert($values);
				$this->flashMessage('Mena úspešne pridaná');
			}
			else
			{
				$this->currencies->edit($currencyId, $values);
				$this->flashMessage('Mena bola aktualizovaná');
			}

				$this->redirect("Currency:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Mena neexistuje');
		}
	}

	public function actionRemove($currencyId)
	{
			$this->currencies->remove($currencyId);
			$this->flashMessage('Mena bola úspešne vymazaná');
			$this->redirect("Currency:");
	}

	public function actionEdit($currencyId)
	{
		$currency = $this->currencies->getCurrency($currencyId);

		$this->template->currencyId = $currencyId;

		$this['currencyForm']->setDefaults($currency->toArray());

	}
}