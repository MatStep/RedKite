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
		COLUMN_DESC = 'desc';

	/** @var Nette\Database\Context */
	private $database;

	/** @var \App\Model\AppModel @inject */
	public $model;

	/** @var \App\Model\LanguageManager @inject */
	public $languages;

	public function __construct(Nette\Database\Context $database, LanguageManager $languages, AppModel $model)
	{
		$this->database   = $database;
		$this->languages  = $languages;
		$this->model	  = $model;
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

	public function insert($values)
	{
		$data = array();
		$data["status"] = $values->status;
		$data["price_sell"] = $values->price_sell;
		$data["brand_id"] = $values->brand;

		$product = $this->database->table(self::PRODUCT_TABLE)->insert($data);

		//insert foreign tables


		$this->database->table(self::PRODUCT_SUPPLIER_TABLE)
			 ->insert(array(
			 	'product_id' => $product->id,
			 	'supplier_id' => $values->supplier,
			 	'price_buy'	=> $values->price_buy,
			 	'status' => 1,
			 	));


		//ADD LANGUAGE DATA
        foreach($this->languages->getAllActive() as $lang) {
            self::translateData($lang->id, $product->id, $values, 0);
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
			));

		//update foreign tables

		$this->database->table(self::PRODUCT_SUPPLIER_TABLE)
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

		// delete all rows where is product located in product supplier

		$allProductSupplier = $this->model->getAllFirstSecond($id,'product','supplier', 0);

		while($allProductSupplier->count() > 0) {
			foreach($allProductSupplier as $productSupplier)
			{
				$productSupplier->delete();
			}
		}
		
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
}