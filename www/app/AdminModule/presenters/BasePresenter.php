<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form;

/**
 * Admin Base presenter
 */
class BasePresenter extends \App\Presenters\BasePresenter
{
	/** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    /** @var App\Model\LanguageManager */
    private $languages;

    /** @var string */
    private $currentLanguage;

    /** @var string temporary variable */
    private $path;

	private $taxes;

	private $currencies;

	public function inject(Model\TaxManager $taxes, Model\CurrencyManager $currencies, Model\LanguageManager $languages)
	{
		$this->taxes = $taxes;
		$this->currencies = $currencies;
		$this->languages = $languages;
	}

	protected function createTemplate($class = NULL)
	{
	    $template = parent::createTemplate($class);
        $template->presenterName = $this->name;

        $this->translator->createTemplateHelpers()->register($template->getLatte());

	    return $template;
	}

	public function startup()
    {
    	parent::startup();
    	$user = $this->getUser();
    	if(!$user->isLoggedIn()) {
    		$this->flashMessage('Pre vstup do administrácie je potrebné sa prihlásiť');
    		$this->redirect(':Homepage:');
    	}

    	$this->template->taxes = $this->taxes->getAll();
    	$this->template->tax = $this->taxes->getActiveTax();
    	$this->template->currencies = $this->currencies->getAll();
    	$this->template->currency = $this->currencies->getActiveCurrency();

    	$this->template->lang = $this->languages->getLanguage();
    	$this->template->lang_czech = $this->languages->changeLanguage('cs');
    	$this->template->lang_slovak = $this->languages->changeLanguage('sk');
    }

    /*Tax settings*/
    public function createComponentTaxSettings()
    {
    	$form = new Form;

    	$form->getElementPrototype()->class('ajax');

    	$taxes = $this->taxes->getAllTaxesAsArray();

    	$select = $form->addSelect('tax', 'Dane', $taxes)
	    		->setAttribute('class', 'form-control');

	    $form->onSuccess[] = array($this, "taxSettingsSucceeded");

	    return $form;
    }

    public function taxSettingsSucceeded($form, $values)
	{
		$taxId = $this->getParameter('id');
		$this->taxes->setActive($values);
		$this->flashMessage('Nastavenie dane bolo zmenené');
		$this->redirect("this");
	}

	/*Currency settings*/
    public function createComponentCurrencySettings()
    {
    	$form = new Form;

    	$form->getElementPrototype()->class('ajax');

    	$currencies = $this->currencies->getAllCurrenciesAsArray();

    	$select = $form->addSelect('currency', 'Dane', $currencies)
	    		->setAttribute('class', 'form-control');

	    $form->onSuccess[] = array($this, "currencySettingsSucceeded");

	    return $form;
    }

    public function currencySettingsSucceeded($form, $values)
	{
		$currencyId = $this->getParameter('id');
		$this->currencies->setActive($values);
		$this->flashMessage('Nastavenie dane bolo zmenené');
		$this->redirect("this");
	}
}
