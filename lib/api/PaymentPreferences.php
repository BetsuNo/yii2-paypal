<?php namespace betsuno\paypal\api;

use PayPal\Common\PPModel;

/**
 * Class PaymentPreferences
 * @package betsuno\paypal\api
 * @property bool $auto_bill_outstanding
 * @property Money $setup_fee
 * @property string $setup_fee_failure_action
 * @property int $payment_failure_threshold
 *
 */
class PaymentPreferences extends PPModel
{

}