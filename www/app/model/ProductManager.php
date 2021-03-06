<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * Product management.
 */
class ProductManager extends Nette\Object
{
	const 
		PRODUCT_TABLE = 'product',
		COLUMN_ID = 'id',
		COLUMN_STATUS = 'status',
		COLUMN_ORDER = 'order',
		COLUMN_ADD_DATE = 'add_date',
		COLUMN_PRICE_SELL = 'price_sell',

		//BRAND
		BRAND_TABLE = 'brand',
		COLUMN_BRAND_ID = 'brand_id',

		//SUPPLIER
		SUPPLIER_TABLE = 'supplier',
		COLUMN_SUPPLIER_ID = 'id',
		COLUMN_SUPPLIER_NAME = 'name',

		//PRODUCT_SUPPLIER
		PRODUCT_SUPPLIER_TABLE = 'product_supplier',
		COLUMN_PRODUCT_SUPPLIER_ID = 'id',
		COLUMN_FK_S_PRODUCT_ID = 'product_id',
		COLUMN_FK_SUPPLIER_ID = 'supplier_id',
		COLUMN_PRICE_BUY = 'price_buy',
		COLUMN_P_S_STATUS = 'status',

		//LANG
		LANG_TABLE = 'lang',
		COLUMN_LANG_ID = 'id',

		//PRODUCT_LANG
		PRODUCT_LANG_TABLE = "product_lang",
		COLUMN_PRODUCT_LANG_ID = "id",
		COLUMN_PRODUCT_ID = "product_id",
		COLUMN_FK_LANG_ID = "lang_id",
		COLUMN_NAME = 'name',
		COLUMN_SHORT_DESC = 'short_desc',
		COLUMN_DESC = 'desc',
		COLUMN_CODE = 'code';

	/** @var Nette\Database\Context */
	private $database;

	/** @var \App\Model\AppModel @inject */
	public $model;

	/** @var \App\Model\ImageManager */
	public $imageManager;

	/** @var \App\Model\LanguageManager @inject */
	public $languages;

	public function __construct(Nette\Database\Context $database, LanguageManager $languages, AppModel $model, \App\Model\ImageManager $imageManager)
	{
		$this->database   = $database;
		$this->languages  = $languages;
		$this->model	  = $model;
		$this->imageManager = $imageManager;
	}

	public function getAll()
	{
		return $this->database->table('product');
	}

	/*Get product*/
	public function getProduct($productId)
	{
		$product =  $this->database->table(self::PRODUCT_TABLE)->where(self::COLUMN_ID, $productId)->fetch();

		if ( !$product )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $product;
	}

	public function getProductImages($productId) 
	{
		return $this->database->table('product_image')
			->where('product_id = ?', $productId)->order('order');
	}

	public function getFeatures($productId)
	{
		return $this->database->table('product_feature')
			->where('product_id = ?', $productId);
	}

	public function getFeature($productId, $featureId)
	{
		return $this->database->table('product_feature')
			->where('product_id = ?', $productId)->where('feature_id', $featureId)->fetch();
	}

	public function getNumberOfProducts()
	{
		return $this->database->table('product')->count('*');
	}

	public function insert($values)
	{
		$data = array();
		$data["status"] = $values->status;
		$data["price_sell"] = $values->price_sell;

		// check if columns for brand exists
		if(!$values->offsetExists('brand_id')) {
			$values->brand = NULL;
		}
		else {
			$data["brand_id"] = $values->brand;
		}
		$data["code"] = $values->code;

		$product = $this->database->table(self::PRODUCT_TABLE)->insert($data);

		//insert foreign tables

		// Table product_category
		if($values->offsetExists('category')) {
			foreach($values->category as $category) {
				$this->database->table('product_category')
					 ->insert(array(
					 	'product_id' => $product->id,
					 	'category_id' => $category,
					 	));
			}
		}

		// Table product_supplier
		if($values->offsetExists('supplier')) {
			$this->database->table(self::PRODUCT_SUPPLIER_TABLE)
				 ->insert(array(
				 	'product_id' => $product->id,
				 	'supplier_id' => $values->supplier,
				 	'price_buy'	=> $values->price_buy,
				 	'status' => 1,
				 	));
		}

		// Table product_feature
		if($values->offsetExists('feature')) {
			foreach($values->feature as $feature) {
				$this->database->table('product_feature')
					 ->insert(array(
					 	'product_id' => $product->id,
					 	'feature_id' => $feature,
					 	));
			}
		}

		// check if columns for translate exists
		if(!$values->offsetExists('short_desc')) {
			$values->short_desc = NULL;
		}

		if(!$values->offsetExists('desc')) {
			$values->desc = NULL;
		}

		//ADD LANGUAGE DATA
		//name is required for product so only if is given
		if($values->offsetExists('name')) {
	        foreach($this->languages->getAllActive() as $lang) {
	            self::translateData($lang->id, $product->id, $values, 0);
	        }
    	}

		return $product;
	}

	public function edit($id, $values)
	{
		$product = $this->database->table(self::PRODUCT_TABLE)->where(self::COLUMN_ID, $id);

		$currentLanguage = $this->languages->getLanguageByName($this->languages->getLanguage());

		if (!$product)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$product->update(array(
			self::COLUMN_STATUS => $values->status,
			self::COLUMN_PRICE_SELL => $values->price_sell,
			self::COLUMN_BRAND_ID => $values->brand,
			self::COLUMN_CODE => $values->code,
			));

		//update foreign tables
		
		$allProductCategory = $this->model->getAllFirstSecond($id,'product','category', 0);

		while($allProductCategory->count() > 0) {
			foreach($allProductCategory as $productCategory)
			{
				$productCategory->delete();
			}
		}

		foreach($values->category as $category) {

			$this->database->table('product_category')
			 ->insert(array(
			 	'product_id' => $id,
			 	'category_id' => $category,
			 	));
		}

		$allProductFeature = $this->model->getAllFirstSecond($id,'product','feature', 0);

		while($allProductFeature->count() > 0) {
			foreach($allProductFeature as $productFeature)
			{
				$productFeature->delete();
			}
		}

		// Table product_feature
		foreach($values->feature as $feature) {
			$this->database->table('product_feature')
				 ->insert(array(
				 	'product_id' => $id,
				 	'feature_id' => $feature,
				 	));
		}

		$this->database->table(self::PRODUCT_SUPPLIER_TABLE)->where('product_id', $id)
			 ->update(array(
			 	'product_id' => $id,
			 	'supplier_id' => $values->supplier,
			 	'price_buy'	=> $values->price_buy,
			 	'status' => 1,
			 	));

		//EDIT LANGUAGE DATA
        self::translateData($currentLanguage, $id, $values, 1);

        return $product;
	}

	public function remove($id)
	{
		// delete all rows where is product located in product lang

		$allProductLang = $this->model->getAllFirstSecond($id,'product','lang');

		while($allProductLang->count() > 0) {
			foreach($allProductLang as $productLang)
			{
				$productLang->delete();
			}
		}

		// delete all rows where is product located in product category

		$allProductCategory = $this->model->getAllFirstSecond($id,'product','category', 0);

		while($allProductCategory->count() > 0) {
			foreach($allProductCategory as $productCategory)
			{
				$productCategory->delete();
			}
		}

		// delete all rows where is product located in product supplier

		$allProductSupplier = $this->model->getAllFirstSecond($id,'product','supplier', 0);

		while($allProductSupplier->count() > 0) {
			foreach($allProductSupplier as $productSupplier)
			{
				$productSupplier->delete();
			}
		}

		// delete all rows where is product located in product feature

		$allProductFeature = $this->model->getAllFirstSecond($id,'product','feature', 0);

		while($allProductFeature->count() > 0) {
			foreach($allProductFeature as $productFeature)
			{
				$productFeature->delete();
			}
		}

		self::removeProductImages($id);
		
		return $this->database->table(self::PRODUCT_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}


	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $productId    id of product
	 */
	public function translateData($langId, $productId, $data, $method)
	{

		if($method == '0')
		{
			$this->database->table(self::PRODUCT_LANG_TABLE)->insert(array(
				self::COLUMN_PRODUCT_ID => $productId,
				self::COLUMN_FK_LANG_ID => $langId,
				self::COLUMN_NAME => $data['name'],
				self::COLUMN_SHORT_DESC => $data['short_desc'],
				self::COLUMN_DESC => $data['desc'],
				));
		}
		else
		{
			$product_lang = $this->model->getFirstSecond($productId, $langId, 'product', 'lang');
			$product_lang->update(array(
				self::COLUMN_NAME => $data['name'],
				self::COLUMN_SHORT_DESC => $data['short_desc'],
				self::COLUMN_DESC => $data['desc'],
				));
		}
	}

	public function addProductImage($productId, $values) 
	{	

		$file = $values->image;

		$fileName = $this->imageManager->getName($file->name);

		if($values->name != NULL)
		{
			$image = $this->database->table('product_image')
        	->insert(array(
        		'product_id' => $productId,
        		'order' => $values->order
        	));
        	//ADD LANGUAGE DATA
        	foreach($this->languages->getAllActive() as $lang) {
            	self::translateDataImage($lang->id, $image->id, $values->name, 0);
        	}
		}
		else
		{
			$image = $this->database->table('product_image')
	        	->insert(array(
	        		'product_id' => $productId,
	        		'order' => $values->order
	        	));
	        //ADD LANGUAGE DATA
        	foreach($this->languages->getAllActive() as $lang) {
            	self::translateDataImage($lang->id, $image->id, $fileName, 0);
        	}
        }

        $imgUrl = $this->imageManager->getImage($file, "products");

        $this->database->table('product_image')
        	 ->where('product_id = ? AND id = ?', $productId, $image->id)
        	 ->update(array('path' => $imgUrl));

	}

	public function addProductFeature($id, $values)
	{
		$allProductFeature = $this->model->getAllFirstSecond($id,'product','feature', 0);

		while($allProductFeature->count() > 0) {
			foreach($allProductFeature as $productFeature)
			{
				$productFeature->delete();
			}
		}

		// Table product_feature
		foreach($values->feature as $feature) {
			$this->database->table('product_feature')
				 ->insert(array(
				 	'product_id' => $id,
				 	'feature_id' => $feature,
				 	));
		}
	}

	public function addProductFeatureValue($id, $values)
	{
		$productFeatureValue = $this->database->table('product_feature_value')
			->where('product_feature_id = ?', $id)->where('feature_value_id', $values->feature_value);

		if($productFeatureValue->count() == 0)
		{
		// Table product_feature_value
			$this->database->table('product_feature_value')
				 ->insert(array(
				 	'product_feature_id' => $id,
				 	'feature_value_id' => $values->feature_value,
				 	));
		}
	}

	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $productImageId    id of productImage
	 */
	public function translateDataImage($langId, $productImageId, $data, $method)
	{

		if($method == '0')
		{
			$this->database->table('product_image_lang')->insert(array(
				'product_image_id' => $productImageId,
				'lang_id' => $langId,
				'name' => $data,
				));
		}
		else
		{
			$product_lang = $this->model->getFirstSecond($productImageId, $langId, 'product_image', 'lang');
			$product_lang->update(array(
				'name' => $data,
				));
		}
	}

	function removeProductImages($productId)
	{
		//gets all product images
		$productImages = $this->database->table('product_image')
			->where('product_id = ?', $productId);

		
		//deletes each of them one by one also from the server and DB
		foreach ($productImages as $productImage)
		{
			$allProductImageLang = $this->model->getAllFirstSecond($productImage->id,'product_image','lang');

			while($allProductImageLang->count() > 0) {
				foreach($allProductImageLang as $productImageLang)
				{
					$productImageLang->delete();
				}
			}

			//delete from server
			unlink($productImage->path);

			//delete from DB
			$productImage->delete();
		}
	}

	public function removeProductImage($productId, $imageId) 
	{
		//get the image to be deleted
		$image = $this->database->table('product_image')
					  ->where('id = ?', $imageId)->fetch();

		$allProductImageLang = $this->model->getAllFirstSecond($imageId,'product_image','lang');

			while($allProductImageLang->count() > 0) {
				foreach($allProductImageLang as $productImageLang)
				{
					$productImageLang->delete();
				}
			}
		//delete the image
		$this->database->table('product_image')
			 ->where('id = ?', $imageId)->delete();
		//get all product images
		$productImages = self::getProductImages($productId);
		/*
			if the deleted image order was 1 then reorder
			the remaining images
		 */
		if ( $image->order == "1" )
		{
			$reorderArray = array();
			foreach ($productImages as $productImage)
			{
				array_push($reorderArray, $productImage->id);
			}
			$this->model->orderItems('product_image', $productId, $reorderArray);
		}

			unlink($image->path);
	}

	public function removeProductFeatureValue($id)
	{
		return $this->database->table('product_feature_value')->where('id', $id)->delete();
	}

	public function changeProductCategory($productId, $newCategoryId) 
	{
		$this->database->table('product_category')
			->where('product_id = ?', $productId)
			->update(array(
				'category_id' => $newCategoryId
			));
	}

	public function changeProductStatus($productId, $status) 
	{
		
		$product = self::getProduct($productId);

		if ( $product->status != $status )
		{
			$values = array('status' => $status);

			$this->database->table(self::PRODUCT_TABLE)
				->get($productId)->update($values);

			return TRUE;
		}

		return TRUE;
	}

	//Change only one category to products
	public function multipleProductsCategoryChange($productsIds, $categoryId) 
	{
		foreach ($productsIds as $productId) 
		{		
			self::changeProductCategory($productId, $categoryId);
		}
	}

	public function multipleProductsStatusChange($productsIds, $status) 
	{

		$i = 0;
		foreach($productsIds as $productId) 
		{
			if ( !self::changeProductStatus($productId, $status) )
			{
				return $i;
			}
			$i++;
		}

		return $i;
	}

	public function multipleProductsRemove($productsIds) 
	{
		foreach($productsIds as $productId)
		{
			self::remove($productId);
		}
	}

}