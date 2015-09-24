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
		COLUMN_DATE_FROM = 'date_from';


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

		$data = array();
		$data["name"]  = $values->name;
		$data["date_from"] = $values->date_from;

		return $this->database->table(self::SUPPLIER_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$supplier = $this->database->table(self::SUPPLIER_TABLE)->where(self::COLUMN_ID, $id);

		if (!$supplier)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$supplier->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_DATE_FROM => $values->date_from,
			));
	}

	public function remove($id)
	{
		return $this->database->table(self::SUPPLIER_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}