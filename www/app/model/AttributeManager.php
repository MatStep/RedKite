<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * attribute management.
 */
class AttributeManager extends Nette\Object
{
	const 
		ATTRIBUTE_TABLE = 'attribute',
		COLUMN_ID = 'id',

		LANG_TABLE = 'lang',
		COLUMN_LANG_ID = 'id',

		ATTRIBUTE_LANG_TABLE = "attribute_lang",
		COLUMN_ATTRIBUTE_LANG_ID = "id",
		COLUMN_ATTRIBUTE_ID = "attribute_id",
		COLUMN_FK_LANG_ID = "lang_id",
		COLUMN_NAME = 'name';


	/** @var Nette\Database\Context */
	private $database;

	/** @var \App\Model\AppModel @inject */
	public $model;

	/** @var \App\Model\LanguageManager @inject */
	public $languages;

	public function __construct(Nette\Database\Context $database, LanguageManager $languages, AppModel $model)
	{
		$this->database   = $database;
		$this->languages  = $languages;
		$this->model	  = $model;
	}

	public function getAll()
	{
		return $this->database->table('attribute');
	}

	/*Get attribute*/
	public function getAttribute($attributeId)
	{
		$attribute =  $this->database->table(self::ATTRIBUTE_TABLE)->where(self::COLUMN_ID, $attributeId)->fetch();

		if ( !$attribute )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $attribute;
	}

	/*Get attribute value*/
	public function getAttributeValue($attributeValueId)
	{
		$attributeValue =  $this->database->table('attribute_value')->where(self::COLUMN_ID, $attributeValueId)->fetch();

		if ( !$attributeValue )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $attributeValue;
	}

	public function getAllAttributeValues($attributeId)
	{
		$attributeValues = $this->database->table('attribute_value')->where('attribute_id', $attributeId);

		return $attributeValues;
	}

	public function insert($value)
	{

		$data = array();

		$attribute =  $this->database->table(self::ATTRIBUTE_TABLE)->insert($data);

		//ADD LANGUAGE DATA
        foreach($this->languages->getAllActive() as $lang) {
            self::translateData($lang->id, $attribute->id, $value, 0);
        }

		return $attribute;

	}

	public function insertAttributeValue($values)
	{

		$data = array();
		$data['attribute_id'] = $values->attributeId;
		$data['from'] = $values->from;
		$data['to'] = $values->to;

		$attributeValue =  $this->database->table('attribute_value')->insert($data);

		//ADD LANGUAGE DATA
        foreach($this->languages->getAllActive() as $lang) {
            self::translateDataAttributeValue($lang->id, $attributeValue->id, $values, 0);
        }

		return $attributeValue;

	}

	public function edit($id, $values)
	{
		$attribute = $this->database->table(self::ATTRIBUTE_TABLE)->where(self::COLUMN_ID, $id);

		if (!$attribute)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$currentLanguage = $this->languages->getLanguageByName($this->languages->getLanguage());

		//EDIT LANGUAGE DATA
        self::translateData($currentLanguage, $id, $values, 1);
	}

	public function editAttributeValue($id, $values)
	{
		$attribute_value = $this->database->table('attribute_value')->where(self::COLUMN_ID, $id);

		if (!$attribute_value)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$currentLanguage = $this->languages->getLanguageByName($this->languages->getLanguage());

		//EDIT LANGUAGE DATA
        self::translateDataAttributeValue($currentLanguage, $id, $values, 1);
	}

	public function remove($id)
	{
		// delete all rows where is attribute located in attribute lang

		$allAttributeLang = $this->model->getAllFirstSecond($id,'attribute','lang');

		while($allAttributeLang->count() > 0) {
			foreach($allAttributeLang as $attributeLang)
			{
				$attributeLang->delete();
			}
		}

		return $this->database->table(self::ATTRIBUTE_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}

	public function removeAttributeValue($id)
	{
		// delete all rows where is attribute located in attribute lang

		$allAttributeValueLang = $this->model->getAllFirstSecond($id,'attribute_value','lang');

		while($allAttributeValueLang->count() > 0) {
			foreach($allAttributeValueLang as $attributeValueLang)
			{
				$attributeValueLang->delete();
			}
		}

		return $this->database->table('attribute_value')->where(self::COLUMN_ID, $id)->delete();
	}


	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $attributeId   id of attribute
	 */
	public function translateData($langId, $attributeId, $data, $method)
	{

		if($method == '0')
		{
			$this->database->table(self::ATTRIBUTE_LANG_TABLE)->insert(array(
				self::COLUMN_ATTRIBUTE_ID => $attributeId,
				self::COLUMN_FK_LANG_ID => $langId,
				self::COLUMN_NAME => $data,
				));
		}
		else
		{
			$attribute_lang = $this->model->getFirstSecond($attributeId, $langId, 'attribute', 'lang');
			$attribute_lang->update(array(
				self::COLUMN_NAME => $data,
				));
		}
	}

	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $attributeId   id of attribute
	 */
	public function translateDataAttributeValue($langId, $attributeValueId, $data, $method)
	{

		if($method == '0')
		{
			$this->database->table('attribute_value_lang')->insert(array(
				'attribute_value_id' => $attributeValueId,
				'lang_id' => $langId,
				'name' => $data['name'],
				));
		}
		else
		{
			$attribute_value_lang = $this->model->getFirstSecond($attributeValueId, $langId, 'attribute_value', 'lang');
			$attribute_value_lang->update(array(
				'name' => $data['name'],
				));
		}
	}
}