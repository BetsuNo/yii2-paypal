<?php namespace betsuno\paypal\api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;

/**
 * Class Subscription
 * @package betsuno\paypal\api
 * @property string $id
 * @property string $plan_id
 * @property string $start_time
 * @property string $quantity
 * @property Money $shipping_amount
 * @property Subscriber $subscriber
 * @property bool $auto_renewal
 * @property string $application_context
 * @property string $status
 * @property string $status_change_note
 * @property string $status_update_time
 * @property string $billing_info
 * @property string $create_time
 * @property string $update_time
 */
class Subscription extends PayPalResourceModel
{
	const STATUS_APPROVAL_PENDING = 'APPROVAL_PENDING';
	const STATUS_APPROVED = 'APPROVED';
	const STATUS_ACTIVE = 'ACTIVE';
	const STATUS_SUSPENDED = 'SUSPENDED';
	const STATUS_CANCELLED = 'CANCELLED';
	const STATUS_EXPIRED = 'EXPIRED';

	/**
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 * @return Subscription
	 */
	public function create($apiContext = null, $restCall = null)
	{
		$json = self::executeCall(
			'/v1/billing/subscriptions',
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
	 * @param string $planId
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 * @return Subscription
	 */
	public static function get($planId, $apiContext = null, $restCall = null)
	{
		ArgumentValidator::validate($planId, 'productId');
		$payLoad = '';
		$json = self::executeCall(
			"/v1/billing/subscriptions/$planId",
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
	 * @param string $reason
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 */
	public function activate($reason, $apiContext = null, $restCall = null)
	{
		self::executeCall(
			"/v1/billing/subscriptions/{$this->id}/activate",
			'POST',
			json_encode(['reason' => $reason]),
			null,
			$apiContext,
			$restCall
		);
	}

	/**
	 * @param string $reason
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 */
	public function cancel($reason, $apiContext = null, $restCall = null)
	{
		self::executeCall(
			"/v1/billing/subscriptions/{$this->id}/cancel",
			'POST',
			json_encode(['reason' => $reason]),
			null,
			$apiContext,
			$restCall
		);
	}

	/**
	 * @param string $reason
	 * @param ApiContext|null $apiContext
	 * @param PayPalRestCall|null $restCall
	 */
	public function suspend($reason, $apiContext = null, $restCall = null)
	{
		self::executeCall(
			"/v1/billing/subscriptions/{$this->id}/suspend",
			'POST',
			json_encode(['reason' => $reason]),
			null,
			$apiContext,
			$restCall
		);
	}


}