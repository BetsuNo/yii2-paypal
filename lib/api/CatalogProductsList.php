<?php namespace betsuno\paypal\api;

use PayPal\Common\PayPalModel;

/**
 * Class CatalogProductsList
 * @package betsuno\paypal\api
 * @property \betsuno\paypal\api\CatalogProduct[] $products
 * @property int count
 * @property string next_id
 */
class CatalogProductsList extends PayPalModel
{
	/**
	 * A list of Dispute resources
	 *
	 * @param \betsuno\paypal\api\CatalogProduct[] $disputes
	 *
	 * @return $this
	 */
	public function setProducts($disputes)
	{
		$this->products = $disputes;
		return $this;
	}

	/**
	 * A list of Dispute resources
	 *
	 * @return \betsuno\paypal\api\CatalogProduct[]
	 */
	public function getProducts()
	{
		return $this->products;
	}

	/**
	 * Append Disputes to the list.
	 *
	 * @param \betsuno\paypal\api\CatalogProduct $dispute
	 * @return $this
	 */
	public function addItem($dispute)
	{
		if (!$this->getProducts()) {
			return $this->setProducts(array($dispute));
		} else {
			return $this->setProducts(
				array_merge($this->getProducts(), array($dispute))
			);
		}
	}

	/**
	 * Remove Disputes from the list.
	 *
	 * @param \betsuno\paypal\api\CatalogProduct $dispute
	 * @return $this
	 */
	public function removeItem($dispute)
	{
		return $this->setProducts(
			array_diff($this->getProducts(), array($dispute))
		);
	}

	/**
	 * Number of items returned in each range of results. Note that the last results range could have fewer items than the requested number of items. Maximum value: 20.
	 *
	 * @param int $count
	 *
	 * @return $this
	 */
	public function setCount($count)
	{
		$this->count = $count;
		return $this;
	}

	/**
	 * Number of items returned in each range of results. Note that the last results range could have fewer items than the requested number of items. Maximum value: 20.
	 *
	 * @return int
	 */
	public function getCount()
	{
		return $this->count;
	}

	/**
	 * Identifier of the next element to get the next range of results.
	 *
	 * @param string $next_id
	 *
	 * @return $this
	 */
	public function setNextId($next_id)
	{
		$this->next_id = $next_id;
		return $this;
	}

	/**
	 * Identifier of the next element to get the next range of results.
	 *
	 * @return string
	 */
	public function getNextId()
	{
		return $this->next_id;
	}
}