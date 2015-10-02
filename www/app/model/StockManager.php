<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * stock management.
 */
class StockManager extends Nette\Object
{
	const 
		STOCK_TABLE = 'stock',
		COLUMN_ID = 'id',
		COLUMN_ADDRESS_ID = 'address_id',
		COLUMN_NAME = 'name',
		COLUMN_COUNT_PRODUCTS = 'count_products',
		

		ADDRESS_TABLE = 'address';


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database   = $database;
	}

	public function getAll()
	{
		return $this->database->table('stock');
	}

	/*Get stock*/
	public function getstock($stockId)
	{
		$stock =  $this->database->table(self::STOCK_TABLE)->where(self::COLUMN_ID, $stockId)->fetch();

		if ( !$stock )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $stock;
	}

	public function insert($values)
	{
		if ( $values->address_id == "default" )
		{
			$values->address_id = self::addAddress($values);
		}

		$data = array();
		$data["name"]  = $values->name;
		$data["count_products"] = $values->count_products;
		$data["address_id"] = $values->address_id;

		$stock = $this->database->table(self::STOCK_TABLE)->insert($data);

		return $stock;
	}

	private function addAddress($values)
	{
		$data = array();
		$data['street'] = $values->street;
		$data['street_no'] = $values->street_no;
		$data['city'] = $values->city;
		$data['zip_code'] = $values->zip_code;
		$data['country'] = $values->country;

		return $this->database->table(self::ADDRESS_TABLE)
					->insert($data);
	}

	public function edit($id, $values)
	{		


		if ( $values->address_id == "default" )
		{
			$values->address_id = self::addAddress($values);
		}

		$stock = $this->database->table(self::STOCK_TABLE)->where(self::COLUMN_ID, $id);


		if (!$stock)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$stock->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_COUNT_PRODUCTS => $values->count_products,
			self::COLUMN_ADDRESS_ID	 => $values->address_id,
			));
	}

	public function remove($id)
	{
		return $this->database->table(self::STOCK_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}