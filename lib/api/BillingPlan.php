<?php namespace betsuno\paypal\api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;

/**
 * Class BillingPlan
 * @package betsuno\paypal\api
 * @property string $id
 * @property string $product_id
 * @property string $name
 * @property string $status
 * @property string $description
 * @property \betsuno\paypal\api\BillingCycle[] $billing_cycles
 * @property \betsuno\paypal\api\PaymentPreferences $payment_preferences
 * @property \betsuno\paypal\api\Taxes $taxes
 * @property bool $quantity_supported
 * @property string $create_time
 * @property string $update_time
 */
class BillingPlan extends PayPalResourceModel
{
	const STATUS_CREATED  = 'CREATED';
	const STATUS_INACTIVE = 'INACTIVE';
	const STATUS_ACTIVE   = 'ACTIVE';

	/**
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 * @return BillingPlan
	 */
	public function create($apiContext = null, $restCall = null)
	{
		$json = self::executeCall(
			'/v1/billing/plans',
			'POST',
			$this->toJSON(),
			null,
			$apiContext,
			$restCall
		);
		$ret = new self();
		$ret->fromJson($json);
		return $ret;
	}

	/**
	 * @param array $params
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 * @return BillingPlansList
	 */
	public static function all($params, $apiContext = null, $restCall = null)
	{
		ArgumentValidator::validate($params, 'params');
		$payLoad = '';
		$allowedParams = [
			'page_size'               => 1,
			'page'                    => 1,
			'total_required'          => true,
		];

		$json = self::executeCall(
			'/v1/catalogs/products?' . http_build_query(array_intersect_key($params, $allowedParams)),
			'GET',
			$payLoad,
			null,
			$apiContext,
			$restCall
		);

		$ret = new BillingPlansList();
		$ret->fromJson($json);

		return $ret;
	}

	/**
	 * @param string $planId
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 * @return BillingPlan
	 */
	public static function get($planId, $apiContext = null, $restCall = null)
	{
		ArgumentValidator::validate($planId, 'productId');
		$payLoad = '';
		$json = self::executeCall(
			"/v1/billing/plans/$planId",
			'GET',
			$payLoad,
			null,
			$apiContext,
			$restCall
		);
		$ret = new self();
		$ret->fromJson($json);
		return $ret;
	}

	/**
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 */
	public function activate($apiContext = null, $restCall = null)
	{
		self::executeCall(
			"/v1/billing/plans/{$this->id}/activate",
			'POST',
			'',
			null,
			$apiContext,
			$restCall
		);
	}

	/**
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 */
	public function deactivate($apiContext = null, $restCall = null)
	{
		self::executeCall(
			"/v1/billing/plans/{$this->id}/deactivate",
			'POST',
			'',
			null,
			$apiContext,
			$restCall
		);
	}

	/**
	 * @param PricingScheme[] $schemes
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 */
	public function updatePricingSchemes($schemes, $apiContext = null, $restCall = null)
	{
		self::executeCall(
			"/v1/billing/plans/{$this->id}/update-pricing-schemes",
			'POST',
			json_encode($schemes),
			null,
			$apiContext,
			$restCall
		);
	}


}