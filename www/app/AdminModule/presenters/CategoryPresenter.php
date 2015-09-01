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

	private $catDepth;

	private $values;

	private $id;

	private $state;

	public function __construct(Model\CategoryManager $categories)
	{
		$this->categories = $categories;
		$this->values = array("name" => "", "parent" => "", "parent_id" => "", "icon" => "");
		$this->id = 0;
		$this->state = "";
	}

	public function renderDefault()
	{
		$this->template->categories = $this->categories->getAll()->order('name');
	}

	protected function createComponentCategoryForm()
    {
        $form = new Form;
        
        $form->addText("name", "Meno")
			 ->setRequired('Meno je povinné')
			 ->getControlPrototype()->class("form-control");
        
        $form->addText('icon', "Icon Class *")
        		->setAttribute('class', 'form-control')
        		->setAttribute('placeholder', 'Napíš class ikonky')
        		->setAttribute('value', $this->values['icon'])
        		->setRequired('Ikonka je povinná');

			foreach ($this->categories->getAll(FALSE)->order('name') as $category) 
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
	    	
        	$form->addSubmit('addItem', 'Pridať kategóriu')
        			->setAttribute('class', 'btn btn-primary');
        	
        	$form->addSubmit('editItem', 'Uložiť zmeny')
        			->setAttribute('class', 'btn btn-primary');

        	$form->onSuccess[] = array($this, 'CategoryFormSucceeded');

        return $form;
    }

    public function CategoryFormSucceeded($form, $values)
    {
    	$adding = true;

    	if ( isset($this->request->getParameters()['categoryId']) )
    	{
    		$categoryId = $this->getParameter('categoryId');
    		$adding = false;
    	}

    	try {
    		if ($adding)
    		{
    			// ADD CATEGORY
    			$parent = $values['parent']? $values['parent'] : NULL;
    			$depth   = $parent? ( $this->catDepth[$parent] + 1 ) : 0;
    			$this->categories->insert($values['name'], $parent, $values["icon"], $depth);
    			$this->flashMessage('Kategória úspešne pridaná');
    		}
    		else
    		{
		    	// EDIT CATEGORY
    			$values['depth']   = $values['parent']? ( $this->catDepth[$values['parent']] + 1 ) : 0;
    			$this->categories->edit($categoryId, $values);
    			$this->flashMessage('Kategória úspešne aktualizovaná');

    		}

    		$this->redirect('Category:');

    	} catch (Nette\Application\BadRequestException $e) {
    		if ($e->getMessage() == "NAME_EXISTS")
    			$form->addError('Názov kategórie už existuje');
    	}
    }

	public function actionRemove($categoryId)
	{
		$this->categories->remove($categoryId);

		$this->flashMessage('Kategória bola úspešne vymazaná');
		$this->redirect("Category:");
	}

	public function actionEdit($categoryId)
	{
		$category = $this->categories->getCategory($categoryId);

		$this->template->categoryId = $categoryId;

		$this['categoryForm']->setDefaults($category->toArray());

	}

}