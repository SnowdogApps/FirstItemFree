<?php

/**
  Snowdog_FirstItemFree_Model_Observer

 */
class Snowdog_FirstItemFree_Model_Observer {
	const FIRST_ITEM_FREE_ACTION = 'snow_first_item_free';

	private $appliedCounter = false;

	public function salesruleValidatorProcess(Varien_Event_Observer $observer) {
		$event = $observer->getEvent();
		$rule = $event->getRule();
		$item = $event->getItem();
		$address = $event->getAddress();
		$quote = $event->getQuote();
		/* @var $quote Mage_Sales_Model_Quote */
		$qty = $event->getQty();
		$result = $event->getResult();

		if($rule->getSimpleAction() != self::FIRST_ITEM_FREE_ACTION)
			return;

		if ($this->appliedCounter === false) {
			$this->appliedCounter = $rule->getDiscountQty();
		}

		$base = 0;
		$total = 0;
		$scale = (double)$rule->getDiscountAmount()/100.0;

		if($scale > 1) $scale = 1;
		if($scale < 0) $scale = 0;

		if ($this->appliedCounter <= 0)
			return;
		if ($item->getQty() > $this->appliedCounter) {
			$base += $item->getBasePrice() * $this->appliedCounter * $scale;
			$total += $item->getPriceInclTax() * $this->appliedCounter * $scale;
			$this->appliedCounter = 0;
		} else {
			$base += $item->getBasePrice() * $item->getQty() * $scale;
			$total += $item->getPriceInclTax() * $item->getQty() * $scale;
			$this->appliedCounter -= $item->getQty();
		}

		$result->setBaseDiscountAmount($base);
		$result->setDiscountAmount($total);
	}

	public function adminhtmlBlockSalesruleActionsPrepareform(Varien_Event_Observer $observer) {
		$event = $observer->getEvent();

		$form = $event->getForm();
		/* @var $form Varien_Data_Form */
		$actionField = $form->getElement('simple_action');
		/* @var $actionField Varien_Data_Form_Element_Select */
		$actions = $actionField->getValues();
		$actions[] = array(
			'label' => Mage::helper("snowfirstitemfree")->__("First n items discounted by percent"),
			'value' => self::FIRST_ITEM_FREE_ACTION,
		);
		$actionField->setValues($actions);
	}

}
