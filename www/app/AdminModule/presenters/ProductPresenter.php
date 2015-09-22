<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form as Form;

/**
 * Product presenter
 */
class ProductPresenter extends \App\AdminModule\Presenters\BasePresenter
{
	/** @var \App\Model\UserManager @inject */
	public $userManager;

	private $products;

	private $values;

	private $id;
	
	public function __construct(Model\ProductManager $products)
	{
		$this->products = $products;
		$this->values = array("name" => "", "short_desc" => "", "desc" => "", "status" => "", "order" => "", "price_sell" => "");
		$this->id = 0;
	}

	public function renderDefault()
	{
		$this->template->products = $this->products->getAll();
	}

	/*Product form*/
	public function createComponentProductForm()
	{
		$form = new Form;

		foreach(parent::getAllLanguages() as $lang)
        {
            $form->addText("name_". $lang->iso_code, "Názov" . "(" . $lang->iso_code . ")")
    			 ->getControlPrototype()->class("form-control");

            if($lang->id == parent::getLanguage()->id)
            {
                $form["name_". $lang->iso_code]->setRequired('Názov je povinný');
            }
        }

		$form->addTextArea("short_desc", "Krátky popis")
			 ->setRequired('Krátky popis je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addTextArea("desc", "Popis")
			 ->setRequired('Popis je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addText("price_sell", "Cena")
			 ->setType('number')
			 ->setRequired('Cena je povinná')
			 ->addRule(Form::FLOAT, "Kurz musí byť číslo")
			 ->getControlPrototype()->class("form-control");

		$form->addCheckbox("status", "");

		$form->addSubmit("add", "Pridať produkt")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "productFormSucceeded");

		return $form;
	}

	public function productFormSucceeded($form, $values)
	{
		$adding = true;

		if( $form['status']->getValue() == 'checked')
		{
			$values->status = 1;
		}
		
		try {
			if ( isset($this->request->getParameters()['productId']) )
			{
				$productId = $this->getParameter('productId');
				$adding = false;
			}

			if ($adding)
			{
				//ADD PRODUCT
				$this->products->insert($values);

				//ADD LANGUAGE DATA
				$lastId = $this->products->getLastInsertedId();

				foreach(parent::getAllLanguages() as $lang) {
                    if($values['name_' . $lang->iso_code] == NULL && $lang->iso_code != parent::getLanguage()->iso_code)
                    {
                        $values['name_' . $lang->iso_code] = 'product_' . $lastId . '_' . 'lang_' . $lang->iso_code;
                    }
                    $this->products->translateData($lang->id, $lastId, $values['name_' . $lang->iso_code], 0);
                }

				$this->flashMessage('Produkt úspešne pridaný');
			}
			else
			{
				$this->products->edit($productId, $values);
				$this->flashMessage('Produkt bol aktualizovaný');
			}

				$this->redirect("Product:");
		} catch (Nette\Application\BadRequestException $e) {
			if ($e->getMessage() == "NAME_EXISTS")
				$form->addError('Produkt neexistuje');
		}
	}

	public function actionRemove($productId)
	{
			$this->products->remove($productId);
			$this->flashMessage('Produkt bol úspešne vymazaný');
			$this->redirect("Product:");
	}

	public function actionEdit($productId)
	{
		$product = $this->products->getProduct($productId);

		$this->template->productId = $productId;

		$this['productForm']->setDefaults($product->toArray());

	}
}