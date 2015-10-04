<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * Feature management.
 */
class FeatureManager extends Nette\Object
{
	const 
		FEATURE_TABLE = 'feature',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name';

	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database, \App\Model\ImageManager $imageManager)
	{
		$this->database   = $database;
	}

	public function getAll()
	{
		return $this->database->table('feature');
	}

	/*Get feature*/
	public function getFeature($featureId)
	{
		$feature =  $this->database->table(self::FEATURE_TABLE)->where(self::COLUMN_ID, $featureId)->fetch();

		if ( !$feature )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $feature;
	}


	public function insert($values)
	{
		if ($this->database->table(self::FEATURE_TABLE)->where(self::COLUMN_NAME, $values->name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");


		$data = array();
		$data["name"]  = $values->name;

		return $this->database->table(self::FEATURE_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$feature = $this->database->table(self::FEATURE_TABLE)->where(self::COLUMN_ID, $id);

		if (!$feature)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$feature->update(array(
			self::COLUMN_NAME => $values->name,
			));
	}

	public function remove($id)
	{
		return $this->database->table(self::FEATURE_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}

}