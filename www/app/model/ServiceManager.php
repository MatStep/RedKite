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
		COLUMN_IMG_PATH = 'img_path',

		LANG_TABLE = 'lang',
		COLUMN_LANG_ID = 'id',

		SERVICE_LANG_TABLE = "service_lang",
		COLUMN_SERVICE_LANG_ID = "id",
		COLUMN_SERVICE_ID = "service_id",
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
		return $this->database->table('service');
	}

	/*Get service*/
	public function getService($serviceId)
	{
		$service =  $this->database->table(self::SERVICE_TABLE)->where(self::COLUMN_ID, $serviceId)->fetch();

		if ( !$service )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $service;
	}

	public function getImage($id)
	{
		return $this->database->table(self::SERVICE_TABLE)
			->where(self::COLUMN_ID, $id)
			->fetch()[self::COLUMN_IMG_PATH];
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
	 * This method returns all service_lang where FK equals to service_id
	 * @param string $serviceId   service id
	 * @return Object			   return service_lang
	 */
	public function getAllServiceLang($serviceId)
	{
		$service_lang = $this->database->table(self::SERVICE_LANG_TABLE)
			->where(self::COLUMN_SERVICE_ID, $serviceId);

		if ( !$service_lang )
		{
			throw new Nette\Application\BadRequestException("SERVICE_LANG_DOESNT_EXIST");
		}

		return $service_lang;
	}


	/** 
	 * This method returns one service_lang where FK equals to service_id and lang_id
	 * @param string $serviceId   service id
	 * @param string $langId	   language id
	 * @return Object			   return service_lang
	 */
	public function getServiceLang($serviceId, $langId)
	{
		$service_lang = $this->database->table(self::SERVICE_LANG_TABLE)
			->where(self::COLUMN_SERVICE_ID, $serviceId)
			->where(self::COLUMN_FK_LANG_ID, $langId)
			->fetch();

		if ( !$service_lang )
		{
			throw new Nette\Application\BadRequestException("SERVICE_LANG_DOESNT_EXIST");
		}

		return $service_lang;
	} 

	public function insert($values)
	{
		if ($this->database->table(self::SERVICE_TABLE)->where(self::COLUMN_NAME, $values->name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");

		if($values->image == "")
			$imgUrl = "";
		else
			$imgUrl = $this->imageManager->getImage($values->image, "services");

		$data = array();
		$data["img_path"]  = $imgUrl;

		return $this->database->table(self::SERVICE_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$service = $this->database->table(self::SERVICE_TABLE)->where(self::COLUMN_ID, $id);

		if (!$service)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		if($values->image == "")
			$imgUrl = "";
		else
			$imgUrl = $this->imageManager->getImage($values->image, "services");

		$service->update(array(
			self::COLUMN_IMG_PATH => $imgUrl,
			));
	}

	public function remove($id)
	{
		// delete all rows where is service located in service lang

		$allServiceLang = self::getAllServiceLang($id);

		while($allServiceLang->count() > 0) {
			foreach($allServiceLang as $serviceLang)
			{
				$serviceLang->delete();
			}
		}

		return $this->database->table(self::SERVICE_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}


	public function removeImage($id)
	{
		$service = self::getService($id);

		unlink($service->img_path);

		$service->update(array(self::COLUMN_IMG_PATH => ''));
	}


	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $serviceId   id of service
	 */
	public function translateData($langId, $serviceId, $data, $method)
	{

		if($method == '0')
		{
			$this->database->table(self::SERVICE_LANG_TABLE)->insert(array(
				self::COLUMN_SERVICE_ID => $serviceId,
				self::COLUMN_FK_LANG_ID => $langId,
				self::COLUMN_NAME => $data['name'],
				self::COLUMN_DESC => $data['desc'],
				));
		}
		else
		{
			$service_lang = self::getServiceLang($serviceId, $langId);
			$service_lang->update(array(
				self::COLUMN_NAME => $data['name'],
				self::COLUMN_DESC => $data['desc'],
				));
		}
	}
}