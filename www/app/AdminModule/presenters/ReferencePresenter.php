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
		$this->values = array("logo_path" => "");
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

	/*
	 * Reference form
	 */
	public function createComponentReferenceForm()
	{
		$form = new Form;

		foreach(parent::getAllLanguages() as $lang)
        {
            if($lang->id == parent::getLanguage()->id)
            {
                $form->addText("name", "Názov" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control")
                     ->setRequired('Názov je povinný');
                $form->addTextArea("desc", "Popis" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control");
            }
        }

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
		$currentLanguage = parent::getLanguage();
		
		try {
			if ( isset($this->request->getParameters()['referenceId']) )
			{
				$referenceId = $this->getParameter('referenceId');
				$adding = false;
			}

			if ($adding)
			{
				//ADD REFERENCE
				$this->references->insert($values);

				//ADD LANGUAGE DATA
				$lastId = $this->references->getLastInsertedId();

				//Add the same for all languages
                foreach(parent::getAllLanguages() as $lang) {
                    $this->references->translateData($lang->id, $lastId, $values, 0);
                }

				$this->flashMessage('Referencia úspešne pridaná');
			}
			else
			{
				//EDIT REFERENCE
				$this->references->edit($referenceId, $values);

				//EDIT LANGUAGE DATA
                $this->references->translateData($currentLanguage, $referenceId, $values, 1);

				$this->flashMessage('Referencia bola aktualizovaná');
			}

				$this->redirect("Reference:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Referencia neexistuje');
		}
	}

	public function getReferenceLang($referenceId)
    {
        return $this->references->getReferenceLang($referenceId, parent::getLanguage()->id);
    }

	public function actionRemove($referenceId)
	{
			$this->references->remove($referenceId);
			$this->flashMessage('Referencia bola úspešne vymazaná');
			$this->redirect("Reference:");
	}

	public function actionRemoveImage($referenceId)
	{
		$this->references->removeImage($referenceId);

		$this->flashMessage('Obrázok bol úspešne vymazaný');
		$this->redirect('Reference:Edit', $referenceId);
	}

	public function actionEdit($referenceId)
	{
		$reference = $this->references->getReference($referenceId);
		$lang = parent::getLanguage();
		$referenceLang = $this->references->getReferenceLang($referenceId, $lang->id);

		$this->template->referenceId = $referenceId;

		$this['referenceForm']->setDefaults($reference->toArray());
		$this['referenceForm']['logo']->setDefaultValue($reference->logo_path);
		$this['referenceForm']['name']->setDefaultValue($referenceLang->name);
		$this['referenceForm']['desc']->setDefaultValue($referenceLang->desc);
	}
}