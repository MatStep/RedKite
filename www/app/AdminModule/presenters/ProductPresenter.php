<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Utils\Image,
	Nette\Forms\Container,
	Nette\Forms\Controls\SubmitButton,
	Nette\Utils\Strings,
	Nette\Application\UI\Form as Form,
	Tracy\Debugger;

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
		$this->template->features = $this->products->getFeatures($productId);
	}

	public function renderAddValue($productId, $featureId)
	{
		$this->template->productId = $productId;
		$this->template->feature = $this->products->getFeature($productId, $featureId);
	}

	public function renderImageReorder($productId) 
	{
		$productImages = $this->products->getProductImages($productId);

		$this->template->productId = $productId;
		$this->template->images = $productImages;
	}

	/*
	 * Test form
	 */
	public function createComponentTestForm()
	{
		$form = new Form;

		$removeEvent = array($this, 'MyFormRemoveElementClicked');

		$users = $form->addDynamic('users', function (Container $user) use ($removeEvent) {
        $user->addText('name', 'Name');

            $user->addSubmit('remove', 'Remove')
            ->setValidationScope(FALSE) # disables validation
            ->onClick[] = $removeEvent;      
		}, 1);

		$users->addSubmit('add', 'Add next person')
        ->setValidationScope(FALSE)
        ->onClick[] = array($this, 'MyFormAddElementClicked');

        $form->addSubmit("add", "Pridať produkt")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

        return $form;
	}

	public function MyFormAddElementClicked(SubmitButton $button)
	{
	    $button->parent->createOne();
	}

	public function MyFormRemoveElementClicked(SubmitButton $button)
	{
	    // first parent is container
	    // second parent is it's replicator
	    $users = $button->parent->parent;
	    $users->remove($button->parent, TRUE);
	}

	/*
	 * Product form
	 */
	protected function createComponentProductForm()
	{
		$form = new Form;

		// $form->getElementPrototype()->class('ajax');

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

        $form->addText("code", "Kód produktu")
             ->setAttribute('placeholder', 'Napríklad: JN782')
        	 ->getControlPrototype()->class("form-control");

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

		// $form->addMultiSelect("featureValue", "Hodnoty", $featureValuesArray)
		// 	 ->setAttribute('data-placeholder', 'Vyberte hodnoty')
		// 	 ->getControlPrototype()->class("form-control featureValuesSelect ajax");

		// Feature values adding dynamically with Replicator
		// Need to fix

		// $removeEvent = array($this, "removeElementClicked");

		// $presenter = $this;
		// $invalidateCallback = function () use ($presenter) {
  //       	/** @var \Nette\Application\UI\Presenter $presenter */
  //       	$presenter->invalidateControl('fv');
  //   	};

		// $featureValues = $form->addDynamic("featureValues", function (Container $container) use ($invalidateCallback, $featureValuesArray) {
		// 	$container->addMultiSelect("value", "Hodnoty", $featureValuesArray)
		// 	 ->setAttribute('data-placeholder', 'Vyberte hodnoty')
		// 	 ->getControlPrototype()->class("form-control featureValuesSelect");

		// 	$container->addSubmit("remove", "Vymazať")
		// 		->setAttribute("class", "remove_button btn ajax")
		// 		->setValidationScope(FALSE)
		// 		->addRemoveOnClick($invalidateCallback);
		// 		// ->onClick[] = $removeEvent;
		// });

		// $featureValues->addSubmit("add", "Pridať novú hodnotu")
		// 	->setAttribute("class", "add_button btn ajax")
		// 	->setValidationScope(FALSE)
		// 	->addCreateOnClick($invalidateCallback);
		// 	// ->onClick[] = array($this, "addElementClicked");

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

	public function addElementClicked(SubmitButton $button)
	{
		$button->parent->createOne();
		$this->redrawControl("fv");
	}

	public function removeElementClicked(SubmitButton $button)
	{
	    // first parent is container
	    // second parent is it's replicator
	    $featureValues = $button->parent->parent;
	    $featureValues->remove($button->parent, TRUE);
	    $this->redrawControl("fv");
	}

	public function handleAddElementClicked($featureId)
	{	
		$form = $this->getComponent("productForm");
		$featureValues = self::createFeatureValuesArrayForSelect2($featureId);
		$form["featureValues"]->createOne();
		$form["featureValues-0-value"]->setItems($featureValues);

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

	/*
	 * ProductImage form
	 */
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

	/*
	 * Product import form
	 */
	public function createComponentProductImportForm()
	{
		$form = new Form;

		$form->addUpload('csv','Súbor')
			 ->setRequired('Nahranie súboru je povinné')
			 ->addCondition(Form::FILLED);
		
		$form->addSubmit("add", "Pridať csv import")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "productImportFormSucceeded");

		return $form;
	}

	/*
	* CSV collumns must be comma separated(','). 
	*/
	public function productImportFormSucceeded($form, $values) 
	{
		$this->importArrayData($values);
		$csvFile = $this->readCSV($values->csv);

		// csvFile is now 2 Dimensional Array, csvFile[rows][columns]
		$this->flashMessage($csvFile[1][1]); //example
		$this->flashMessage(json_encode($csvFile));
	}

	public function readCSV($csvFile){
		$line_of_text = array();
		ini_set('auto_detect_line_endings', true);
		$file_handle = fopen($csvFile, 'rb');
		set_time_limit(0); // neccesery if csv is large 
		while (!feof($file_handle) ) {
			$line_of_text[] = fgetcsv($file_handle,',');
		}
		fclose($file_handle);
		return $line_of_text;
	}

	/*
	 * Function for data import from array to DB
	 * Values is array with csv data
	 */
	public function importArrayData($values)
	{
		$import = $this->readCSV($values->csv);
		Debugger::barDump($import);

		// One product test
		$data = new Nette\Utils\ArrayHash();
		$data->code = $import[1][0];
		$data->price_sell = $import[1][1];
		$data->status = 1;
		$data->brand = NULL;

		Debugger::barDump($data);


		$this->products->insert($data);
	}

	/*
	 * Image reorder form
	 */
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

	public function imageReorderFormSucceeded($form, $values) 
	{
		$productId = $this->request->getParameters()['productId'];

		$imageOrderString = 
			$this->getHttpRequest()->getPost('reorderedString');

		$imageOrderString = self::parseCheckedBoxes($imageOrderString);

		$this->model->orderItems('product_image', $productId, $imageOrderString);

		$this->flashMessage('Obrázky boli preusporiadané');
		$this->redirect('Product:imageReorder', $productId);
	}

	/*
	 * Feature form
	 */
	public function createComponentFeatureForm()
	{
		$form = new Form;

		$form->getElementPrototype()->class('ajax');

		$featuresArray = self::createFeaturesArrayForSelect();

		$form->addMultiSelect("feature", "Vlastnosti", $featuresArray)
			 ->setAttribute('data-placeholder', 'Vyberte vlastnosť')
			 ->getControlPrototype()->class("form-control featureSelect form-control-80 ajax");

		$form->addSubmit("add", "Upraviť vlastnosti")
			 ->getControlPrototype()->class("btn btn-primary");

		$form->onSuccess[] = array($this, "featureFormSucceeded");

		return $form;
	}

	public function featureFormSucceeded($form, $values)
	{
		$productId = $this->request->getParameters()['productId'];

		//ADD FEATURE
		$this->products->addProductFeature($productId, $values);

		$this->flashMessage('Vlastnosť úspešne pridaná');

		$this->redirect("Product:edit", $productId);

		// if(!$this->isAjax())
		// {
		// 	$this->redirect("Product:edit", $productId);
		// }
		// else {
		// 	$this->redrawControl('featureBox');
		// 	$this->redrawControl('featureAdd');
		// 	$form->setValues(array(), TRUE);
		// }
	}

	/*
	 * Feature value form
	 */

	public function createComponentFeatureValueForm()
	{
		$form = new Form;

		$form->getElementPrototype()->class('ajax');

		$featureId = $this->request->getParameters()['featureId'];

		$featureValuesArray = self::createFeatureValuesArrayForSelect2($featureId);

		$form->addSelect("feature_value", "Hodnota", $featureValuesArray)
			 ->setAttribute('placeholder', 'Pridať hodnotu')
			 ->getControlPrototype()->class("form-control");

        $form->addHidden('id');

		$form->addSubmit("add", "Pridať hodnotu")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "featureValueFormSucceeded");

		return $form;
	}

	public function featureValueFormSucceeded($form, $values)
	{
		$productId = $this->request->getParameters()['productId'];
		$featureId = $this->request->getParameters()['featureId'];
		$productFeatureId = $values->id;

		//ADD FEATURE VALUE
		$this->products->addProductFeatureValue($productFeatureId, $values);

		$this->flashMessage('Hodnota úspešne pridaná');

		$this->redirect("Product:addValue", $productId, $featureId);

		// if(!$this->isAjax())
		// {
		// 	$this->redirect("Product:edit", $productId);
		// }
		// else {
		// 	$this->redrawControl('featureBox');
		// 	$this->invalidateControl('list');
		// 	$this->invalidateControl('form');
		// 	$form->setValues(array(), TRUE);
		// }
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

    public function getFeatureLang($featureId)
    {
        return $this->products->model->getFirstSecond($featureId, parent::getLanguage()->id, 'feature', 'lang');
    }

    public function getFeatureValueLang($featureValueId)
    {
        return $this->products->model->getFirstSecond($featureValueId, parent::getLanguage()->id, 'feature_value', 'lang');
    }

    public function getFeatureValues($featureId)
    {
        return $this->products->model->getAllFirstSecond($featureId, 'product_feature', 'value');
    }

    public function actionImageRemove($productId, $imageId) 
	{
		$this->products->removeProductImage($productId, $imageId);
		$this->flashMessage('Obrázok úspešne vymazaný');
		$this->redirect('Product:edit', $productId, true);
	}

	public function actionRemoveProductFeatureValue($productId, $productFeatureValueId)
	{
			$this->products->removeProductFeatureValue($productFeatureValueId);
			$this->flashMessage('Vlastnosť bola úspešne vymazaná');
			$this->redirect('Product:edit', $productId);
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
		$this['featureForm']['feature']->setDefaultValue($fArray);

		// foreach($productImages as $productImage)
		// {
		// 	$this['productImageForm']['name']->setDefaultValue($productImage->name);
		// }
	}
}