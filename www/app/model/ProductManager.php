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
		COLUMN_NAME = 'name',
		COLUMN_SHORT_DESC = 'short_desc',
		COLUMN_DESC = 'desc',
		COLUMN_STATUS = 'status',
		COLUMN_ORDER = 'order',
		COLUMN_ADD_DATE = 'add_date',
		COLUMN_PRICE_SELL = 'price_sell',

		BRAND_TABLE = 'brand',
		COLUMN_BRAND_ID = 'brand_id',

		LANG_TABLE = 'lang',
		COLUMN_LANG_ID = 'id',

		PRODUCT_LANG_TABLE = "product_lang",
		COLUMN_PRODUCT_LANG_ID = "id",
		COLUMN_PRODUCT_ID = "product_id",
		COLUMN_FK_LANG_ID = "lang_id",
		COLUMN_TNAME = 'name',
		COLUMN_TSHORT_DESC = 'short_desc',
		COLUMN_TDESC = 'desc';

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database   = $database;
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
	

	/**
	 * This method returns last inserted row
	 * @return int	return id of last inserted row
	*/
	function getLastInsertedId()
	{
		$id = $this->database->query("SELECT LAST_INSERT_ID()")->fetchField();

		return $id;
	}


	/** 
	 * This method returns all product_lang where FK equals to product_id
	 * @param string $productId   product id
	 * @return Object			   return product_lang
	 */
	public function getAllProductLang($productId)
	{
		$product_lang = $this->database->table(self::PRODUCT_LANG_TABLE)
			->where(self::COLUMN_PRODUCT_ID, $productId);

		if ( !$product_lang )
		{
			throw new Nette\Application\BadRequestException("PRODUCT_LANG_DOESNT_EXIST");
		}

		return $product_lang;
	}


	/** 
	 * This method returns one product_lang where FK equals to product_id and lang_id
	 * @param string $productId   product id
	 * @param string $langId	   language id
	 * @return Object			   return product_lang
	 */
	public function getProductLang($productId, $langId)
	{
		$product_lang = $this->database->table(self::PRODUCT_LANG_TABLE)
			->where(self::COLUMN_PRODUCT_ID, $productId)
			->where(self::COLUMN_FK_LANG_ID, $langId)
			->fetch();

		if ( !$product_lang )
		{
			throw new Nette\Application\BadRequestException("PRODUCT_LANG_DOESNT_EXIST");
		}

		return $product_lang;
	}

	public function insert($values)
	{
		if ($this->database->table(self::PRODUCT_TABLE)->where(self::COLUMN_NAME, $values->name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");

		$data = array();
		$data["name"]  = $values->name;
		$data["short_desc"] = $values->short_desc;
		$data["desc"] = $values->desc;
		$data["status"] = $values->status;
		$data["price_sell"] = $values->price_sell;
		$data["brand_id"] = 1;//$values->brand_id;

		return $this->database->table(self::PRODUCT_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$product = $this->database->table(self::PRODUCT_TABLE)->where(self::COLUMN_ID, $id);

		if (!$product)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$product->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_SHORT_DESC => $values->short_desc,
			self::COLUMN_DESC => $values->desc,
			self::COLUMN_STATUS => $values->status,
			self::COLUMN_PRICE_SELL => $values->price_sell,
			));
	}

	public function remove($id, $state = "parent")
	{
		return $this->database->table(self::PRODUCT_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}


	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $productId   id of product
	 */
	public function translateData($langId, $productId, $data, $method)
	{
		if($method == '0')
		{
			$this->database->table(self::PRODUCT_LANG_TABLE)->insert(array(
				self::COLUMN_PRODUCT_ID => $productId,
				self::COLUMN_FK_LANG_ID => $langId,
				self::COLUMN_TNAME => $data['name'],
				self::COLUMN_TSHORT_DESC => $data['short_desc'],
				self::COLUMN_TDESC => $data['desc'],
				));
		}
		else
		{
			$product_lang = self::getProductLang($productId, $langId);
			$product_lang->update(array(
				self::COLUMN_TNAME => $data['name'],
				self::COLUMN_TSHORT_DESC => $data['short_desc'],
				self::COLUMN_TDESC => $data['desc'],
				));
		}
	}
}