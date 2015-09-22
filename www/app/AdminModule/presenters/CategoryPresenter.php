<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form;

/**
 * Category presenter
 */
class CategoryPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	private $categories;

    public $languages;

	private $catDepth;

	private $values;

	private $id;

	private $state;

	public function __construct(Model\CategoryManager $categories)
	{
		$this->categories = $categories;
		$this->values = array("parent" => "", "parent_id" => "", "icon" => "");
		$this->id = 0;
		$this->state = "";
	}

	public function renderDefault()
	{
		$this->template->categories = $this->categories->getAll();
        $this->template->languages = parent::getAllLanguages();
	}

    /*Category form*/
	protected function createComponentCategoryForm()
    {
        $form = new Form;
        
        foreach(parent::getAllLanguages() as $lang)
        {

            if($lang->id == parent::getLanguage()->id)
            {
                $form->addText("name_". $lang->iso_code, "Názov" . "(" . $lang->iso_code . ")")
                     ->getControlPrototype()->class("form-control")
                     ->setRequired('Názov je povinný');
            }
        }
        
        $form->addText('icon', "Icon Class *")
        		->setAttribute('class', 'form-control')
        		->setAttribute('placeholder', 'Napíš class ikonky')
        		->setAttribute('value', $this->values['icon'])
        		->setRequired('Ikonka je povinná');

			foreach ($this->categories->getAll(FALSE) as $category) 
			{ 
				$this->catDepth[$category["id"]]   = $category["depth"];
			}        

        	$categories = $this->categories->getAllCategoriesAsArray();

	        $select = $form->addSelect('parent', 'Kategória', $categories)
	    		->setAttribute('class', 'form-control');

	    	if ($this->id)
	    		$select->setDisabled(true);

	    	if ($this->id && isset($this->values["parent_id"]) && $this->values["parent_id"])
	    		$select->setDefaultValue($this->values["parent_id"]);
	    	
        	$form->addSubmit('add', 'Pridať kategóriu')
        			->setAttribute('class', 'btn btn-primary pull-right');
        	
        	$form->addSubmit('edit', 'Uložiť zmeny')
        			->setAttribute('class', 'btn btn-primary pull-right');

        	$form->onSuccess[] = array($this, 'CategoryFormSucceeded');

        return $form;
    }

    public function CategoryFormSucceeded($form, $values)
    {
    	$adding = true;

        $currentLanguage = parent::getLanguage();

    	if ( isset($this->request->getParameters()['categoryId']) )
    	{
    		$categoryId = $this->getParameter('categoryId');
    		$adding = false;
    	}

    	try {
    		if ($adding)
    		{
    			// ADD CATEGORY
    			$parent = $values['parent']? $values['parent'] : 0;
    			$depth   = $parent? ( $this->catDepth[$parent] + 1 ) : 0;
                $this->categories->insert($parent, $values["icon"], $depth);

                //ADD LANGUAGE DATA
                $lastId = $this->categories->getLastInsertedId();

                //Add the same for all languages
                foreach(parent::getAllLanguages() as $lang) {
                    $this->categories->translateData($lang->id, $lastId, $values['name_' . $currentLanguage->iso_code], 0);
                }
    			$this->flashMessage('Kategória úspešne pridaná');
    		}
    		else
    		{
		    	// EDIT CATEGORY
    			$values['depth']   = $values['parent']? ( $this->catDepth[$values['parent']] + 1 ) : 0;
    			$this->categories->edit($categoryId, $values);

                //EDIT LANGUAGE DATA
                    $this->categories->translateData($currentLanguage->id, $categoryId, $values['name_' . $currentLanguage->iso_code], 1);

    			$this->flashMessage('Kategória úspešne aktualizovaná');

    		}

    		$this->redirect('Category:');

    	} catch (Nette\Application\BadRequestException $e) {
    		if ($e->getMessage() == "NAME_EXISTS")
    			$form->addError('Názov kategórie už existuje');
            if ($e->getMessage() == "DOESNT_EXIST")
                $form->addError('Kategória neexistuje');
            if ($e->getMessage() == "CATEGORY_LANG_DOESNT_EXIST")
                $form->addError('Kategória nemá zaindexovaný preklad');
    	}
    }

    public function getCategoryLang($categoryId)
    {
        return $this->categories->getCategoryLang($categoryId, parent::getLanguage()->id);
    }

	public function actionRemove($categoryId)
	{
		$this->categories->remove($categoryId);

		$this->flashMessage('Kategória bola úspešne vymazaná');
		$this->redirect("Category:");
	}

	public function actionEdit($categoryId, $categoryLangId)
	{
		$category = $this->categories->getCategory($categoryId);

		$this->template->categoryId = $categoryId;

		$this['categoryForm']->setDefaults($category->toArray());

        $lang = parent::getLanguage();

        $categoryLang = $this->categories->getCategoryLang($categoryId, $lang->id);
        $this['categoryForm']['name_' . $lang->iso_code]->setDefaultValue($categoryLang->name);

        $this['categoryForm']->setDefaults($category->toArray());

        if($category->parent_id != 0)
        {
            $parent = $this->categories->getCategory($category->parent_id);
            $this['categoryForm']['parent']->setDefaultValue($parent);
        }
	}

}