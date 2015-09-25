<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form,
	Nette\Utils\Image;

/**
 * Supplier presenter
 */
class SupplierPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	/** @var \App\Model\AddressManager @inject */
	public $addressManager;

	private $suppliers;

	private $values;

	private $id;
	
	public function __construct(Model\SupplierManager $suppliers)
	{
		$this->suppliers = $suppliers;
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->suppliers = $this->suppliers->getAll();
	}

	public function renderAdd()
	{
	}

	public function renderEdit($supplierId)
	{
		$this->template->supplier = $this->suppliers->getSupplier($supplierId);
	}

	/*Supplier form*/
	public function createComponentSupplierForm()
	{
		$form = new Form;

		//NO ADDRESS YET//
		$addressesArray = self::createAddressesArrayForSelect();

		$form->addText("name", "Názov dodávateľa")
			 ->setRequired('Názov je povinný')
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

		$form->addText("date_from", "Dátum začatia spolupráce")
			 ->setRequired('Dátum je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addSubmit("add", "Pridať dodávateľa")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "supplierFormSucceeded");

		return $form;
	}

	public function supplierFormSucceeded($form, $values)
	{
		$adding = true;
		
		try {
			if ( isset($this->request->getParameters()['supplierId']) )
			{
				$supplierId = $this->getParameter('supplierId');
				$adding = false;
			}

			if ($adding)
			{
				$this->suppliers->insert($values);
				$this->flashMessage('Dodávateľ úspešne pridaná');
			}
			else
			{
				$this->suppliers->edit($supplierId, $values);
				$this->flashMessage('Dodávateľ bol aktualizovaný');
			}

				$this->redirect("Supplier:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Tento dodávateľ už existuje');
		}
	}

	public function actionRemove($supplierId)
	{
			$this->suppliers->remove($supplierId);
			$this->flashMessage('Dodávateľ bol úspešne vymazaný');
			$this->redirect("Supplier:");
	}

	public function actionEdit($supplierId)
	{
		$supplier = $this->suppliers->getSupplier($supplierId);

		$this->template->supplierId = $supplierId;

		$this['supplierForm']->setDefaults($supplier->toArray());
		$this['supplierForm']['date_from']->setDefaultValue(date('d.m.Y', strtotime($supplier->date_from)));

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