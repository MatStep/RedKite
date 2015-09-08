<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * Brand management.
 */
class BrandManager extends Nette\Object
{
	const 
		BRAND_TABLE = 'brand',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name',
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
		return $this->database->table('brand');
	}

	/*Get brand*/
	public function getBrand($brandId)
	{
		$brand =  $this->database->table(self::BRAND_TABLE)->where(self::COLUMN_ID, $brandId)->fetch();

		if ( !$brand )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $brand;
	}

	public function insert($values)
	{
		if ($this->database->table(self::BRAND_TABLE)->where(self::COLUMN_NAME, $values->name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");

		if($values->logo == "")
			$imgUrl = "";
		else
			$imgUrl = $this->imageManager->getImage($values->logo, "brands");

		$data = array();
		$data["name"]  = $values->name;
		$data["logo_path"] = $imgUrl;

		return $this->database->table(self::BRAND_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$brand = $this->database->table(self::BRAND_TABLE)->where(self::COLUMN_ID, $id);

		if (!$brand)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		if($values->logo == "")
			$imgUrl = "";
		else
			$imgUrl = $this->imageManager->getImage($values->logo, "brands");

		$brand->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_LOGO_PATH => $imgUrl,
			));
	}

	public function remove($id)
	{
		return $this->database->table(self::BRAND_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}

	public function getImage($id)
	{
		return $this->database->table(self::BRAND_TABLE)
			->where(self::COLUMN_ID, $id)
			->fetch()[self::COLUMN_LOGO_PATH];
	}

	public function removeImage($id)
	{
		$brand = self::getBrand($id);

		unlink($brand->logo_path);

		$brand->update(array(self::COLUMN_LOGO_PATH => ''));
	}

}