<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form,
	Nette\Utils\Image;

/**
 * Feature presenter
 */
class FeaturePresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	private $features;

	private $values;

	private $id;
	
	public function __construct(Model\FeatureManager $features)
	{
		$this->features = $features;
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->features = $this->features->getAll();
	}

	public function renderEdit($featureId)
	{
		$this->template->feature = $this->features->getFeature($featureId);
	}

	/*Feature form*/
	public function createComponentFeatureForm()
	{
		$form = new Form;

		$form->addText("name", "Názov feature")
			 ->setRequired('Názov je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addSubmit("add", "Pridať feature")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "featureFormSucceeded");

		return $form;
	}

	public function featureFormSucceeded($form, $values)
	{
		$adding = true;
		$currentLanguage = parent::getLanguage();
		
		try {
			if ( isset($this->request->getParameters()['featureId']) )
			{
				$featureId = $this->getParameter('featureId');
				$adding = false;
			}

			if ($adding)
			{
				//ADD FEATURE
				$this->features->insert($values);

				$this->flashMessage('Feature úspešne pridaná');
			}
			else
			{
				//EDIT FEATURE
				$this->features->edit($featureId, $values);

				$this->flashMessage('Feature bola aktualizovaná');
			}

				$this->redirect("Feature:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Feature neexistuje');
		}
	}

	public function actionRemove($featureId)
	{
			$this->features->remove($featureId);
			$this->flashMessage('Feautre bola úspešne vymazaná');
			$this->redirect("Feature:");
	}


	public function actionEdit($featureId)
	{
		$feature = $this->features->getFeature($featureId);
		$this->template->featureId = $featureId;
		$this['featureForm']->setDefaults($feature->toArray());
	}
}