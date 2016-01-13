<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,	
    Nette\Application\UI\Form as Form;

/**
 * Admin presenter
 */
class AdminPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	/*Before render template redirect if user has no rights to enter the page*/
	public function beforeRender()
	{
		if(!$this->userManager->hasRole($this->getUser(), "admin")) {
			$this->flashMessage('Nemáte práva na vstup do sekcie Admin');
			$this->redirect('Homepage:');
		}
	}

	public function renderDefault()
	{
		$loggedUser = $this->getUser();
		
		$users = $this->userManager->getUsers();

		$this->template->loggedUser = $loggedUser;

		$this->template->users = $users;
	}

	public function renderEdit($userId)
	{
		$user = $this->userManager->getUser($userId);

		if (!$user) {
			$this->flashMessage('Stránka nebola nájdená');
			$this->redirect(':Homepage');
		}
	}

	/*
	 * User form
	 */
	public function createComponentUserForm()
	{
		$form = new Form;

		$roles = array(
			'admin' => 'admin',
			'user' => 'user'
			);

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

        $form->addSelect("role", "Roľa:", $roles)
        	 ->setRequired('Roľa je povinná')
        	 ->setDefaultValue('user');
        $form['role']->getControlPrototype()->class('form-control');

		$form->addSubmit("add", "Pridať používateľa")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "userFormSucceeded");

		return $form;
	}

	public function userFormSucceeded($form, $values)
	{
		$adding = true;
		
		try {
			if ( isset($this->request->getParameters()['userId']) )
			{
				$userId = $this->getParameter('userId');
				$adding = false;
			}

			if ($adding)
			{
				$this->userManager->addUser($values);
				$this->flashMessage('Používateľ úspešne pridaný');
			}
			else
			{
				$this->userManager->editUser($userId, $values);
				$this->flashMessage('Používateľ bol aktualizovaný');
			}

				$this->redirect("Admin:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Používateľ neexistuje');
		}
	}

	public function actionRemove($userId)
	{
		if($userId != $this->getUser()->id)
		{
			$this->userManager->removeUser($userId);
			$this->flashMessage('Používateľ bol úspešne vymazaný');
			$this->redirect("Admin:");
		}
		else
		{
			$this->flashMessage('Nemožno vymazať prihláseného používateľa');
			$this->redirect("Admin:");
		}
	}

	public function actionEdit($userId)
	{
		$user = $this->userManager->getUser($userId);

		$this->template->userId = $userId;

		$this['userForm']->setDefaults($user->toArray());

	}

}