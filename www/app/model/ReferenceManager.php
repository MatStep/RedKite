<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * Reference management.
 */
class ReferenceManager extends Nette\Object
{
	const 
		REFERENCE_TABLE = 'reference',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name',
		CULUMN_DESC = 'desc',
		COLUMN_LOGO_PATH = 'logo_path';

	/** @var Nette\Database\Context */
	private $database;

	/** @var \App\Model\ImageManager */
	public $imageManager;

	public function __construct(Nette\Database\Context $database, \App\Model\ImageManager $imageManager)
	{
		$this->database   = $database;
		$this->imageManager = $imageManager;
	}

	public function getAll()
	{
		return $this->database->table('reference');
	}

	/*Get reference*/
	public function getReference($referenceId)
	{
		$reference =  $this->database->table(self::REFERENCE_TABLE)->where(self::COLUMN_ID, $referenceId)->fetch();

		if ( !$reference )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $reference;
	}

	public function insert($values)
	{
		if ($this->database->table(self::REFERENCE_TABLE)->where(self::COLUMN_NAME, $values->name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");

		if($values->logo == "")
			$imgUrl = "";
		else
			$imgUrl = $this->imageManager->getImage($values->logo, "references");

		$data = array();
		$data["name"]  = $values->name;
		$data["desc"]  = $values->desc;
		$data["logo_path"] = $imgUrl;

		return $this->database->table(self::REFERENCE_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$reference = $this->database->table(self::REFERENCE_TABLE)->where(self::COLUMN_ID, $id);

		if (!$reference)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		if($values->logo == "")
			$imgUrl = "";
		else
			$imgUrl = $this->imageManager->getImage($values->logo, "references");

		$reference->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_DESC => $values->desc,
			self::COLUMN_LOGO_PATH => $imgUrl,
			));
	}

	public function remove($id)
	{
		return $this->database->table(self::REFERENCE_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}

	public function getImage($id)
	{
		return $this->database->table(self::REFERENCE_TABLE)
			->where(self::COLUMN_ID, $id)
			->fetch()[self::COLUMN_LOGO_PATH];
	}

	public function removeImage($id)
	{
		$reference = self::getReference($id);

		unlink($reference->logo_path);

		$reference->update(array(self::COLUMN_LOGO_PATH => ''));
	}

}