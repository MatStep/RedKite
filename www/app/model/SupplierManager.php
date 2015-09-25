<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * supplier management.
 */
class SupplierManager extends Nette\Object
{
	const 
		SUPPLIER_TABLE = 'supplier',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name',
		COLUMN_DATE_FROM = 'date_from',
		COLUMN_ADDRESS_ID = 'address_id',

		ADDRESS_TABLE = 'address';


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database   = $database;
	}

	public function getAll()
	{
		return $this->database->table('supplier');
	}

	/*Get supplier*/
	public function getsupplier($supplierId)
	{
		$supplier =  $this->database->table(self::SUPPLIER_TABLE)->where(self::COLUMN_ID, $supplierId)->fetch();

		if ( !$supplier )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $supplier;
	}

	public function insert($values)
	{
		if ( $values->address_id == "default" )
		{
			$values->address_id = self::addAddress($values);
		}

		$data = array();
		$data["name"]  = $values->name;
		$data["date_from"] = date('Y-m-d', strtotime($values->date_from));
		$data["address_id"] = $values->address_id;

		$supplier = $this->database->table(self::SUPPLIER_TABLE)->insert($data);

		return $supplier;
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

		$supplier = $this->database->table(self::SUPPLIER_TABLE)->where(self::COLUMN_ID, $id);

		$dateFromWithRightForm = date('Y-m-d', strtotime($values->date_from));

		if (!$supplier)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$supplier->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_DATE_FROM => $dateFromWithRightForm,
			self::COLUMN_ADDRESS_ID	 => $values->address_id,
			));
	}

	public function remove($id)
	{
		return $this->database->table(self::SUPPLIER_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}