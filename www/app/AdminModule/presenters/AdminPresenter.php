<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,	
    Nette\Application\UI\Form as Form;

class AdminPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	public function renderDefault()
	{
		$user = $this->getUser();
		
		$users = $this->userManager->getUsers();

		$this->template->users = $users;
	}

	public function hello(){
		echo "hello";
	}

	public function actionRemove($userId)
	{
		$this->userManager->removeUser($userId);

		$this->flashMessage('Používateľ bol úspešne vymazaný');
		$this->redirect("Admin:");
	}

	public function createComponentAddUserForm()
	{
		$form = new Form;

		$form->addText("name", "Meno")
			 ->setRequired('Meno je povinné')
			 ->getControlPrototype()->class("form-control");

		$form->addText("email", "E-mail")
			 ->getControlPrototype()->class("form-control");

		$form->addPassword('password', 'Heslo', 20)
             ->setAttribute('placeholder', 'Heslo')
             ->setOption('description', 'aspoň 6 znakov')
             ->setRequired('heslo je povinné')
             ->addRule(Form::MIN_LENGTH, 'Heslo musí byž aspoň %d znakov dlhé', 6);
        $form['password']->getControlPrototype()->class('form-control');

        $form->addPassword('password2', 'Potvrdenie hesla', 20)
             ->setAttribute('placeholder', 'Potvrdenie hesla')
             ->addConditionOn($form['password'], Form::VALID)
             ->setRequired('heslo je povinné')
             ->addRule(Form::EQUAL, 'Heslá sa nezhodujú', $form['password']);
        $form['password2']->getControlPrototype()->class('form-control');

		$form->addSubmit("submit", "Submit")
			 ->getControlPrototype()->class("btn btn-primary btn-block");

		$form->onSuccess[] = array($this, "userAddFormSucceeded");

		return $form;
	}

	public function userAddFormSucceeded($form, $values)
	{
			$this->userManager->addUser($values);

			$this->flashMessage('Používateľ úspešne pridaný');
			$this->redirect("Admin:");
	}


}