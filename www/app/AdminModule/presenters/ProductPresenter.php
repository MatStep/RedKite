<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Utils\Image,
	Nette\Forms\Container,
	Nette\Forms\Controls\SubmitButton,
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

	/** @var \App\Model\FeatureManager @inject */
	public $featureManager;

	/** @var \App\Model\AppModel @inject */
	public $model;

	/** @var App\Model\LanguageManager */
    public $languages;

	private $products;

	private $values;

	private $id;

	const
		PRODUCT_IMG_FOLDER = '{$basePath}/images/products/';
	
	public function __construct(Model\ProductManager $products)
	{
		$this->products = $products;
		$this->values = array("status" => "", "order" => "", "price_sell" => "");
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->products = $this->products->getAll();
		$this->template->products_num = $this->products->getNumberOfProducts();
	}

	public function renderAdd()
	{
		$this->template->form = $this->template->_form = $this["productForm"];
		$this->template->products = $this->products->getAll();
	}

	public function renderEdit($productId)
	{
		$this->template->images = $this->products->getProductImages($productId);
	}

	public function renderImageReorder($productId) 
	{
		$productImages = $this->products->getProductImages($productId);

		$this->template->productId = $productId;
		$this->template->images = $productImages;
	}

	/*Product form*/
	public function createComponentProductForm()
	{
		$form = new Form;

		$form->getElementPrototype()->class('ajax');

		$categoriesArray = $this->categoryManager->getAllCategoriesAsArray();
		$brandsArray = self::createBrandsArrayForSelect();
		$suppliersArray = self::createSuppliersArrayForSelect();
		$featuresArray = self::createFeaturesArrayForSelect();
		$featureValuesArray = self::createFeatureValuesArrayForSelect();

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
			 ->setRequired('Kategória je povinná')
			 ->setAttribute('data-placeholder', 'Vyberte kategóriu')
			 ->getControlPrototype()->class("form-control categorySelect");

		$form->addSelect("brand", "Značka", $brandsArray)
			 ->setRequired('Značka je povinná')
			 ->getControlPrototype()->class("form-control");

		/*$form->addSelect("supplier", "Dodávateľ", $suppliersArray)
			 ->setRequired('Dodávateľ je povinný')
			 ->getControlPrototype()->class("form-control");*/

		$form->addMultiSelect("supplier", "Dodávatelia", $suppliersArray)
			 ->setRequired('Dodávateľ je povinná')
			 ->setAttribute('data-placeholder', 'Vyberte dodávateľa')
			 ->getControlPrototype()->class("form-control supplierSelect");

		$form->addMultiSelect("feature", "Vlastnosti", $featuresArray)
			 ->setAttribute('data-placeholder', 'Vyberte vlastnosť')
			 ->getControlPrototype()->class("form-control featureSelect ajax");

		$form->addMultiSelect("featureValue", "Hodnoty", $featureValuesArray)
			 ->setAttribute('data-placeholder', 'Vyberte hodnoty')
			 ->getControlPrototype()->class("form-control featureValuesSelect ajax");

		$removeEvent = array($this, "removeElemenetClicked");

		$featureValues = $form->addDynamic("featureValues", function (Container $container) use ($removeEvent) {
			$container->addText("featureVal", "Hodnota");

			$container->addSubmit("remove_fv", "Vymazať")
				->setValidationScope(FALSE)
				->onClick[] = $removeEvent;
		});

		$featureValues->addSubmit("add_fv", "Pridať novú hodnotu")
			->setValidationScope(FALSE);
			// ->onClick[] = array($this, "addElementClicked");

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

	public function handleAddElementClicked()
	{
		$form = $this->getComponent("productForm");
		$form["featureValues"]->createOne();

		if($this->isAjax())
		{
			$this->redrawControl("fv");
		}
		else
		{
			$this->redirect("this");
		}
	}

	public function handleShowSelect($featureId)
	{
		$featureValues = self::createFeatureValuesArrayForSelect2($featureId);
		$form = $this->getComponent("productForm");
		$form["featureValue"]->setItems($featureValues);
		
		if($this->isAjax())
		{
			$this->redrawControl("featureValue");
		}
		else
		{
			$this->redirect("this");
		}
	}

	public function createComponentProductImageForm()
	{
		$form = new Form;

		$form->addUpload('image','Obrázok')
			 ->setRequired('Nahranie obrázku je povinné')
			 ->addCondition(Form::FILLED)
			 ->addRule(Form::IMAGE, 'Nepodporovaný formát obrázku');

		foreach(parent::getAllLanguages() as $lang)
        {
            if($lang->id == parent::getLanguage()->id)
            {
				$form->addText("name", "Názov" . "(" . $lang->iso_code . ")")
		                     ->getControlPrototype()->class("form-control");
            }
        }
		$form->addSubmit("add", "Pridať obrázok")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "productImageFormSucceeded");

		return $form;
	}

	public function productImageFormSucceeded($form, $values) 
	{
		$productId = $this->request->getParameters()['productId'];

		$images = $this->products->getProductImages($productId);
		
		if ( count($images) == 0 ) 
		{
			$values->order = 1;
		}
		else 
		{
			$values->order = count($images) + 1;
		}

		$this->products->addProductImage($productId, $values);
		$this->redirect('Product:edit', $productId, true);
	}

	public function createComponentImageReorderForm() 
	{
		$form = new Form;

		$form->addSubmit('send', 'Uložiť')
			 ->getControlPrototype()->class('btn btn-primary form-control');
		
		$form->addText('reorderedString', '')
			 ->getControlPrototype()->class('reorder');

		$form->onSuccess[] = array($this, 'imageReorderFormSucceded');

		return $form;
	}

	public function imageReorderFormSucceded($form, $values) 
	{
		$productId = $this->request->getParameters()['productId'];

		$imageOrderString = 
			$this->getHttpRequest()->getPost('reorderedString');

		$imageOrderString = self::parseCheckedBoxes($imageOrderString);

		$this->model->orderItems('product_image', $productId, $imageOrderString);

		$this->flashMessage('Obrázky boli preusporiadané');
		$this->redirect('Product:imageReorder', $productId);
	}

	public function parseCheckedBoxes($checkedBoxes) 
	{
		$productsIds = '';

		preg_match_all('!\d+!', $checkedBoxes, $productsIds);
		
		$ids = array();
		
		foreach($productsIds[0] as $id)
		{
			array_push($ids, (int)$id);
		}
		
		return $ids;
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

	private function createFeaturesArrayForSelect()
	{
		$features = $this->featureManager->getAll();

		$featureArray = array();

		$featuresArray[''] = "--";

		foreach ( $features as $feature )
		{
			$featuresArray[$feature->id] = $this->products->model->getFirstSecond($feature->id, parent::getLanguage()->id, 'feature', 'lang')->name;
		}

		return $featuresArray;
	}

	private function createFeatureValuesArrayForSelect()
	{
		$featureValues = $this->featureManager->getAllFeatureValue();

		$featureValuesArray = array();

		$featuresValuesArray[''] = "--";

		foreach ( $featureValues as $featureValue )
		{
			$featureValuesArray[$featureValue->id] = $this->products->model->getFirstSecond($featureValue->id, parent::getLanguage()->id, 'feature_value', 'lang')->value;
		}

		return $featureValuesArray;
	}

	private function createFeatureValuesArrayForSelect2($featureId)
	{
		$featureValues = $this->featureManager->getFeatureValues($featureId);

		$featureValuesArray = array();

		$featuresValuesArray[''] = "--";

		foreach ( $featureValues as $featureValue )
		{
			$featureValuesArray[$featureValue->id] = $this->products->model->getFirstSecond($featureValue->id, parent::getLanguage()->id, 'feature_value', 'lang')->value;
		}

		return $featureValuesArray;
	}

	public function getLink($productId)
	{
		$images = $this->products->getProductImages($productId);

		if($images->count() == 0)
		{
			return 'images/products/default_image.png';
		}
		else
		{
			$imgPath = $images->where('order = 1')->fetch()->path;
			return $imgPath;
		}
	}

	public function getProductLang($productId)
    {
        return $this->products->model->getFirstSecond($productId, parent::getLanguage()->id, 'product', 'lang');
    }

    public function actionImageRemove($productId, $imageId) 
	{
		$this->products->removeProductImage($productId, $imageId);
		$this->flashMessage('Obrázok úspešne vymazaný');
		$this->redirect('Product:edit', $productId, true);
	}

	public function actionRemove($productId)
	{
		$this->products->remove($productId);
		$this->flashMessage('Produkt bol úspešne vymazaný');
		$this->redirect("Product:");
	}

	public function actionEdit($productId, $goToImageTab = false)
	{
		$product = $this->products->getProduct($productId);
		$productLang = self::getProductLang($productId);
		$productCategory = $this->products->model->getAllFirstSecond($productId, 'product', 'category');
		$productFeature = $this->products->model->getAllFirstSecond($productId, 'product', 'feature');
		$productImages = $this->products->getProductImages($productId);
		$cArray = array();
		$fArray = array();
		foreach($productCategory as $productCat)
		{
			array_push($cArray, $productCat->category_id);
		}
		foreach($productFeature as $productF)
		{
			array_push($fArray, $productF->feature_id);
		}

		$productSupplier = $this->products->model->getAllFirstSecond($productId, 'product', 'supplier')->fetch();
		$sArray = array();
		$productSupplierArray = $this->products->model->getAllFirstSecond($productId, 'product', 'supplier');
		foreach($productSupplierArray as $productSup)
		{
			array_push($sArray, $productSup->supplier_id);
		}
		//Now is there only one row, status is not mentioned
		

		$this->template->goToImageTab = $goToImageTab;
		$this->template->productId = $productId;

		$this['productForm']->setDefaults($product->toArray());
		$this['productForm']['price_buy']->setDefaultValue($productSupplier->price_buy);
		$this['productForm']['name']->setDefaultValue($productLang->name);
		$this['productForm']['short_desc']->setDefaultValue($productLang->short_desc);
		$this['productForm']['desc']->setDefaultValue($productLang->desc);
		$this['productForm']['category']->setDefaultValue($cArray);
		$this['productForm']['feature']->setDefaultValue($fArray);
		$this['productForm']['supplier']->setDefaultValue($sArray);
		$this['productForm']['brand']->setDefaultValue($product->brand);

		// foreach($productImages as $productImage)
		// {
		// 	$this['productImageForm']['name']->setDefaultValue($productImage->name);
		// }
	}
}