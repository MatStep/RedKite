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

		$form->addText("name", "Názov")
			 ->setRequired('Názov je povinný')
			 ->getControlPrototype()->class("form-control");

		$form->addText("value", "Hodnota")
			 ->setRequired('Hodnota je povinná')
			 ->getControlPrototype()->class("form-control");

		$form->addSubmit("add", "Pridať daň")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->addSubmit("edit", "Uložiť zmeny")
			 ->getControlPrototype()->class("btn btn-primary pull-right");

		$form->onSuccess[] = array($this, "productFormSucceeded");

		return $form;
	}

	public function productFormSucceeded($form, $values)
	{
		$adding = true;
		
		try {
			if ( isset($this->request->getParameters()['productId']) )
			{
				$productId = $this->getParameter('productId');
				$adding = false;
			}

			if ($adding)
			{
				$this->products->insert($values);
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
			$this->flashMessage('Daň bola úspešne vymazaná');
			$this->redirect("Product:");
	}

	public function actionEdit($productId)
	{
		$product = $this->products->getProduct($productId);

		$this->template->productId = $productId;

		$this['productForm']->setDefaults($product->toArray());

	}
}