<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * Tax management.
 */
class TaxManager extends Nette\Object
{
	const 
		TAX_TABLE = 'tax',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name',
		COLUMN_VALUE = 'value';

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database   = $database;
	}

	public function getAll()
	{
		return $this->database->table('tax');
	}

	/*Get tax*/
	public function getTax($taxId)
	{
		$tax =  $this->database->table(self::TAX_TABLE)->where(self::COLUMN_ID, $taxId)->fetch();

		if ( !$tax )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $tax;
	}

	public function insert($values)
	{
		if ($this->database->table(self::TAX_TABLE)->where(self::COLUMN_NAME, $values->name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");

		$data = array();
		$data["name"]  = $values->name;
		$data["value"] = $values->value;

		return $this->database->table(self::TAX_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$tax = $this->database->table(self::TAX_TABLE)->where(self::COLUMN_ID, $id);

		if (!$tax)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$tax->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_VALUE => $values->value,
			));
	}

	public function remove($id, $state = "parent")
	{
		return $this->database->table(self::TAX_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}