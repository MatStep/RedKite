<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;


/**
 * Category management
 */
class CategoryManager extends Nette\Object
{
	const
		CATEGORY_TABLE = 'category',
		COLUMN_ID = 'id',
		COLUMN_PARENT_ID = 'parent_id',
		COLUMN_ICON = 'icon',
		COLUMN_DEPTH = 'depth',

		LANG_TABLE = 'lang',
		COLUMN_LANG_ID = 'id',

		CATEGORY_LANG_TABLE = "category_lang",
		COLUMN_CATEGORY_LANG_ID = "id",
		COLUMN_CATEGORY_ID = "category_id",
		COLUMN_FK_LANG_ID = "lang_id",
		COLUMN_NAME = "name";

	/** @var Nette\Database\Context */
	private $database;

	/** @var App\Model\LanguageManager */
    public $languages;

	/** @var array */
	public $categoriesArray = array();

	/**
	 * Database constructor
	 */
	public function __construct(Nette\Database\Context $database, LanguageManager $languages)
	{
		$this->database   = $database;
		$this->languages  = $languages;
	}


	/**
	 * getAll method returns all categories.
	 * @param  boolean $parent If value is FALSE, method return all categories included their childrens. TRUE return just main categories.
	 * @return Object          Categories from database
	 */
	public function getAll($parent = TRUE)
	{
		$q = $this->database->table('category');
		if ($parent)
			$q->where('parent_id', 0);
		return $q;
	}


	/**
	 * Get category method returns category with categoryId
	 * @param int $categoryId	id of category
	 * @return Object	Category with $categoryId
	 */
	public function getCategory($categoryId)
	{
		$category =  $this->database->table(self::CATEGORY_TABLE)
						  ->where(self::COLUMN_ID, $categoryId)
						  ->fetch();

		if ( !$category )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $category;
	}


	/**
	 * This method sort categories with parent categories and their child following
	 * @param Object $categories	Categories from database
	 * @param int $categoryId		id of parent category
	 * @return Array				return array of sorted subcategories
	 */
	public function sortCategories($categories, $categoryId) 
	{
		$i = 0;
		$subCatArray = array();	
		foreach ($categories as $category) 
		{
			if ( $category['parent_id'] == $categoryId ) 
			{
				array_push($this->categoriesArray,$category);
				array_merge($this->categoriesArray, self::sortCategories($categories, $category['id']));
			}			
		}
		return $subCatArray;
	}


	/**
	 * Get all categories in array where subcategories are intended with spaces before category name
	 * @return Array	method return array with sorted categories
	 */
	public function getAllCategoriesAsArray() 
	{
		$categories = self::getAll(FALSE);
		$catArray = array();

		foreach ($categories as $category) {
			$category = $category->toArray();
			$category['selectName'] = '';
			array_push($catArray, $category);
		}
		$categories = $catArray;
		$catArray = array();

		foreach ($categories as $category) 
		{
			for ( $i = 0; $i < $category['depth']; $i++)
			{
				$category['selectName'] = "\xc2\xa0 \xc2\xa0 \xc2\xa0 \xc2\xa0" . $category['selectName'];
			}
			$category['selectName'] = $category['selectName'] . self::getCategoryLang($category['id'], $this->languages->getLanguageByName($this->languages->getLanguage()))['name'];
			array_push($catArray, $category);
		}

		$categories = $catArray;
		$catArray = array();
		foreach($categories as $category) 
		{
			if ( $category['depth'] == 0 ) 
			{
				array_push($this->categoriesArray,$category);	
				array_merge($this->categoriesArray, self::sortCategories($categories, $category['id']));
			}
		}

		//Edit categories for select with category name
		$i = 0;
		$catArray = array();
		$catArray[0] = '';
		$categories = $this->categoriesArray;

		foreach ($categories as $category) 
		{
			$catArray[$category['id']]= $category['selectName'];	
		}
		return $catArray;
	}


	/**
	 * This method get all subscribers for specific category
	 * @param int $categoryId	id of category
	 * @return Object	method returns subcategories that have parant_id same as category_id of parent category
	 */
	public function getSubcategories($categoryId)
	{
		return $this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_PARENT_ID, $categoryId);
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
	 * This method returns all category_lang where FK equals to category_id
	 * @param string $categoryId   category id
	 * @return Object			   return category_lang
	 */
	public function getAllCategoryLang($categoryId)
	{
		$category_lang = $this->database->table(self::CATEGORY_LANG_TABLE)
			->where(self::COLUMN_CATEGORY_ID, $categoryId);

		if ( !$category_lang )
		{
			throw new Nette\Application\BadRequestException("CATEGORY_LANG_DOESNT_EXIST");
		}

		return $category_lang;
	}


	/** 
	 * This method returns one category_lang where FK equals to category_id and lang_id
	 * @param string $categoryId   category id
	 * @param string $langId	   language id
	 * @return Object			   return category_lang
	 */
	public function getCategoryLang($categoryId, $langId)
	{
		$category_lang = $this->database->table(self::CATEGORY_LANG_TABLE)
			->where(self::COLUMN_CATEGORY_ID, $categoryId)
			->where(self::COLUMN_FK_LANG_ID, $langId)
			->fetch();

		if ( !$category_lang )
		{
			throw new Nette\Application\BadRequestException("CATEGORY_LANG_DOESNT_EXIST");
		}

		return $category_lang;
	}


	/**
	 * Method inserts new category in database
	 * @param string $name		Name of category
	 * @param int $parent_id	Identifier of parent category
	 * @param string $icon		Icon of category
	 * @param int $depth		depth of category, tells how many parent categories category have
	 * @return Object			Inserted row
	*/
	public function insert($parent_id = 0, $icon, $depth = 0)
	{
		$data = array();
		$data["parent_id"] = $parent_id;
		$data["icon"] 	   = $icon;
		$data["depth"] 	   = $depth;

		return $this->database->table(self::CATEGORY_TABLE)->insert($data);

	}


	/**
	 * Edit category in database
	 * @param int $id			Identifier of category
	 * @param array $values		Values of catgory
	 * @return void
	*/
	public function edit($id, $values)
	{
		$category = $this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_ID, $id);

		if (!$category)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$category->update(array(
			self::COLUMN_ICON => $values->icon,
			self::COLUMN_PARENT_ID => $values->parent,
			self::COLUMN_DEPTH => $values->depth,
			));
	}


	/**
	 * Remove category from database
	 * @param int $id			Identifier of category	
	 * @return Object			Removed category
	*/
	public function remove($id)
	{
		$subcategories = self::getSubcategories($id);

		while($subcategories->count() > 0) {
			foreach($subcategories as $subcategory)
			{
				$subcategory->update(array(
					self::COLUMN_PARENT_ID => NULL,
					self::COLUMN_DEPTH => 0,
					));
			}
			$subcategories = self::getSubcategories($subcategory['id']);
		}

		// delete all rows where is category located in category lang

		$allCategoryLang = self::getAllCategoryLang($id);

		while($allCategoryLang->count() > 0) {
			foreach($allCategoryLang as $categoryLang)
			{
				$categoryLang->delete();
			}
		}

		return $this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}


	/** 
	 * translate data
	 * @param int $lang 		   language id
	 * @param array $data		   array of data to transalte
	 * @param int $method 		   method defines 0-> adding, 1->updating
	 * @param string $categoryId   id of category
	 */
	public function translateData($langId, $categoryId, $data, $method)
	{
		//Checks if Category name in the language exists
		// if ($this->database->table(self::CATEGORY_LANG_TABLE)
		// 	->where(self::COLUMN_NAME, $data)
		// 	->where(self::COLUMN_FK_LANG_ID, $langId)->count() > 0)
		// 	throw new Nette\Application\BadRequestException("NAME_EXISTS");

		if($method == '0')
		{
			$this->database->table(self::CATEGORY_LANG_TABLE)->insert(array(
				self::COLUMN_CATEGORY_ID => $categoryId,
				self::COLUMN_FK_LANG_ID => $langId,
				self::COLUMN_NAME => $data,
				));
		}
		else
		{
			$category_lang = self::getCategoryLang($categoryId, $langId);
			$category_lang->update(array(
				self::COLUMN_NAME => $data,
				));
		}
	}
}