<?php namespace betsuno\paypal\api;

use PayPal\Common\PayPalModel;

/**
 * Class BillingInfo
 * @package betsuno\paypal\api
 * @property Money $outstanding_balance
 * @property CycleExecution[] $cycle_executions
 * @property LastPayment $last_payment
 * @property string $next_billing_time
 * @property string $final_payment_time
 * @property int $failed_payments_count
 */
class BillingInfo extends PayPalModel
{

}