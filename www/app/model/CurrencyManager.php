<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * Currency management.
 */
class CurrencyManager extends Nette\Object
{
	const 
		CURRENCY_TABLE = 'currency',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name',
		COLUMN_SIGN = 'sign',
		COLUMN_RATE = 'rate',
		COLUMN_ACTIVE = 'active';

	public $currenciesArray = array();

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database   = $database;
	}

	public function getAll()
	{
		return $this->database->table('currency');
	}

	/*Get currency*/
	public function getCurrency($currencyId)
	{
		$currency =  $this->database->table(self::CURRENCY_TABLE)->where(self::COLUMN_ID, $currencyId)->fetch();

		if ( !$currency )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $currency;
	}

	/*Get active currency*/
	public function getActiveCurrency()
	{
		return $this->database->table(self::CURRENCY_TABLE)->where(self::COLUMN_ACTIVE, 1)->fetch();
	}

	/*Get all currencies in array */
	public function getAllCurrenciesAsArray() 
	{
		$currencies = self::getAll();
		$currencyArray = array();

		foreach ($currencies as $currency) {
			$currency = $currency->toArray();
			$currency['selectName'] = '';
			array_push($currencyArray, $currency);
		}

		$currencies = $currencyArray;
		$currencyArray = array();

		// If currency is active don't add it to currencyArray, currency will be added later as first
		foreach ($currencies as $currency) 
		{
			$currency['selectName'] = $currency['selectName'] . $currency['name'];
			if($currency['id'] == self::getActiveCurrency()->id)
			{
				$currency['selectName'] = $currency['selectName'] . " (aktívne)";
				$active = $currency['selectName'];
			}
			else
			{
				array_push($currencyArray, $currency);
			}
		}

		$currencies = $currencyArray;
		$currencyArray = array();
		foreach($currencies as $currency) 
		{
			array_push($this->currenciesArray,$currency);	
			array_merge($this->currenciesArray, $currencies);
		}
		
		//Edit currencies for select with currency name
		$currencyArray[0] = $active;
		$currencies = $this->currenciesArray;

		foreach ($currencies as $currency) 
		{
			$currencyArray[$currency['id']]= $currency['selectName'];	
		}
		return $currencyArray;
	}

	/*Unset active currency*/
	public function unsetActive()
	{
		$currency = self::getActiveCurrency();

		$currency->update(array(
			self::COLUMN_ACTIVE => 0,
			));
	}

	/*Set a new currency active*/
	public function setActive($values)
	{
		$currency = $this->database->table(self::CURRENCY_TABLE)->where(self::COLUMN_ID, $values->currency);

		self::unsetActive();

		$currency->update(array(
			self::COLUMN_ACTIVE => 1,
			));
	}

	public function insert($values)
	{
		if ($this->database->table(self::CURRENCY_TABLE)->where(self::COLUMN_NAME, $values->name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");

		$this->database->table(self::CURRENCY_TABLE)->insert(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_SIGN => $values->sign,
			self::COLUMN_RATE => $values->rate,
			));

	}

	public function edit($id, $values)
	{
		$currency = $this->database->table(self::CURRENCY_TABLE)->where(self::COLUMN_ID, $id);

		if (!$currency)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$currency->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_SIGN => $values->sign,
			self::COLUMN_RATE => $values->rate,
			));
	}

	public function remove($id, $state = "parent")
	{
		return $this->database->table(self::CURRENCY_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}