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

        $form->addText("name", "N�zov slu�by")
             ->setRequired('N�zov je povinn�')
             ->getControlPrototype()->class("form-control");

        $form->addText("desc", "Popis")
             ->setRequired('Popis je povinn�')
             ->getControlPrototype()->class("form-control");

        $form->addSubmit("add", "Prida� slu�bu")
             ->getControlPrototype()->class("btn btn-primary pull-right");

        $form->addSubmit("edit", "Ulo�i� zmeny")
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
                $this->flashMessage('Slu�ba �spe�ne pridan�');
            }
            else
            {
                $this->services->edit($serviceId, $values);
                $this->flashMessage('Sluzba bola aktualizovan�');
            }

    		$this->redirect('Service:');

    	} catch (Nette\Application\BadRequestException $e) {
    		if ($e->getMessage() == "NAME_EXISTS")
    			$form->addError('N�zov slu�by u� existuje');
    	}
    }

	public function actionRemove($serviceId)
	{
		$this->services->remove($serviceId);

		$this->flashMessage('Slu�ba bola �spe�ne vymazan�');
		$this->redirect("Service:");
	}

	public function actionEdit($serviceId)
	{
		$service = $this->services->getService($serviceId);

		$this->template->serviceId = $serviceId;

		$this['serviceForm']->setDefaults($service->toArray());

	}

}