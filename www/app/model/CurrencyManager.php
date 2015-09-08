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
		COLUMN_RATE = 'rate';

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