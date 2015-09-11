<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * service management.
 */
class ServiceManager extends Nette\Object
{
	const 
		SERVICE_TABLE = 'service',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name',
		COLUMN_DESC = 'desc';


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database   = $database;
	}

	public function getAll()
	{
		return $this->database->table('service');
	}

	/*Get service*/
	public function getservice($serviceId)
	{
		$service =  $this->database->table(self::SERVICE_TABLE)->where(self::COLUMN_ID, $serviceId)->fetch();

		if ( !$service )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $service;
	}

	public function insert($values)
	{
		if ($this->database->table(self::SERVICE_TABLE)->where(self::COLUMN_NAME, $values->name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");

		$data = array();
		$data["name"]  = $values->name;
		$data["desc"] = $values->desc;

		return $this->database->table(self::SERVICE_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$service = $this->database->table(self::SERVICE_TABLE)->where(self::COLUMN_ID, $id);

		if (!$service)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$service->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_DESC => $values->desc,
			));
	}

	public function remove($id)
	{
		return $this->database->table(self::SERVICE_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}