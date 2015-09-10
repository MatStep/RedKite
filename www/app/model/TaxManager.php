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
		COLUMN_VALUE = 'value',
		COLUMN_ACTIVE = 'active';

	public $taxesArray = array();

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

	/*Get active tax*/
	public function getActiveTax()
	{
		return $this->database->table(self::TAX_TABLE)->where(self::COLUMN_ACTIVE, 1)->fetch();
	}

	/*Get all taxes in array */
	public function getAllTaxesAsArray() 
	{
		$taxes = self::getAll();
		$taxArray = array();

		foreach ($taxes as $tax) {
			$tax = $tax->toArray();
			$tax['selectName'] = '';
			array_push($taxArray, $tax);
		}

		$taxes = $taxArray;
		$taxArray = array();

		// If tax is active don't add it to taxArray, tax will be added later as first
		foreach ($taxes as $tax) 
		{
			$tax['selectName'] = $tax['selectName'] . $tax['name'];
			if($tax['id'] == self::getActiveTax()->id)
			{
				$tax['selectName'] = $tax['selectName'] . " (aktívne)";
				$active = $tax['selectName'];
			}
			else
			{
				array_push($taxArray, $tax);
			}
		}

		$taxes = $taxArray;
		$taxArray = array();
		foreach($taxes as $tax) 
		{
			array_push($this->taxesArray,$tax);	
			array_merge($this->taxesArray, $taxes);
		}
		
		//Edit taxes for select with tax name
		$taxArray[0] = $active;
		$taxes = $this->taxesArray;

		foreach ($taxes as $tax) 
		{
			$taxArray[$tax['id']]= $tax['selectName'];	
		}
		return $taxArray;
	}

	/*Unset active tax*/
	public function unsetActive()
	{
		$tax = self::getActiveTax();

		$tax->update(array(
			self::COLUMN_ACTIVE => 0,
			));
	}

	/*Set a new tax active*/
	public function setActive($values)
	{
		$tax = $this->database->table(self::TAX_TABLE)->where(self::COLUMN_ID, $values->tax);

		self::unsetActive();

		$tax->update(array(
			self::COLUMN_ACTIVE => 1,
			));
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