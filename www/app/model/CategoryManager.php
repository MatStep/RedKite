<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;


/**
 * Category management.
 */
class CategoryManager extends Nette\Object
{
	const
		CATEGORY_TABLE = 'category',
		COLUMN_ID = 'id',
		COLUMN_PARENT_ID = 'parent_id',
		COLUMN_ICON = 'icon',
		COLUMN_DEPTH = 'depth',
		COLUMN_NAME = 'name';

	public $categoriesArray = array();

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database   = $database;
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
			$q->where('parent_id', NULL);
		return $q;
	}

	/*Get category*/
	public function getCategory($categoryId)
	{
		$category =  $this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_ID, $categoryId)->fetch();

		if ( !$category )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $category;
	}

	/*Sort categories*/
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

	/*Get all categories in array where subcategories are intended with spaces before category name*/
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
			$category['selectName'] = $category['selectName'] . $category['name'];
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

	/*Get subcategories*/
	public function getSubcategories($categoryId)
	{
		return $this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_PARENT_ID, $categoryId);
	}

	public function insert($name, $parent_id = 0, $icon, $depth = 0)
	{
		if ($this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_NAME, $name)->count() > 0)
			throw new Nette\Application\BadRequestException("NAME_EXISTS");

		$data = array();
		$data["name"] 	 = $name;
		$data["parent_id"] = $parent_id;
		$data["icon"] 	   = $icon;
		$data["depth"] 	   = $depth;

		return $this->database->table(self::CATEGORY_TABLE)->insert($data);

	}

	public function edit($id, $values)
	{
		$category = $this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_ID, $id);

		if (!$category)
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		$category->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_ICON => $values->icon,
			self::COLUMN_PARENT_ID => $values->parent,
			self::COLUMN_DEPTH => $values->depth,
			));
	}

	public function remove($id, $state = "parent")
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
		return $this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}