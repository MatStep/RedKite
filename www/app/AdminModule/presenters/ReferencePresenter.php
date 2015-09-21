<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form,
	Nette\Utils\Image;

/**
 * Reference presenter
 */
class ReferencePresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	private $references;

	private $values;

	private $id;
	
	public function __construct(Model\ReferenceManager $references)
	{
		$this->references = $references;
		$this->values = array("name" => "", "logo_path" => "");
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->references = $this->references->getAll();
	}

	public function renderEdit($referenceId)
	{
		$this->template->reference = $this->references->getReference($referenceId);
		$logo_path = $this->references->getImage($referenceId);
		$this->template->logo_path = $logo_path;
	}

	/*Reference form*/
	public function createComponentReferenceForm()
	{
		$form = new Form;

		$form->addText("name", "Názov")
			 ->setRequired('Názov je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addTextArea("desc", "Popis")
			 ->setRequired('Popis je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addUpload("logo", "Logo")
			 ->addCondition(Form::FILLED)
			 
			 ->addRule(Form::IMAGE, "Obrázok musí byť JPEG, PNG alebo GIF");

		$form->addSubmit("add", "Pridať referenciu")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "referenceFormSucceeded");

		return $form;
	}

	public function referenceFormSucceeded($form, $values)
	{
		$adding = true;
		
		try {
			if ( isset($this->request->getParameters()['referenceId']) )
			{
				$referenceId = $this->getParameter('referenceId');
				$adding = false;
			}

			if ($adding)
			{
				$this->references->insert($values);
				$this->flashMessage('Logo úspešne pridané');
			}
			else
			{
				$this->references->edit($referenceId, $values);
				$this->flashMessage('Logo bolo aktualizované');
			}

				$this->redirect("Reference:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Logo neexistuje');
		}
	}

	public function actionRemove($referenceId)
	{
			$this->references->remove($referenceId);
			$this->flashMessage('Logo bolo úspešne vymazané');
			$this->redirect("Reference:");
	}

	public function actionEdit($referenceId)
	{
		$reference = $this->references->getReference($referenceId);

		$this->template->referenceId = $referenceId;

		$this['referenceForm']->setDefaults($reference->toArray());

	}

	public function actionRemoveImage($referenceId)
	{
		$this->references->removeImage($referenceId);

		$this->flashMessage('Obrázok bol úspešne vymazaný');
		$this->redirect('Reference:Edit', $referenceId);
	}
}