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
		$this->values = array("name" => "", "desc" => "");
		$this->id = 0;

	}

	public function renderDefault()
	{
		$this->template->services = $this->services->getAll()->order('name');
	}

    /*Service form*/
	protected function createComponentServiceForm()
    {
        $form = new Form;

        $form->addText("name", "Názov služby")
             ->setRequired('Názov je povinný')
             ->getControlPrototype()->class("form-control");

        $form->addText("desc", "Popis")
             ->setRequired('Popis je povinný')
             ->getControlPrototype()->class("form-control");

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

    	if ( isset($this->request->getParameters()['serviceId']) )
    	{
    		$serviceId = $this->getParameter('serviceId');
    		$adding = false;
    	}

    	try {
    		if ($adding)
            {
                $this->services->insert($values);
                $this->flashMessage('Služba úspešne pridaná');
            }
            else
            {
                $this->services->edit($serviceId, $values);
                $this->flashMessage('Služba bola aktualizovaná');
            }

    		$this->redirect('Service:');

    	} catch (Nette\Application\BadRequestException $e) {
    		if ($e->getMessage() == "NAME_EXISTS")
    			$form->addError('Názov služby už existuje');
    	}
    }

	public function actionRemove($serviceId)
	{
		$this->services->remove($serviceId);

		$this->flashMessage('Služba bola úspešne vymazaná');
		$this->redirect("Service:");
	}

	public function actionEdit($serviceId)
	{
		$service = $this->services->getService($serviceId);

		$this->template->serviceId = $serviceId;

		$this['serviceForm']->setDefaults($service->toArray());

	}

}