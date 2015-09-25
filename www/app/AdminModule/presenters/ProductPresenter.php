<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form;

/**
 * Product presenter
 */
class ProductPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	private $products;

	private $values;

	private $id;
	
	public function __construct(Model\ProductManager $products)
	{
		$this->products = $products;
		$this->values = array("status" => "", "order" => "", "price_sell" => "");
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->products = $this->products->getAll();
	}

	/*Product form*/
	public function createComponentProductForm()
	{
		$form = new Form;

		foreach(parent::getAllLanguages() as $lang)
        {
            if($lang->id == parent::getLanguage()->id)
            {
                $form->addText("name", "Názov" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control")
                     ->setRequired('Názov je povinný');
                $form->addText("short_desc", "Krátky popis" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control")
                     ->setRequired('Krátky popis je povinný');
                $form->addTextArea("desc", "Popis" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control");
            }
        }

		$form->addText("price_sell", "Cena")
			 ->setType('number')
			 ->setRequired('Cena je povinná')
			 ->addRule(Form::FLOAT, "Kurz musí byť číslo")
			 ->getControlPrototype()->class("form-control");

		$form->addCheckbox("status", "");

		$form->addSubmit("add", "Pridať produkt")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "productFormSucceeded");

		return $form;
	}

	public function productFormSucceeded($form, $values)
	{
		$adding = true;
		$currentLanguage = parent::getLanguage();

		if( $form['status']->getValue() == 'checked')
		{
			$values->status = 1;
		}
		
		try {
			if ( isset($this->request->getParameters()['productId']) )
			{
				$productId = $this->getParameter('productId');
				$adding = false;
			}

			if ($adding)
			{
				//ADD PRODUCT
				$this->products->insert($values);

				//ADD LANGUAGE DATA
				$lastId = $this->products->getLastInsertedId();

				//Add the same for all languages
                foreach(parent::getAllLanguages() as $lang) {
                    $this->products->translateData($lang->id, $lastId, $values, 0);
                }

				$this->flashMessage('Produkt úspešne pridaný');
			}
			else
			{
				//EDIT PRODUCT
				$this->products->edit($productId, $values);

				//EDIT LANGUAGE DATA
                $this->products->translateData($currentLanguage, $productId, $values, 1);

				$this->flashMessage('Produkt bol aktualizovaný');
			}

				$this->redirect("Product:");
				
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Názov produktu už existuje');
		}
	}

	public function getProductLang($productId)
    {
        return $this->products->getProductLang($productId, parent::getLanguage()->id);
    }

	public function actionRemove($productId)
	{
			$this->products->remove($productId);
			$this->flashMessage('Produkt bol úspešne vymazaný');
			$this->redirect("Product:");
	}

	public function actionEdit($productId)
	{
		$product = $this->products->getProduct($productId);
		$lang = parent::getLanguage();
		$productLang = $this->products->getProductLang($productId, $lang->id);

		$this->template->productId = $productId;

		$this['productForm']->setDefaults($product->toArray());
		$this['productForm']['name']->setDefaultValue($productLang->name);
		$this['productForm']['short_desc']->setDefaultValue($productLang->short_desc);
		$this['productForm']['desc']->setDefaultValue($productLang->desc);

	}
}