<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * address management.
 */
class AddressManager extends Nette\Object
{
	const 
		ADDRESS_TABLE = 'address',
		COLUMN_ID = 'id',
		COLUMN_STREET = 'street',
		COLUMN_STREET_NO = 'street_no',
		COLUMN_CITY = 'city',
		COLUMN_COUNTRY = 'country',
		COLUMN_ZIP_CODE = 'zip_code';


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database   = $database;
	}

	public function getAll()
	{
		return $this->database->table('address');
	}

	/*Get address*/
	public function getaddress($addressId)
	{
		$address =  $this->database->table(self::ADDRESS_TABLE)->where(self::COLUMN_ID, $addressId)->fetch();

		if ( !$address )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $address;
	}

	public function insert($values)
	{

		$data = array();
		$data["street"]  = $values->street;
		$data["street_no"] = $values->street_no;
		$data["city"] = $values->city;
		$data["country"] = $values->country;
		$data["zip_code"] = $values->zip_code;

		return $this->database->table(self::ADDRESS_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$address = $this->database->table(self::ADDRESS_TABLE)->where(self::COLUMN_ID, $id);

		if (!$address)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$address->update(array(
			self::COLUMN_STREET => $values->street,
			self::COLUMN_STREET_NO => $values->street_no,
			self::COLUMN_CITY => $values->city,
			self::COLUMN_COUNTRY => $values->country,
			self::COLUMN_ZIP_CODE => $values->zip_code,
			));
	}

	public function remove($id)
	{
		return $this->database->table(self::ADDRESS_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}