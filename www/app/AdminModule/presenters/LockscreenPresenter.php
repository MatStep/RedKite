<?php

namespace App\AdminModule\Presenters;

use Nette\Application\UI;

/**
 *Lockscreen presenter
 */
class LockscreenPresenter extends \App\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
    public $userManager;

    public function startup()
	{
		parent::startup();

	}

	protected function createComponentLoginForm()
    {
        $form = new UI\Form;
        
        $form->addText('name')
        		->setAttribute('class', 'form-control')
        		->setAttribute('placeholder', 'Meno')
        		->setRequired('Meno je povinné');
        
        $form->addPassword('password')
        		->setAttribute('class', 'form-control')
        		->setAttribute('placeholder', 'Heslo')
        		->setRequired('Heslo je povinné');

        $form->addSubmit('loginButton', 'Login')
        			->setAttribute('class', 'btn');
        	
        $form->onSuccess[] = array($this, 'LoginFormSucceeded');

        return $form;
    }

    public function LoginFormSucceeded(UI\Form $form, $values)
    {   
    	try
    	{
    		$user = $this->getUser();
    		$user->login($this->userManager->authenticate(array($values->name, $values->password)));

         //    if ( !$this->autorisator->hasRole($user, "admin") )
        	// {
        	// 	$form->addError('Nedovolený vstup');
        	// }
        	// else
        	// {
    			$this->flashMessage('Prihlásenie prebehlo úspešne');
    			$this->redirect('Homepage:default');
            // }

    	}
    	catch(\Nette\Security\AuthenticationException $e)
    	{
    		$form->addError('Nesprávne meno alebo heslo');
    	}
    }

    public function actionOut() 
    {
        $this->getUser()->logout(TRUE);
        $this->flashMessage('Odhlásenie prebehlo úspešne');
        $this->redirect(':Homepage:');
    }
}