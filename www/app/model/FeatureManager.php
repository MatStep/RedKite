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

	/** @var \App\Model\AppModel @inject */
	public $model;

	/** @var \App\Model\LanguageManager @inject */
	public $languages;


	public function __construct(Nette\Database\Context $database, \App\Model\ImageManager $imageManager, LanguageManager $languages, AppModel $model)
	{
		$this->database   = $database;
		$this->languages  = $languages;
		$this->model	  = $model;
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
		$data = array();
		// $data["name"]  = $values->name;
		$feature = $this->database->table(self::FEATURE_TABLE)->insert($data);

		//ADD LANGUAGE DATA
        foreach($this->languages->getAllActive() as $lang) {
            self::translateData($lang->id, $feature->id, $values, 0);
        }

		return $feature;

	}

	public function edit($id, $values)
	{
		$feature = $this->database->table(self::FEATURE_TABLE)->where(self::COLUMN_ID, $id);

		$currentLanguage = $this->languages->getLanguageByName($this->languages->getLanguage());

		if (!$feature)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		//EDIT LANGUAGE DATA
        self::translateData($currentLanguage, $id, $values, 1);
	}

	public function remove($id)
	{
		// delete all rows where is feature located in feature lang

		$allFeatureLang = $this->model->getAllFirstSecond($id,'feature','lang');

		while($allFeatureLang->count() > 0) {
			foreach($allFeatureLang as $featureLang)
			{
				$featureLang->delete();
			}
		}

		return $this->database->table(self::FEATURE_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}

	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $featureId   id of feature
	 */
	public function translateData($langId, $featureId, $data, $method)
	{

		if($method == '0')
		{
			$this->database->table('feature_lang')->insert(array(
				'feature_id' => $featureId,
				'lang_id' => $langId,
				'name' => $data['name'],
				));
		}
		else
		{
			$feature_lang = $this->model->getFirstSecond($featureId, $langId, 'feature', 'lang');
			$feature_lang->update(array(
				'name' => $data['name'],
				));
		}
	}

}