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

	/** @var array */
	public $languagesArray = array();

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
	 * Method returns language selected from DB by id
	 * @param int $code 	id of language
	 * @return Object		Current language from database
	 */
	public function getLanguageById($id)
	{
		$language =  $this->database->table(self::LANGUAGE_TABLE)->where(self::COLUMN_ID, $id)->fetch();

		if ( !$language )
		{
			throw new Nette\Application\BadRequestException("DOESNT_EXIST");
		}

		return $language;
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
	 * Get all languages in array
	 * @return array	Array of languages for select
	 */
	public function getAllLanguagesAsArray() 
	{
		$languages = self::getAll();
		$languageArray = array();

		foreach ($languages as $language) {
			$language = $language->toArray();
			$language['selectName'] = '';
			array_push($languageArray, $language);
		}

		$languages = $languageArray;
		$languageArray = array();

		// If language is active don't add it to languageArray, language will be added later as first
		foreach ($languages as $language) 
		{
			$language['selectName'] = $language['selectName'] . $language['name'];
			if($language['id'] == self::getLanguageByName(self::getLanguage())->id)
			{
				$language['selectName'] = $language['selectName'] . " (aktívne)";
				$active = $language['selectName'];
			}
			else
			{
				array_push($languageArray, $language);
			}
		}

		$languages = $languageArray;
		$languageArray = array();
		foreach($languages as $language) 
		{
			array_push($this->languagesArray,$language);	
			array_merge($this->languagesArray, $languages);
		}
		
		//Edit languages for select with language name
		$languageArray[0] = $active;
		$languages = $this->languagesArray;

		foreach ($languages as $language) 
		{
			$languageArray[$language['id']]= $language['selectName'];	
		}
		return $languageArray;
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