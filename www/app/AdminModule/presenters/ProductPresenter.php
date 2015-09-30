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

	/** @var \App\Model\CategoryManager @inject */
	public $categoryManager;

	/** @var \App\Model\BrandManager @inject */
	public $brandManager;

	/** @var \App\Model\SupplierManager @inject */
	public $supplierManager;

	/** @var App\Model\LanguageManager */
    public $languages;

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

		$categoriesArray = $this->categoryManager->getAllCategoriesAsArray();
		$brandsArray = self::createBrandsArrayForSelect();
		$suppliersArray = self::createSuppliersArrayForSelect();

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
			 ->addRule(Form::FLOAT, "Cena musí byť číslo")
			 ->getControlPrototype()->class("form-control");

		$form->addText("price_buy", "Cena")
			 ->setType('number')
			 ->addRule(Form::FLOAT, "Cena musí byť číslo")
			 ->getControlPrototype()->class("form-control");

		$form->addMultiSelect("category", "Kategórie", $categoriesArray)
			 ->setRequired('Značka je povinná')
			 ->getControlPrototype()->class("form-control");

		$form->addSelect("brand", "Značka", $brandsArray)
			 ->setRequired('Značka je povinná')
			 ->getControlPrototype()->class("form-control");

		$form->addSelect("supplier", "Dodávateľ", $suppliersArray)
			 ->setRequired('Dodávateľ je povinný')
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

				$this->flashMessage('Produkt úspešne pridaný');
			}
			else
			{
				//EDIT PRODUCT
				$this->products->edit($productId, $values);

				$this->flashMessage('Produkt bol aktualizovaný');
			}

				$this->redirect("Product:");
				
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Názov produktu už existuje');
		}
	}

	private function createBrandsArrayForSelect()
	{
		$brands = $this->brandManager->getAll();

		$brandsArray = array();

		$brandsArray[''] = "--";

		foreach ( $brands as $brand )
		{
			$brandsArray[$brand->id] = $brand->name;
		}

		return $brandsArray;
	}

	private function createSuppliersArrayForSelect()
	{
		$suppliers = $this->supplierManager->getAll();

		$suppliersArray = array();

		$suppliersArray[''] = "--";

		foreach ( $suppliers as $supplier )
		{
			$suppliersArray[$supplier->id] = $supplier->name;
		}

		return $suppliersArray;
	}

	public function getProductLang($productId)
    {
        return $this->products->model->getFirstSecond($productId, parent::getLanguage()->id, 'product', 'lang');
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
		$productLang = self::getProductLang($productId);
		$productCategory = $this->products->model->getAllFirstSecond($productId, 'product', 'category')->fetch();
		//Now is there only one row, status is not mentioned
		$productSupplier = $this->products->model->getAllFirstSecond($productId, 'product', 'supplier')->fetch();

		$this->template->productId = $productId;

		$this['productForm']->setDefaults($product->toArray());
		$this['productForm']['price_buy']->setDefaultValue($productSupplier->price_buy);
		$this['productForm']['name']->setDefaultValue($productLang->name);
		$this['productForm']['short_desc']->setDefaultValue($productLang->short_desc);
		$this['productForm']['desc']->setDefaultValue($productLang->desc);
		$this['productForm']['category']->setDefaultValue($productCategory->category_id);
		$this['productForm']['brand']->setDefaultValue($product->brand);
		$this['productForm']['supplier']->setDefaultValue($productSupplier->supplier_id);

	}
}