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

	/** @var App\Model\LanguageManager */
    public $languages;

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

	public function renderAddValue($featureId)
	{
		$this->template->features = $this->features->getFeature($featureId);
		$this->template->feature = $this->features->getFeature($featureId);
	}

	/*Feature form*/
	public function createComponentFeatureForm()
	{
		$form = new Form;

		foreach(parent::getAllLanguages() as $lang)
        {
            if($lang->id == parent::getLanguage()->id)
            {
                $form->addText("name", "Názov" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control")
                     ->setRequired('Názov je povinný');
            }
        }

		$form->addSubmit("add", "Pridať vlastnosť")
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

				$this->flashMessage('Vlastnosť úspešne pridaná');
			}
			else
			{
				//EDIT FEATURE
				$this->features->edit($featureId, $values);

				$this->flashMessage('Vlastnosť bola aktualizovaná');
			}

				$this->redirect("Feature:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Vlastnosť s danným menom už existuje');
		}
	}

	/*Feature value form*/
	public function createComponentFeatureValueForm()
	{
		$form = new Form;

		foreach(parent::getAllLanguages() as $lang)
        {
            if($lang->id == parent::getLanguage()->id)
            {
                $form->addText("value", "Hodnota" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control")
                     ->setRequired('Hodnota je povinná');
            }
        }

		$form->addSubmit("add", "Pridať hodnotu")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "featureValueFormSucceeded");

		return $form;
	}

	public function featureValueFormSucceeded($form, $values)
	{
		$adding = true;
		$currentLanguage = parent::getLanguage();
		
		try {
			if ( isset($this->request->getParameters()['featureValueId']) )
			{
				$featureValueId = $this->getParameter('featureValueId');
				$adding = false;
			}
			if ( isset($this->request->getParameters()['featureId']) )
			{
				$featureId = $this->getParameter('featureId');
			}

			if ($adding)
			{
				//ADD FEATURE
				$this->features->insertFeatureValue($featureId, $values);

				$this->flashMessage('Hodnota úspešne pridaná');
			}
			else
			{
				//EDIT FEATURE
				$this->features->editFeatureValue($featureValueId, $values);

				$this->flashMessage('Hodnota bola aktualizovaná');
			}

				$this->redirect("Feature:addValue", $featureId);
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Hodnota s danným menom už existuje');
		}
	}

	public function getFeatureLang($featureId)
    {
        return $this->features->model->getFirstSecond($featureId, parent::getLanguage()->id, 'feature', 'lang');
    }

    public function getFeatureValueLang($featureValueId)
    {
        return $this->features->model->getFirstSecond($featureValueId, parent::getLanguage()->id, 'feature_value', 'lang');
    }

    public function getFeatureValues($featureId)
    {
        return $this->features->model->getAllFirstSecond($featureId, 'feature', 'value');
    }

	public function actionRemove($featureId)
	{
			$this->features->remove($featureId);
			$this->flashMessage('Vlastnosť bola úspešne vymazaná');
			$this->redirect("Feature:");
	}

	public function actionRemoveFeatureValue($featureValueId)
	{
			$this->features->removeFeatureValue($featureValueId);
			$this->flashMessage('Vlastnosť bola úspešne vymazaná');
			$this->redirect("Feature:");
	}


	public function actionEdit($featureId)
	{
		$feature = $this->features->getFeature($featureId);
		$featureLang = self::getFeatureLang($featureId);
		$this->template->featureId = $featureId;
		$this['featureForm']->setDefaults($feature->toArray());
		$this['featureForm']['name']->setDefaultValue($featureLang->name);
	}


	public function actionEditFeatureValue($featureValueId)
	{
		$featureValue = $this->featureValues->getFeatureValue($featureValueId);
		$featureValueLang = self::getFeatureValueLang($featureValueId);
		$this->template->featureValueId = $featureValueId;
		$this['featureValueForm']->setDefaults($featureValue->toArray());
		$this['featureValueForm']['value']->setDefaultValue($featureValueLang->value);
	}
}