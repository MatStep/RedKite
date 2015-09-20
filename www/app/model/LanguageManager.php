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

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var string */
	public $language;

	/**
	 * Database and translator constructor
	 */
	public function __construct(Nette\Database\Context $database, \Kdyby\Translation\Translator $translator, Nette\Http\Request $httpRequest)
	{
		$this->database    = $database;
		$this->translator  = $translator;
		$this->language    = $this->translator->getLocale();
		$this->httpRequest = $httpRequest;
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
		return $this->database->table(self::LANGUAGE_TABLE)->where(self::COLUMN_ACTIVE, 1);
	}


	/**
	 * Method retrurns current language
	 * @return string		Language that Translator is Using
	 */
	public function getLanguage()
	{
		return $this->language;
	}


	/**
	 * Method returns language selected from DB by code
	 * @param string $code 	Iso code
	 * @return Object		Current language from database
	 */
	public function getLanguageByName($code)
	{
		$language =  $this->database->table(self::LANGUAGE_TABLE)->where(self::COLUMN_ISO_CODE, $code)->fetch();

		if ( !$language )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $language;
	}


	/**
	 * Method set language
	 * @param string $lang	Language to set
	 */
	public function setLanguage($lang)
	{
		$this->language = $lang;
	}


	/**
	 * Method change language in url
	 * @param string $lang	Language to change
	 * @return string	return Url to change
	 */
	public function changeLanguage($lang)
	{
		$base = $this->httpRequest->url->baseUrl;
		$relative = $this->httpRequest->url->relativeUrl;

		$lang_code = strtok($relative, '/');

		// there is + 1 because of slash
		if(strlen($lang_code) == 2)
		{
			$relative = substr($relative, strlen($lang_code) + 1);
		}

		$url = $base . $lang . "/" . $relative;

		return $url;
	}
}