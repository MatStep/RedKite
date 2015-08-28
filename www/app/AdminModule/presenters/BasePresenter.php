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

    	// if ( !$this->autorisator->hasRole($this->getUser(), "admin") )
    	// 	$this->redirect('Lockscreen:');
    }
}
