<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form,
	Nette\Utils\Image;

/**
 * Address presenter
 */
class AddressPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	private $addresses;

	private $values;

	private $id;
	
	public function __construct(Model\AddressManager $addresses)
	{
		$this->addresses = $addresses;
		$this->values = array("name" => "", "logo_path" => "");
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->addresses = $this->addresses->getAll();
	}

	public function renderEdit($addressId)
	{
		$this->template->address = $this->addresses->getAddress($addressId);
	}

	/*Address form*/
	public function createComponentAddressForm()
	{
		$form = new Form;

		$form->addText("street", "Ulica")
			 ->setRequired('Ulica je povinná')
			 ->getControlPrototype()->class("form-control");

		$form->addTextArea("street_no", "Číslo ulice")
			 ->setRequired('Číslo ulice je povinné')
			 ->getControlPrototype()->class("form-control");

		$form->addTextArea("city", "Mesto")
			 ->setRequired('Mesto je povinné')
			 ->getControlPrototype()->class("form-control");

		$form->addTextArea("country", "Krajina")
			 ->setRequired('Krajina je povinná')
			 ->getControlPrototype()->class("form-control");

		$form->addTextArea("zip_code", "PSČ")
			 ->setRequired('PSČ je povinné')
			 ->getControlPrototype()->class("form-control");

		$form->addSubmit("add", "Pridať adresu")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "addressFormSucceeded");

		return $form;
	}

	public function addressFormSucceeded($form, $values)
	{
		$adding = true;
		
		if ( isset($this->request->getParameters()['addressId']) )
		{
			$addressId = $this->getParameter('addressId');
			$adding = false;
		}

		if ($adding)
		{
			$this->addresses->insert($values);
			$this->flashMessage('Adresa úspešne pridaná');
		}
		else
		{
			$this->addresses->edit($addressId, $values);
			$this->flashMessage('Adresa bola aktualizovaná');
		}

			$this->redirect("Address:");
	}

	public function actionRemove($addressId)
	{
			$this->addresses->remove($addressId);
			$this->flashMessage('Adresa bola úspešne vymazaná');
			$this->redirect("Address:");
	}

	public function actionEdit($addressId)
	{
		$address = $this->addresses->getAddress($addressId);

		$this->template->addressId = $addressId;

		$this['addressForm']->setDefaults($address->toArray());

	}

}