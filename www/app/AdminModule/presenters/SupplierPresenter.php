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

	public function renderEdit($supplierId)
	{
		$this->template->supplier = $this->suppliers->getSupplier($supplierId);
	}

	/*Supplier form*/
	public function createComponentSupplierForm()
	{
		$form = new Form;

		//NO ADDRESS YET//

		$form->addText("name", "Názov dodávateľa")
			 ->setRequired('Názov je povinný')
			 ->getControlPrototype()->class("form-control");

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

	}

}