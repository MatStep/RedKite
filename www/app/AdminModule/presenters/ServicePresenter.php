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

        //Service
        $service = $this->services->getService($serviceId);
        $this->template->service = $service;

        //Row
        $row = $this->attributes->getAttribute($service->row_id);
        $this->template->row = $row;

        //Col
        $col = $this->attributes->getAttribute($service->col_id);
        $this->template->col = $col;

        //Rows
        $rows = $this->attributes->getAllAttributeValues($row->id);
        $this->template->rows = $rows;

        //Cols
        $cols = $this->attributes->getAllAttributeValues($col->id);
        $this->template->cols = $cols;

        //Services
		$this->template->services = $this->services->getAll();

        $this->template->table_cells = $rows->count() * $cols->count();

        //Values
        $this->template->values = $this->attributes->getAllServiceAttributeValues($serviceId);
	}

    public function renderAddValue($serviceId, $attributeId)
    {
        $this->template->service = $this->services->getService($serviceId);
        $this->template->attribute = $this->attributes->getAttribute($attributeId);
    }

    public function renderEditValue($serviceId, $attributeId, $attributeValueId)
    {
        $this->template->service = $this->services->getService($serviceId);
        $this->template->attribute = $this->attributes->getAttribute($attributeId);
        $this->template->attributeValue = $this->attributes->getAttributeValue($attributeValueId);
    }

    public function renderEditPrice($serviceId, $valueId1, $valueId2, $serviceAttributeValueId)
    {
        $this->template->service = $this->services->getService($serviceId);
        $this->template->value_id1 = $this->attributes->getAttributeValue($valueId1);
        $this->template->value_id2 = $this->attributes->getAttributeValue($valueId2);
        $this->template->service_attribute_value_id = $this->attributes->getValueByAttributeValueId($valueId1, $valueId2);
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
     * Attribute value form
     */
    protected function createComponentAttributeValueForm()
    {
        $form = new Form;

        foreach(parent::getAllLanguages() as $lang)
        {
            if($lang->id == parent::getLanguage()->id)
            {
                $form->addText("name", "Názov" . "(" . $lang->iso_code . ")")
                     ->setRequired('Názov je povinný');
            }
        }

        $form->addText("from", "Počet od")
             ->setType('number');

        $form->addText("to", "Počet do")
             ->setType('number');

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

        if ( isset($this->request->getParameters()['attributeValueId']) )
        {
            $attributeValueId = $this->getParameter('attributeValueId');
            $adding = false;
        }

        $serviceId = $this->getParameter('serviceId');
        $attributeId = $this->getParameter('attributeId');
        $values['attributeId'] = $attributeId;

        try {
            if ($adding)
            {
                $this->attributes->insertAttributeValue($values);

                $this->flashMessage('Tabuľka bola upravená');
            }
            else
            {

                $this->attributes->editAttributeValue($attributeValueId, $values);

                $this->flashMessage('Tabuľka bola aktualizovaná');
            }

            $this->redirect('Service:', $serviceId);

        } catch (Nette\Application\BadRequestException $e) {
            if ($e->getMessage() == "NAME_EXISTS")
                $form->addError('Názov atribútu už existuje');
        }
    }

    /*
     * Service attribute value form
     */
    protected function createComponentServiceAttributeValueForm()
    {
        $form = new Form;

        $form->addText("price_sell", "Predajná cena")
             ->setAttribute('placeholder', '1.25')
             ->setRequired('Údaj je povinný.');

        $form->addHidden("attribute_value_id1");
        $form->addHidden("attribute_value_id2");

        $form->addSubmit("add", "Pridať")
             ->getControlPrototype()->class("btn btn-primary pull-right");

        $form->addSubmit("edit", "Uložiť zmeny")
             ->getControlPrototype()->class("btn btn-primary pull-right");

        $form->onSuccess[] = array($this, "serviceAttributeValueFormSucceeded");

        return $form;
    }

    public function ServiceAttributeValueFormSucceeded($form, $values)
    {
        $adding = true;
        $currentLanguage = parent::getLanguage();

        if ( isset($this->request->getParameters()['valueId1'])
         &&  isset($this->request->getParameters()['valueId2'])
         &&  isset($this->request->getParameters()['serviceAttributeValueId']))
        {
            $attributeValueId1 = $this->getParameter('valueId1');
            $attributeValueId2 = $this->getParameter('valueId2');
            $serviceAttributeValueId = $this->getParameter('serviceAttributeValueId');
            $values['attribute_value_id1'] = $attributeValueId1;
            $values['attribute_value_id2'] = $attributeValueId2;
            $adding = false;
        }
        else
        {
            $attributeValueId1 = $values['attribute_value_id1'];
            $attributeValueId2 = $values['attribute_value_id2'];
        }

        $serviceId = $this->getParameter('serviceId');

        try {
            if ($adding)
            {
                $this->attributes->insertServiceAttributeValue($values);

                $this->flashMessage('Údaj bol pridaný');
            }
            else
            {

                $this->attributes->editServiceAttributeValue($serviceAttributeValueId, $values);

                $this->flashMessage('Údaj bol aktualizovaný');
            }

            $this->redirect('Service:', $serviceId);

        } catch (Nette\Application\BadRequestException $e) {
            if ($e->getMessage() == "NAME_EXISTS")
                $form->addError('Názov atribútu už existuje');
            if ($e->getMessage() == "ALREADY_EXISTS")
            {
                $form->addError('Hodnota pre dané políčko už je v databáze zapísané');
                $this->flashMessage('Hodnota pre dané políčko už je v databáze zapísané');
                $this->redirect('Service:', $serviceId);
            }
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

    public function getAttributeValueLang($attributeValueId)
    {
        return $this->attributes->model->getFirstSecond($attributeValueId, parent::getLanguage()->id, 'attribute_value', 'lang');;
    }

    public function existValue($id1, $id2)
    {
        return $this->attributes->existValue($id1, $id2);
    }

    public function getValueByAttributeValueId($id1, $id2)
    {
        return $this->attributes->getValueByAttributeValueId($id1, $id2);
    }

    // Get values from values result set that get data from DB query and compare all id's with attributes to get the data
    public function getFromValues($values, $id1, $id2)
    {
        foreach($values as $value)
        {
            if($value->attribute_value_id1 == $id1 && $value->attribute_value_id2 == $id2)
                return $value;
        }
    }

	public function actionRemove($serviceId)
	{
        $service = $this->services->getService($serviceId);
        $attributeRow = $service->row_id;
        $attributeCol = $service->col_id;
        $attributeValuesRow = $this->attributes->getAllAttributeValues($attributeRow);
        $attributeValuesCol = $this->attributes->getAllAttributeValues($attributeCol);

        // Get all values for service
        $values = $this->attributes->getAllServiceAttributeValues($serviceId);

        foreach($values as $value)
        {
            // sav_id is id from DB query
            $this->attributes->removeServiceAttributeValue($value->sav_id);
        }

        //delete attribute_values
        foreach($attributeValuesRow as $r)
        {
            $this->attributes->removeAttributeValue($r->id);
        }
        foreach($attributeValuesCol as $c)
        {
            $this->attributes->removeAttributeValue($c->id);
        }

        //delete service
        $this->services->remove($serviceId);

        //delete attributes
        $this->attributes->remove($attributeRow);
        $this->attributes->remove($attributeCol);

		$this->flashMessage('Služba bola úspešne vymazaná');
		$this->redirect("Service:");
	}

    public function actionRemoveAttributeValue($serviceId, $attributeValueId)
    {
        $attribute = $this->attributes->getAttributeValue($attributeValueId);

        // Get all values for service
        $values = $this->attributes->getAllServiceAttributeValues($serviceId);

        foreach($values as $value)
        {
            if($value->attribute_value_id1 == $attributeValueId || $value->attribute_value_id2 == $attributeValueId)
            {
                // sav_id is id from DB query
                $this->attributes->removeServiceAttributeValue($value->sav_id);
            }
        }

        $this->attributes->removeAttributeValue($attributeValueId);

        $this->flashMessage('Hodnota bola úspešne vymazaná');
        $this->redirect("Service:", $serviceId);
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

    public function actionEditValue($serviceId, $attributeId, $attributeValueId)
    {
        $attributeValue = $this->attributes->getAttributeValue($attributeValueId);
        $lang = parent::getLanguage();
        $attributeValueLang = self::getAttributeValueLang($attributeValueId);

        $this->template->attributeValueId = $attributeValueId;

        $this['attributeValueForm']->setDefaults($attributeValue->toArray());
        $this['attributeValueForm']['name']->setDefaultValue($attributeValueLang->name);

    }

    public function actionEditPrice($serviceId, $valueId1, $valueId2, $serviceAttributeValueId)
    {
        $value = $this->attributes->getServiceAttributeValue($serviceAttributeValueId);

        $this['serviceAttributeValueForm']->setDefaults($value->toArray());

    }

}