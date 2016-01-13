<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form,
	Nette\Utils\Image;

/**
 * Brand presenter
 */
class BrandPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	private $brands;

	private $values;

	private $id;
	
	public function __construct(Model\BrandManager $brands)
	{
		$this->brands = $brands;
		$this->values = array("name" => "", "logo_path" => "");
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->brands = $this->brands->getAll();
	}

	public function renderEdit($brandId)
	{
		$this->template->brand = $this->brands->getBrand($brandId);
		$logo_path = $this->brands->getImage($brandId);
		$this->template->logo_path = $logo_path;
	}

	/*
	 * Brand form
	 */
	public function createComponentBrandForm()
	{
		$form = new Form;

		$form->addText("name", "Názov")
			 ->setRequired('Názov je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addUpload("logo", "Logo")
			 ->addCondition(Form::FILLED)
			 
			 ->addRule(Form::IMAGE, "Obrázok musí byť JPEG, PNG alebo GIF");

		$form->addSubmit("add", "Pridať značku")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "brandFormSucceeded");

		return $form;
	}

	public function brandFormSucceeded($form, $values)
	{
		$adding = true;
		
		try {
			if ( isset($this->request->getParameters()['brandId']) )
			{
				$brandId = $this->getParameter('brandId');
				$adding = false;
			}

			if ($adding)
			{
				$this->brands->insert($values);
				$this->flashMessage('Logo úspešne pridané');
			}
			else
			{
				$this->brands->edit($brandId, $values);
				$this->flashMessage('Logo bolo aktualizované');
			}

				$this->redirect("Brand:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Logo neexistuje');
		}
	}

	public function actionRemove($brandId)
	{
		$this->brands->remove($brandId);

		$this->flashMessage('Logo bolo úspešne vymazané');
		$this->redirect("Brand:");
	}

	public function actionRemoveImage($brandId)
	{
		$this->brands->removeImage($brandId);

		$this->flashMessage('Obrázok bol úspešne vymazaný');
		$this->redirect('Brand:Edit', $brandId);
	}

	public function actionEdit($brandId)
	{
		$brand = $this->brands->getBrand($brandId);

		$this->template->brandId = $brandId;

		$this['brandForm']->setDefaults($brand->toArray());

	}
}