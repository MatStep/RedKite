<?php

namespace App\Presenters;

use Nette,
	App\Model,
	Latte\Engine,
	Nette\Application\UI\Form as Form;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    protected function createTemplate($class = NULL)
	{
	    $template = parent::createTemplate($class);
        $template->presenterName = $this->name;

        $this->translator->createTemplateHelpers()->register($template->getLatte());

	    return $template;
	}
}
