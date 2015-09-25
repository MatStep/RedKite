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
		COLUMN_LOGO_PATH = 'logo_path',

		LANG_TABLE = 'lang',
		COLUMN_LANG_ID = 'id',

		REFERENCE_LANG_TABLE = "reference_lang",
		COLUMN_REFERENCE_LANG_ID = "id",
		COLUMN_REFERENCE_ID = "reference_id",
		COLUMN_FK_LANG_ID = "lang_id",
		COLUMN_NAME = 'name',
		COLUMN_DESC = 'desc';

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


	/**
	 * This method returns last inserted row
	 * @return int	return id of last inserted row
	*/
	function getLastInsertedId()
	{
		$id = $this->database->query("SELECT LAST_INSERT_ID()")->fetchField();

		return $id;
	}


	/** 
	 * This method returns all reference_lang where FK equals to reference_id
	 * @param string $referenceId   reference id
	 * @return Object			   return reference_lang
	 */
	public function getAllReferenceLang($referenceId)
	{
		$reference_lang = $this->database->table(self::REFERENCE_LANG_TABLE)
			->where(self::COLUMN_REFERENCE_ID, $referenceId);

		if ( !$reference_lang )
		{
			throw new Nette\Application\BadRequestException("REFERENCE_LANG_DOESNT_EXIST");
		}

		return $reference_lang;
	}


	/** 
	 * This method returns one reference_lang where FK equals to reference_id and lang_id
	 * @param string $referenceId   reference id
	 * @param string $langId	   language id
	 * @return Object			   return reference_lang
	 */
	public function getReferenceLang($referenceId, $langId)
	{
		$reference_lang = $this->database->table(self::REFERENCE_LANG_TABLE)
			->where(self::COLUMN_REFERENCE_ID, $referenceId)
			->where(self::COLUMN_FK_LANG_ID, $langId)
			->fetch();

		if ( !$reference_lang )
		{
			throw new Nette\Application\BadRequestException("REFERENCE_LANG_DOESNT_EXIST");
		}

		return $reference_lang;
	}


	public function insert($values)
	{

		if($values->logo == "")
			$imgUrl = "";
		else
			$imgUrl = $this->imageManager->getImage($values->logo, "references");

		$data = array();
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
		{
			$imgUrl = $this->imageManager->getImage($values->logo, "references");

			$reference->update(array(
				self::COLUMN_LOGO_PATH => $imgUrl,
				));
		}
	}

	public function remove($id)
	{
		// delete all rows where is reference located in reference lang

		$allReferenceLang = self::getAllReferenceLang($id);

		while($allReferenceLang->count() > 0) {
			foreach($allReferenceLang as $referenceLang)
			{
				$referenceLang->delete();
			}
		}
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

	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $referenceId   id of reference
	 */
	public function translateData($langId, $referenceId, $data, $method)
	{

		if($method == '0')
		{
			$this->database->table(self::REFERENCE_LANG_TABLE)->insert(array(
				self::COLUMN_REFERENCE_ID => $referenceId,
				self::COLUMN_FK_LANG_ID => $langId,
				self::COLUMN_NAME => $data['name'],
				self::COLUMN_DESC => $data['desc'],
				));
		}
		else
		{
			$reference_lang = self::getReferenceLang($referenceId, $langId);
			$reference_lang->update(array(
				self::COLUMN_NAME => $data['name'],
				self::COLUMN_DESC => $data['desc'],
				));
		}
	}

}