<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form;

/**
 * Tax presenter
 */
class TaxPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	private $taxes;

	private $values;

	private $id;
	
	public function __construct(Model\TaxManager $taxes)
	{
		$this->taxes = $taxes;
		$this->values = array("name" => "", "value" => "");
		$this->id = 0;
	}

	/*Before render template redirect if user has no rights to enter the page*/
	public function beforeRender()
	{
		if(!$this->userManager->hasRole($this->getUser(), "admin")) {
			$this->flashMessage('Nemáte práva na vstup do sekcie dane');
			$this->redirect('Homepage:');
		}
	}

	public function renderDefault()
	{
		$this->template->taxes = $this->taxes->getAll();
	}

	/*Tax form*/
	public function createComponentTaxForm()
	{
		$form = new Form;

		$form->addText("name", "Názov")
			 ->setRequired('Názov je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addText("value", "Hodnota")
			 ->setRequired('Hodnota je povinná')
			 ->getControlPrototype()->class("form-control");

		$form->addSubmit("add", "Pridať daň")
			 ->getControlPrototype()->class("btn btn-primary btn-block");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary btn-block");

		$form->onSuccess[] = array($this, "taxFormSucceeded");

		return $form;
	}

	public function taxFormSucceeded($form, $values)
	{
		$adding = true;
		
		try {
			if ( isset($this->request->getParameters()['taxId']) )
			{
				$taxId = $this->getParameter('taxId');
				$adding = false;
			}

			if ($adding)
			{
				$this->taxes->insert($values);
				$this->flashMessage('Daň úspešne pridaná');
			}
			else
			{
				$this->taxes->edit($taxId, $values);
				$this->flashMessage('Daň bola aktualizovaný');
			}

				$this->redirect("Tax:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Daň neexistuje');
		}
	}

	public function actionRemove($taxId)
	{
			$this->taxes->remove($taxId);
			$this->flashMessage('Daň bola úspešne vymazaná');
			$this->redirect("Tax:");
	}

	public function actionEdit($taxId)
	{
		$tax = $this->taxes->getTax($taxId);

		$this->template->taxId = $taxId;

		$this['taxForm']->setDefaults($tax->toArray());

	}
}