<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form,
    Tracy\Debugger;

/**
 * Service presenter
 */
class ServicePresenter extends \App\AdminModule\Presenters\BasePresenter
{
	private $services;

    private $attributes;

	private $values;

	private $id;


	public function __construct(Model\ServiceManager $services, Model\AttributeManager $attributes)
	{
		$this->services = $services;
        $this->attributes = $attributes;
		$this->values = array("img_path" => "");
		$this->id = 0;

	}

	public function renderDefault($serviceId)
	{
        if($serviceId == NULL) {
            $this->redirect("Service:All");
        }
        $service = $this->services->getService($serviceId);
        $this->template->service = $service;
        $this->template->row = $this->attributes->getAttribute($service->row_id);
        $this->template->col = $this->attributes->getAttribute($service->col_id);
		$this->template->services = $this->services->getAll();
	}

    public function renderAll()
    {
        $this->template->services = $this->services->getAll();
    }

    public function renderEdit($serviceId)
    {
        $this->template->service = $this->services->getService($serviceId);
        $img_path = $this->services->getImage($serviceId);
        $this->template->img_path = $img_path;
    }

    /*
     * Service form
     */
	protected function createComponentServiceForm()
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
                $form->addText("name_row", "Názov" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control")
                     ->setRequired('Názov je povinný');
                $form->addText("name_col", "Názov" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control")
                     ->setRequired('Názov je povinný');
            }
        }

        $form->addUpload("image", "Obrázok")
             ->addCondition(Form::FILLED)
             
             ->addRule(Form::IMAGE, "Obrázok musí byť JPEG, PNG alebo GIF");

        $form->addSubmit("add", "Pridať službu")
             ->getControlPrototype()->class("btn btn-primary pull-right");

        $form->addSubmit("edit", "Uložiť zmeny")
             ->getControlPrototype()->class("btn btn-primary pull-right");

        $form->onSuccess[] = array($this, "serviceFormSucceeded");

        return $form;
    }

    public function ServiceFormSucceeded($form, $values)
    {
    	$adding = true;
        $currentLanguage = parent::getLanguage();

    	if ( isset($this->request->getParameters()['serviceId']) )
    	{
    		$serviceId = $this->getParameter('serviceId');
    		$adding = false;
    	}

    	try {
    		if ($adding)
            {
                //ADD ATTRIBUTES
                $attribute1 = $this->attributes->insert($values['name_row']);
                $attribute2 = $this->attributes->insert($values['name_col']);

                $values['attribute1'] = $attribute1;
                $values['attribute2'] = $attribute2;

                //ADD SERVICE
                $service = $this->services->insert($values);

                //ADD LANGUAGE DATA
                $lastId = $this->services->getLastInsertedId();

                //Add the same for all languages
                foreach(parent::getAllLanguages() as $lang) {
                    $this->services->translateData($lang->id, $lastId, $values, 0);
                }

                $this->flashMessage('Služba úspešne pridaná');
            }
            else
            {
                $service = $this->services->getService($serviceId);

                //EDIT ATTRIBUTES
                $attribute1 = $this->attributes->edit($service->row_id, $values['name_row']);
                $attribute2 = $this->attributes->edit($service->col_id, $values['name_col']);

                $values['attribute1'] = $attribute1;
                $values['attribute2'] = $attribute2;

                //EDIT SERVICE
                $this->services->edit($serviceId, $values);

                //EDIT LANGUAGE DATA
                $this->services->translateData($currentLanguage, $serviceId, $values, 1);

                $this->flashMessage('Služba bola aktualizovaná');
            }

    		$this->redirect('Service:', $service->id);

    	} catch (Nette\Application\BadRequestException $e) {
    		if ($e->getMessage() == "NAME_EXISTS")
    			$form->addError('Názov služby už existuje');
    	}
    }

    /*
     * Attribute form
     */
    protected function createComponentAttributeValueForm()
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

        $form->addText("from", "Počet od")
             ->setType('number')
             ->addRule(Form::INTEGER, 'Zadaná hodnota musí byť číslo');

        $form->addText("to", "Počet do")
             ->setType('number')
             ->addRule(Form::INTEGER, 'Zadaná hodnota musí byť číslo');

        $form->addSubmit("add", "Pridať")
             ->getControlPrototype()->class("btn btn-primary pull-right");

        $form->addSubmit("edit", "Uložiť zmeny")
             ->getControlPrototype()->class("btn btn-primary pull-right");

        $form->onSuccess[] = array($this, "attributeValueFormSucceeded");

        return $form;
    }

    public function AttributeValueFormSucceeded($form, $values)
    {
        $adding = true;
        $currentLanguage = parent::getLanguage();

        if ( isset($this->request->getParameters()['attributeId']) )
        {
            $attributeId = $this->getParameter('attributeId');
            $adding = false;
        }

        try {
            if ($adding)
            {
                //ADD Attribute Values
                //TODO add insertValues
                $this->attributes->insertValues($values);
                $this->attributes->insertValues($values);

                $this->flashMessage('Tabuľka úspešne vytvorená');
            }
            else
            {
                //EDIT ATTRIBUTE
                $this->attributes->editValues($attributeId, $values);

                $this->flashMessage('Služba bola aktualizovaná');
            }

            $this->redirect('this');

        } catch (Nette\Application\BadRequestException $e) {
            if ($e->getMessage() == "NAME_EXISTS")
                $form->addError('Názov atribútu už existuje');
        }
    }

    public function getServiceLang($serviceId)
    {
        return $this->services->getServiceLang($serviceId, parent::getLanguage()->id);
    }

    public function getAttributeLang($attributeId)
    {
        return $this->attributes->model->getFirstSecond($attributeId, parent::getLanguage()->id, 'attribute', 'lang');;
    }

	public function actionRemove($serviceId)
	{
        $service = $this->services->getService($serviceId);
        $attributeRow = $service->row_id;
        $attributeCol = $service->col_id;

        //delete service
		$this->services->remove($serviceId);

        //delete attributes
        $this->attributes->remove($attributeRow);
        $this->attributes->remove($attributeCol);

		$this->flashMessage('Služba bola úspešne vymazaná');
		$this->redirect("Service:");
	}

    public function actionRemoveImage($serviceId)
    {
        $this->services->removeImage($serviceId);

        $this->flashMessage('Obrázok bol úspešne vymazaný');
        $this->redirect('Service:Edit', $serviceId);
    }

	public function actionEdit($serviceId)
	{
		$service = $this->services->getService($serviceId);
        $lang = parent::getLanguage();
        $serviceLang = $this->services->getServiceLang($serviceId, $lang->id);
        $attributeRow = self::getAttributeLang($service->row_id);
        $attributeCol = self::getAttributeLang($service->col_id);

		$this->template->serviceId = $serviceId;

		$this['serviceForm']->setDefaults($service->toArray());
        $this['serviceForm']['name']->setDefaultValue($serviceLang->name);
        $this['serviceForm']['desc']->setDefaultValue($serviceLang->desc);
        $this['serviceForm']['name_row']->setDefaultValue($attributeRow->name);
        $this['serviceForm']['name_col']->setDefaultValue($attributeCol->name);

	}

}