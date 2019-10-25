<?php namespace betsuno\paypal\api;

use PayPal\Api\Links;
use PayPal\Common\PayPalModel;

/**
 * Class BillingPlansList
 * @package betsuno\paypal\api
 * @property \betsuno\paypal\api\BillingPlan[] $plans
 * @property int $total_items
 * @property int $total_pages
 * @property Links $links
 */
class BillingPlansList extends PayPalModel
{
	/**
	 * A list of Dispute resources
	 *
	 * @param \betsuno\paypal\api\BillingPlan[] $disputes
	 *
	 * @return $this
	 */
	public function setPlans($disputes)
	{
		$this->plans = $disputes;
		return $this;
	}

	/**
	 * A list of Dispute resources
	 *
	 * @return \betsuno\paypal\api\BillingPlan[]
	 */
	public function getPlans()
	{
		return $this->plans;
	}

	/**
	 * Append Disputes to the list.
	 *
	 * @param \betsuno\paypal\api\BillingPlan $dispute
	 * @return $this
	 */
	public function addItem($dispute)
	{
		if (!$this->getPlans()) {
			return $this->setPlans(array($dispute));
		} else {
			return $this->setPlans(
				array_merge($this->getPlans(), array($dispute))
			);
		}
	}

	/**
	 * Remove Disputes from the list.
	 *
	 * @param \betsuno\paypal\api\BillingPlan $dispute
	 * @return $this
	 */
	public function removeItem($dispute)
	{
		return $this->setPlans(
			array_diff($this->getPlans(), array($dispute))
		);
	}

	/**
	 * Number of items returned in each range of results. Note that the last results range could have fewer items than the requested number of items. Maximum value: 20.
	 *
	 * @param int $count
	 *
	 * @return $this
	 */
	public function setTotalItems($count)
	{
		$this->total_items = $count;
		return $this;
	}

	/**
	 * Number of items returned in each range of results. Note that the last results range could have fewer items than the requested number of items. Maximum value: 20.
	 *
	 * @return int
	 */
	public function getTotalItems()
	{
		return $this->total_items;
	}

	/**
	 * Identifier of the next element to get the next range of results.
	 *
	 * @param int $total_pages
	 *
	 * @return $this
	 */
	public function setTotalPages($total_pages)
	{
		$this->total_pages = $total_pages;
		return $this;
	}

	/**
	 * Identifier of the next element to get the next range of results.
	 *
	 * @return int
	 */
	public function getTotalPages()
	{
		return $this->total_pages;
	}
}