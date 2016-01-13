<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * App model contains methods and functions called in other managers
 */
class AppModel extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;

	/** @var \App\Model\LanguageManager @inject */
	public $languages;

	public function __construct(Nette\Database\Context $database, LanguageManager $languages)
	{
		$this->database   = $database;
		$this->languages  = $languages;
	}

	/** 
	 * first_second means many to many table, where first is table A and second is table B
	 * This method returns all rows in first_second where FK equals to first_id
	 * @param int $firstId   	  first id
	 * @param string $first		  name of first item column
	 * @param string $second	  name of second item column
	 * @param int $exception	  1 => throws exception 0 => doesn't throw
	 * @return Object			  return first_second
	 */
	public function getAllFirstSecond($firstId, $first, $second, $exception = 1)
	{
		$table = $first . '_' . $second;
		$firstColumn = $first . '_id';

		$first_second = $this->database->table($table)
			->where($firstColumn, $firstId);

		if ( !$first_second && $exception == 1)
		{
			throw new Nette\Application\BadRequestException($table . "_DOESNT_EXIST");
		}

		return $first_second;
	}


	/** 
	 * first_second means many to many table, where first is table A and second is table B
	 * This method returns one first_second where FK equals to first_id and second_id
	 * @param int $firstId   	  first id
	 * @param int $secondId   	  second id
	 * @param string $first		  name of first item column
	 * @param string $second	  name of second item column
	 * @param int $exception	  1 => throws exception 0 => doesn't throw
	 * @return Object			  return first_second
	 */
	public function getFirstSecond($firstId, $secondId, $first, $second, $exception = 1)
	{
		$table = $first . '_' . $second;
		$firstColumn = $first . '_id';
		$secondColumn = $second . '_id';

		$first_second = $this->database->table($table)
			->where($firstColumn, $firstId)
			->where($secondColumn, $secondId)
			->fetch();

		if ( !$first_second && $exception == 1)
		{
			throw new Nette\Application\BadRequestException($table . "_DOESNT_EXIST");
		}

		return $first_second;
	}

	/**
	 * This method orders items
	 * @param string $table		name of table where is item
	 * @param int $itemId		id of item
	 * @param int $itemOrder	number of order, array of items
	 * @return void
	 */
	public function orderItems($table, $itemId, $itemOrder)
	 {
		for ( $i = 0; $i < count($itemOrder); $i++ ) 
		{
			$this->database->table($table)
				 ->where('id = ?', $itemOrder[$i])
				 ->update(array('order' => $i + 1));
		}
	}
}