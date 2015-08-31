<?php

namespace App\AdminModule\Presenters;


/**
 * Admin Base presenter
 */
class BasePresenter extends \App\Presenters\BasePresenter
{

	public function startup()
    {
    	parent::startup();
    	$user = $this->getUser();
    	if(!$user->isLoggedIn()) {
    		$this->flashMessage('Pre vstup do administrácie je potrebné sa prihlásiť');
    		$this->redirect(':Homepage:');
    	}
    }
}
