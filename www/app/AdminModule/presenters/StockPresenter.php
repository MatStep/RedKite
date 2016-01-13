<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form,
	Nette\Utils\Image;

/**
 * Stock presenter
 */
class StockPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	/** @var \App\Model\AddressManager @inject */
	public $addressManager;

	private $stocks;

	private $values;

	private $id;
	
	public function __construct(Model\StockManager $stocks)
	{
		$this->stocks = $stocks;
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->stocks = $this->stocks->getAll();
	}

	public function renderAdd()
	{
	}

	public function renderEdit($stockId)
	{
		$this->template->stock = $this->stocks->getStock($stockId);
	}

	/*
	 * Stock form
	 */
	public function createComponentStockForm()
	{
		$form = new Form;

		//NO ADDRESS YET//
		$addressesArray = self::createAddressesArrayForSelect();

		$form->addText("name", "Názov dodávateľa")
			 ->setRequired('Názov je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addText("count_products", "Počet produktov")
			 ->setRequired('Počet produktov je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addSelect("address_id", "Adresa dodávateľa", $addressesArray) 
			 ->getControlPrototype()->class("form-control");

		$form->addText("street", "Ulica")
			 ->setAttribute("class", "form-control")
			 ->addConditionOn($form['address_id'], Form::EQUAL, "default")
			 	->setRequired('Ulica je povinná');

		$form->addText("street_no", "Číslo ulice")
			 ->setAttribute("class", "form-control")
			 ->addConditionOn($form['address_id'], Form::EQUAL, "default")
			 	->setRequired('Číslo ulice je povinné');

		$form->addText("city", "Mesto")
			 ->setAttribute("class", "form-control")
			 ->addConditionOn($form['address_id'], Form::EQUAL, "default")
			 	->setRequired('Mesto je povinné');

		$form->addText("country", "Krajina")
			 ->setAttribute("class", "form-control")
			 ->addConditionOn($form['address_id'], Form::EQUAL, "default")
			 	->setRequired('Krajina je povinná');

		$form->addText("zip_code", "PSČ")
			 ->setAttribute("class", "form-control")
			 ->addConditionOn($form['address_id'], Form::EQUAL, "default")
				 ->setRequired('PSČ je povinné');

		$form->addSubmit("add", "Pridať dodávateľa")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "stockFormSucceeded");

		return $form;
	}

	public function stockFormSucceeded($form, $values)
	{
		$adding = true;
		
		try {
			if ( isset($this->request->getParameters()['stockId']) )
			{
				$stockId = $this->getParameter('stockId');
				$adding = false;
			}

			if ($adding)
			{
				$this->stocks->insert($values);
				$this->flashMessage('Sklad úspešne pridaný');
			}
			else
			{
				$this->stocks->edit($stockId, $values);
				$this->flashMessage('Sklad bol aktualizovaný');
			}

				$this->redirect("Stock:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Tento sklad už existuje');
		}
	}

	public function actionRemove($stockId)
	{
			$this->stocks->remove($stockId);
			$this->flashMessage('Sklad bol úspešne vymazaný');
			$this->redirect("Stock:");
	}

	public function actionEdit($stockId)
	{
		$stock = $this->stocks->getStock($stockId);

		$this->template->stockId = $stockId;

		$this['stockForm']->setDefaults($stock->toArray());

	}

	private function createAddressesArrayForSelect()
	{
		$addresses = $this->addressManager->getAll();

		$addressesArray = array();

		$addressesArray["default"] = "--";

		foreach ( $addresses as $address )
		{
			$addressesArray[$address->id] = $address->street . " " . $address->street_no . ", " . $address->zip_code . " " . $address->city . ", " . $address->country;
		}

		return $addressesArray;
	}
}