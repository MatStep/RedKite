<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form;

/**
 * Service presenter
 */
class ServicePresenter extends \App\AdminModule\Presenters\BasePresenter
{
	private $services;

	private $values;

	private $id;


	public function __construct(Model\ServiceManager $services)
	{
		$this->services = $services;
		$this->values = array("name" => "", "desc" => "", "img_path" => "");
		$this->id = 0;

	}

	public function renderDefault()
	{
		$this->template->services = $this->services->getAll();
	}

    public function renderEdit($serviceId)
    {
        $this->template->service = $this->services->getService($serviceId);
        $img_path = $this->services->getImage($serviceId);
        $this->template->img_path = $img_path;
    }

    /*Service form*/
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
                //ADD SERVICE
                $this->services->insert($values);

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
                //EDIT SERVICE
                $this->services->edit($serviceId, $values);

                //EDIT LANGUAGE DATA
                $this->services->translateData($currentLanguage, $serviceId, $values, 1);

                $this->flashMessage('Služba bola aktualizovaná');
            }

    		$this->redirect('Service:');

    	} catch (Nette\Application\BadRequestException $e) {
    		if ($e->getMessage() == "NAME_EXISTS")
    			$form->addError('Názov služby už existuje');
    	}
    }

    public function getServiceLang($serviceId)
    {
        return $this->services->getServiceLang($serviceId, parent::getLanguage()->id);
    }

	public function actionRemove($serviceId)
	{
		$this->services->remove($serviceId);

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

		$this->template->serviceId = $serviceId;

		$this['serviceForm']->setDefaults($service->toArray());
        $this['serviceForm']['name']->setDefaultValue($serviceLang->name);
        $this['serviceForm']['desc']->setDefaultValue($serviceLang->desc);

	}

}