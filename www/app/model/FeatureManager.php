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

	public function getAllFeatureValue()
	{
		return $this->database->table('feature_value');
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

	/*Get feature value*/
	public function getFeatureValue($featureValueId)
	{
		$featureValue =  $this->database->table('feature_value')->where('id', $featureValueId)->fetch();

		if ( !$featureValue )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $featureValue;
	}

	/*Get feature values*/
	public function getFeatureValues($featureId)
	{
		$featureValues =  $this->database->table('feature_value')->where('feature_id', $featureId);

		if ( !$featureValues )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $featureValues;
	}

	public function insert($values)
	{
		$data = array();

		$feature = $this->database->table(self::FEATURE_TABLE)->insert($data);

		//ADD LANGUAGE DATA
        foreach($this->languages->getAllActive() as $lang) {
            self::translateData($lang->id, $feature->id, $values, 0);
        }

		return $feature;

	}

	public function insertFeatureValue($featureId, $values)
	{
		$data = array();
		$data["feature_id"] = $featureId;

		$featureValue = $this->database->table('feature_value')->insert($data);

		//ADD LANGUAGE DATA
        foreach($this->languages->getAllActive() as $lang) {
            self::translateDataFeatureValue($lang->id, $featureValue->id, $values, 0);
        }

		return $featureValue;

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

	public function editFeatureValue($id, $values)
	{
		$featureValue = $this->database->table('feature_value')->where('id', $id);

		$currentLanguage = $this->languages->getLanguageByName($this->languages->getLanguage());

		if (!$featureValue)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		//EDIT LANGUAGE DATA
        self::translateDataFeatureValue($lang->id, $featureValue->id, $values, 1);
	}

	public function remove($id)
	{
		// delete all rows where is feature located in feature lang

		$allFeatureLang = $this->model->getAllFirstSecond($id,'feature','lang');

		$allFeatureValues = self::getFeatureValues($id);

		$k = $allFeatureValues->count();

		while($allFeatureLang->count() > 0) {
			foreach($allFeatureLang as $featureLang)
			{
				$featureLang->delete();
			}
		}

		while($k > 0) {
			foreach($allFeatureValues as $featureValue)
			{
				self::removeFeatureValue($featureValue->id);
				$k--;
			}
		}

		return $this->database->table(self::FEATURE_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}

	public function removeFeatureValue($id)
	{
		// delete all rows where is feature value located in feature value lang

		$allFeatureValueLang = $this->model->getAllFirstSecond($id,'feature_value','lang');

		while($allFeatureValueLang->count() > 0) {
			foreach($allFeatureValueLang as $featureValueLang)
			{
				$featureValueLang->delete();
			}
		}

		return $this->database->table('feature_value')->where('id', $id)->delete();
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

	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $featureValueId   id of featureValue
	 */
	public function translateDataFeatureValue($langId, $featureValueId, $data, $method)
	{

		if($method == '0')
		{
			$this->database->table('feature_value_lang')->insert(array(
				'feature_value_id' => $featureValueId,
				'lang_id' => $langId,
				'value' => $data['value'],
				));
		}
		else
		{
			$feature_value_lang = $this->model->getFirstSecond($featureValueId, $langId, 'feature_value', 'lang');
			$feature_value_lang->update(array(
				'value' => $data['value'],
				));
		}
	}

}