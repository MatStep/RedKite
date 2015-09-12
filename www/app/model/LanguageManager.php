<?php

namespace App\Model;

use Nette,
	App\Model,
	Exception;

/**
 * Language management
 *
 * @author		Martin Stepanek
 *
 */

class LanguageManager extends Nette\Object
{
	const
		LANGUAGE_TABLE = 'lang',
		COLUMN_ID = 'id',
		COLUMN_IMG = 'img',
		COLUMN_ACTIVE = 'active',
		COLUMN_ISO_CODE = 'iso_code',
		COLUMN_LANGUAGE_CODE = 'language_code';

	/** @var Nette\Database\Context */
	private $database;

	/** @var \Kdyby\Translation\Translator */
	private $translator;

	/** @var string */
	public $language;

	/**
	 * Database and translator constructor
	 */
	public function __construct(Nette\Database\Context $database, \Kdyby\Translation\Translator $translator)
	{
		$this->database   = $database;
		$this->translator = $translator;
		$this->language   = $this->translator->getLocale();
	}


	/**
	 * Method return all languages in database
	 * @return Object	Languages from database
	 */
	public function getAll()
	{
		return $this->database->table(self::LANGUAGE_TABLE);
	}

	/**
	 * Method return all active languages in database
	 * @return Object	Active languages from database
	 */
	public function getAllActive()
	{
		return $this->database->table(self::LANGUAGE_TABLE)->where(self::COLUMN_ACTIVE, 1)->fetch();
	}


	/**
	 * Method retrurns current language
	 * @return string		Language that Translator is Using
	 */
	public function getLanguage()
	{
		return $this->language;
	}
}