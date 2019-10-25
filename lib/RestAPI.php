<?php /** @noinspection PhpUnused */

namespace betsuno\paypal;

use betsuno\paypal\api\BillingPlan;
use betsuno\paypal\api\CatalogProduct;
use betsuno\paypal\api\PricingScheme;
use betsuno\paypal\api\Subscription;
use Exception;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use yii\base\Component;

/**
 * Class RestAPI
 * @package betsuno\paypal
 * @property ApiContext $config
 */
class RestAPI extends Component
{
	public $_apiContext;
	public $_credentials;

	public $successUrl = '';
	public $cancelUrl = '';

	public $pathFileConfig;

	/**
	 * @param  $config
	 * @return mixed
	 * @throws Exception
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		//set config default for paypal
		if (!$this->pathFileConfig) {
			$this->pathFileConfig = __DIR__ . '/config-rest.php';
		}

		// check file config already exist.
		if (!file_exists($this->pathFileConfig)) {
			throw new Exception('File config does not exist.', 500);
		}

		//set config file
		$this->_credentials = require($this->pathFileConfig);

		if (!in_array($this->_credentials['config']['mode'], ['sandbox', 'live'])) {
			throw new Exception('Error Processing Request', 503);
		}

		return $this->_credentials;
	}

	/**
	 * Get api context
	 *
	 * @return mixed
	 */
	public function getConfig()
	{
		if (!$this->_apiContext) {
			$this->setConfig();
		}

		return $this->_apiContext;
	}

	/**
	 * @return ApiContext
	 */
	private function setConfig()
	{
		// ### Api context
		// Use an ApiContext object to authenticate
		// API calls. The clientId and clientSecret for the
		// OAuthTokenCredential class can be retrieved from
		// developer.paypal.com
		$this->_apiContext = (new ApiContext(new OAuthTokenCredential(
				$this->_credentials['client_id'],
				$this->_credentials['secret'])
		));

		$this->_apiContext->setConfig($this->_credentials['config']);

		return $this->_apiContext;
	}

	/**
	 * @return string
	 */
	private function getBaseUrl()
	{
		if (PHP_SAPI == 'cli') {
			$trace=debug_backtrace();
			$relativePath = substr(dirname($trace[0]['file']), strlen(dirname(dirname(__FILE__))));
			echo "Warning: This sample may require a server to handle return URL. Cannot execute in command line. Defaulting URL to http://localhost$relativePath \n";
			return 'http://localhost' . $relativePath;
		}
		$protocol = 'http';
		if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
			$protocol .= 's';
		}
		$host = $_SERVER['HTTP_HOST'];
		$request = $_SERVER['PHP_SELF'];
		return dirname($protocol . '://' . $host . $request);
	}

	/**
	 * @param array|null $params
	 * @return array|bool
	 * @throws Exception
	 */
	public function getLinkCheckOut($params = null)
	{
		if (!$params) {
			return false;
		}

		 /*Payer
		 A resource representing a Payer that funds a payment
		 For paypal account payments, set payment method
		 to 'paypal'.
		 */
		$payer = new Payer();
		$payer->setPaymentMethod('paypal');

		$itemList = new ItemList();
		// Item must be a array and has one or more item.
		if (!$params['items']) {
			return false;
		}
		$arrItem = [];
		foreach ($params['items'] as $key => $item) {
			$it = new Item();
			$it->setName($item['name'])
				->setCurrency($params['currency'])
				->setQuantity($item['quantity'])
				->setPrice($item['price']);
			$arrItem[] = $it;
		}
		$itemList->setItems($arrItem);

		$amount = new Amount();
		$amount->setCurrency($params['currency'])
			   ->setTotal($params['total_price']);

		$transaction = new Transaction();
		$transaction->setAmount($amount)
					->setItemList($itemList)
					->setDescription($params['description']);

		// ### Redirect urls
		// Set the urls that the buyer must be redirected to after
		// payment approval/ cancellation.
		$redirectUrls = new RedirectUrls();
		$baseUrl = $this->getBaseUrl();
		$redirectUrls->setReturnUrl($baseUrl . $this->successUrl)
					 ->setCancelUrl($baseUrl . $this->cancelUrl);
		// ### Payment
		// A Payment Resource; create one using
		// the above types and intent set to 'sale'
		$payment = new Payment();
		$payment->setIntent('sale')
				->setPayer($payer)
				->setRedirectUrls($redirectUrls)
				->setTransactions([$transaction]);

		// ### Create Payment
		// Create a payment by calling the 'create' method
		// passing it a valid apiContext.
		try {
			$payment->create($this->config);
		} catch (PayPalConnectionException $ex) {
			throw new Exception($ex->getData(), $ex->getMessage());
		}
		// ### Get redirect url
		$redirectUrl = $payment->getApprovalLink();

		return [
			'payment_id' => $payment->getId(),
			'status' => $payment->getState(),
			'redirect_url' => $redirectUrl,
			'description' => $transaction->getDescription(),
		];
	}

	/**
	 * @param string $paymentId
	 * @return array
	 */
	public function getResult($paymentId) {

		$payment = Payment::get($paymentId, $this->config);

		$execution = new PaymentExecution();
		$execution->setPayerId($_GET['PayerID']);
		$payment = $payment->execute($execution, $this->config);

		$result = @$payment->toArray();
		return $result;
	}

	/**
	 * @param null $params
	 * @return bool
	 */
	public function checkPayment($params = null){
		if (!$params) {
			return false;
		}
		$this->setConfig();
		$payment = Payment::get($params['payment_id'], $this->config);
		if (empty($payment->transactions)){
			return false;
		}
		$summ = 0;
		foreach ($payment->transactions as $transaction) {
			$summ += floatval($transaction->amount->getTotal());
		}

		return $summ == $params['price'];
	}

	/**
	 * @param array|null $params
	 * @return bool|Payment
	 */
	public function executePayment($params = null){

		if (!$params) {
			return false;
		}

		$payment = Payment::get($params['payment_id'], $this->config);

		$execution = new PaymentExecution();
		$execution->setPayerId($params['payer_id']);
		return $payment->execute($execution, $this->config);
	}

	/**
	 * @param CatalogProduct $product
	 * @return CatalogProduct
	 */
	public function createProduct($product)
	{
		return $product->create($this->config);
	}

	/**
	 * @param string $id
	 * @return CatalogProduct
	 */
	public function getProduct($id)
	{
		return CatalogProduct::get($id, $this->config);
	}

	/**
	 * @param array $params
	 * @return api\CatalogProductsList
	 */
	public function listProducts($params)
	{
		return CatalogProduct::all($params, $this->config);
	}

	/**
	 * @param BillingPlan $plan
	 * @return BillingPlan
	 */
	public function createBillingPlan($plan)
	{
		return $plan->create($this->config);
	}

	/**
	 * @param string $id
	 * @return BillingPlan
	 */
	public function getBillingPlan($id)
	{
		return BillingPlan::get($id, $this->config);
	}

	/**
	 * @param array $params
	 * @return api\BillingPlansList
	 */
	public function listBillingPlans($params)
	{
		return BillingPlan::all($params, $this->config);
	}

	/**
	 * @param BillingPlan $plan
	 */
	public function activateBillingPlan($plan)
	{
		$plan->activate($this->config);
	}

	/**
	 * @param BillingPlan $plan
	 */
	public function deactivateBillingPlan($plan)
	{
		$plan->deactivate($this->config);
	}

	/**
	 * @param BillingPlan $plan
	 * @param PricingScheme[] $schemes
	 */
	public function updateBillingPlanPricingSchemes($plan, $schemes)
	{
		$plan->updatePricingSchemes($schemes, $this->config);
	}

	/**
	 * @param Subscription $subscription
	 * @return Subscription
	 */
	public function createSubscription($subscription)
	{
		return $subscription->create($this->config);
	}

	/**
	 * @param string $id
	 * @return Subscription
	 */
	public function getSubscription($id)
	{
		return Subscription::get($id, $this->config);
	}

	/**
	 * @param Subscription $subscription
	 * @param string $reason
	 */
	public function activateSubscription($subscription, $reason)
	{
		$subscription->activate($reason, $this->config);
	}

	/**
	 * @param Subscription $subscription
	 * @param string $reason
	 */
	public function cancelSubscription($subscription, $reason)
	{
		$subscription->cancel($reason, $this->config);
	}

	/**
	 * @param Subscription $subscription
	 * @param string $reason
	 */
	public function suspendSubscription($subscription, $reason)
	{
		$subscription->suspend($reason, $this->config);
	}
}
