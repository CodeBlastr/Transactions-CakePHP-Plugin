<?php
App::uses('ModelBehavior', 'Model');

/**
 * Buyable Behavior class file.
 *
 * The ability to exchange real money by interfacing with a payment processor
 * 
 * Usage is :
 * Attach behavior to a model, and when you save 
 *
 * @filesource
 * @author			Richard Kersey
 * @copyright       Buildrr LLC
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link            https://github.com/zuha/Transactions-Zuha-Cakephp-Plugin
 */
class BuyableBehavior extends ModelBehavior {

/**
 * Behavior settings
 * 
 * @access public
 * @var array
 */
	public $settings = array();

/**
 * The full results of Model::find() that are modified and saved
 * as a new copy.
 *
 * @access public
 * @var array
 */
	public $record = array();

/**
 * Default values for settings.
 *
 * - recursive: whether to copy hasMany and hasOne records
 * - habtm: whether to copy hasAndBelongsToMany associations
 * - stripFields: fields to strip during copy process
 * - ignore: aliases of any associations that should be ignored, using dot (.) notation.
 * will look in the $this->contain array.
 *
 * @access private
 * @var array
 */
    protected $defaults = array(
    	'buyModel' => ''
		);

/**
 * Status List
 * Processors should use this list in order map processor response types to our own
 * internal processor response types.  So that every processor's response can
 * be matched to our internal response types
 * 
 * @access public
 * @var array
 */
	public $statusTypes = array(
		'paid' => 'paid',
		'open' => 'open',
		'pending' => 'pending',
		'used' => 'used'
		);

/**
 * Response
 * 
 * @access public
 * @var array
 */
	public $response = array();
	//public $paymentComponent = null;  // from the component
	//public $Controller = null;  // from the component

/**
 * Recurring
 * 
 * @access public
 * @var array
 */
	public $recurring = false;


/**
 * Configuration method.
 *
 * @param object $Model Model object
 * @param array $config Config array
 * @access public
 * @return boolean
 */
    public function setup(Model $Model, $config = array()) {
    	$this->settings = array_merge($this->defaults, $config);
		$this->modelName = !empty($this->settings['modelAlias']) ? $this->settings['modelAlias'] : $Model->alias;
		$this->foreignKey =  !empty($this->settings['foreignKeyName']) ? $this->settings['foreignKeyName'] : $Model->primaryKey;
    	return true;
	}

/**
 * Set recurring value default is false
 */
	public function recurring($val = false) {
		$this->recurring = $val;
	}

/**
 * Load Processor (should be datasource right?)
 */
	public function loadProcessor($processor = array()) {
        if (!empty($processor)) {

            if(strcasecmp('braintree',$processor) == 0){
                $processor .= 'Payment';
            }
            App::uses($processor, 'Transactions.Model/Processor');
			$this->Processor = new $processor;
			$this->Processor->modelName = !empty($this->settings['buyModel']) ? $this->settings['buyModel'] : 'Transaction';
			$this->Processor->statusTypes = $this->statusTypes;
        } else {
            throw new NotFoundException('Site payment settings have not been configured.');
        }
	}


/**
 * The Buy Function.
 * This function takes $data to process a payment with the merchant account defined by Transaction.mode
 * If there are any problems processing the payment, an exception should be raised with it's details for the user.
 * If the payment is successful, the $data array, with any necessary additions, should be returned.
 * 
 * @param array $data
 * @return array
 * @throws Exception
 */
	public function buy(Model $Model, $data = null) {
		if (!defined('__TRANSACTIONS_DEFAULT_PAYMENT')) {
        	throw new Exception('Payment configuration required', 1);
        }	
		try {
			$paymentProcessor = ucfirst(strtolower($data['Transaction']['mode']));
			$paymentProcessor = explode('.', $paymentProcessor);
			$paymentProcessor = $paymentProcessor[0];
			//debug($data['Transaction']['mode']);exit;
			$this->loadProcessor($paymentProcessor);
			
			App::uses('Transaction' , 'Transactions.Model');
			$Transaction = new Transaction;
			
			$data = $Transaction->beforePayment($data);
			if (method_exists($Model, 'beforePayment') && is_callable('beforePayment')) {
				$data = $Model->beforePayment($data);
			}
			
			if ($this->recurring) {
				$this->Processor->recurring = true;	
			}
			$data = $this->Processor->pay($data);
			
			$Transaction->afterSuccessfulPayment(CakeSession::read('Auth.User.id'), $data);
			if (method_exists($Model, 'afterSuccessfulPayment') && is_callable('afterSuccessfulPayment')) {
				$Model->afterSuccessfulPayment(CakeSession::read('Auth.User.id'), $data);
			}
			
            return $data;
			
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

/**
 * function changeSubscription() use to change the recurring profile status
 * profileId: id of recurrence, can be profile id of payer
 * action: suspended or cancelled
 */
 	// was ManageRecurringPaymentsProfileStatus
	public function editPaymenProfile($data = null) {
		debug($data);
		exit;
		
		$this->loadComponent(ucfirst(strtolower($data['Mode'])));
		$this->paymentComponent->ManageRecurringPaymentsProfileStatus($data['profileId'], $data['action']);

		return $this->paymentComponent->response;
	}
	
	
}