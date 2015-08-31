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

		// if (!$category)
		// {
		// 	throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		// }

		$category->update(array(
			self::COLUMN_NAME => $values->name,
			self::COLUMN_ICON => $values->icon,
			self::COLUMN_PARENT_ID => $values->parent,
			self::COLUMN_DEPTH => $values->depth,
			));
	}

	public function remove($id, $state = "parent")
	{
		return $this->database->table(self::CATEGORY_TABLE)->where(self::COLUMN_ID, $id)->delete();
	}
}