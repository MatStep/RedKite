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
		COLUMN_BRAND_ID = 'brand_id';

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
			self::COLUMN_BRAND_ID => $values->brand_id,
			));
	}

	public function remove($id, $state = "parent")
	{
		return $this->database->table(self::PRODUCT_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}