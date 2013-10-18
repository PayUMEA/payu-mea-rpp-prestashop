<?php 
class payU extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();
	var $conirmationUrl = '';
	public function __construct()
	{
		$this->name = 'payU';
		$this->tab = 'payments_gateways';
		//$this->tab = 'Payment';
		$this->version = '1.01';
		
		$this->currencies = true;
		$this->currencies_mode = 'radio';

        parent::__construct();

		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('payU');
        $this->description = $this->l('Accepts payments by payU');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}

	public function getpayUUrl($process)
	{
			 
			return Configuration::get('PAYU_SANDBOX') ? 'https://staging.payu.co.za/rpp.do?PayUReference='.$payUrefrence : 'https://secure.payu.co.za/rpp.do?PayUReference='.$process;
	}

	
	public function install(){
		if (!parent::install()
			OR !Configuration::updateValue('PAYU_SAFE_KEY', '{07F70723-1B96-4B97-B891-7BF708594EEA}')
			OR !Configuration::updateValue('PAYU_SOAP_USERNAME', 'Staging Integration Store 3')
			OR !Configuration::updateValue('PAYU_SOAP_PASSWORD', 'WSAUFbw6')			
			OR !Configuration::updateValue('PAYU_PAYMENT_METHOD', 'CREDITCARD')
			OR !Configuration::updateValue('PAYU_WHERE_TO_PAY', '')
			OR !Configuration::updateValue('PAYU_INVOICE', '')
			OR !Configuration::updateValue('PAYU_BILLING_CURRENCY', '')
			//OR !Configuration::updateValue('PAYU_CANCEL_URL', '')						
			OR !Configuration::updateValue('PAYU_SANDBOX', 1)
			OR !Configuration::updateValue('PAYU_TRANSACTION_TYPE', 'RESERVE') 
			OR !$this->registerHook('payment')
			OR !$this->registerHook('paymentReturn')
			OR !$this->registerHook('paymentConfirm')
			OR !$this->registerHook('leftColumn')
			OR !$this->registerHook('cancelProduct')		 
			OR !Configuration::updateValue('CONFIRMATIONURL', 1))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('PAYU_SAFE_KEY')
			OR !Configuration::deleteByName('PAYU_SOAP_USERNAME')
			OR !Configuration::deleteByName('PAYU_SOAP_PASSWORD')
			OR !Configuration::deleteByName('PAYU_SANDBOX')
			OR !Configuration::deleteByName('CONFIRMATIONURL')
			OR !Configuration::deleteByName('PAYU_PAYMENT_METHOD')
			OR !Configuration::deleteByName('PAYU_WHERE_TO_PAY')
			OR !Configuration::deleteByName('PAYU_INVOICE')
			OR !Configuration::deleteByName('PAYU_BILLING_CURRENCY')
			OR !Configuration::deleteByName('PAYU_CANCEL_URL')
			//OR !Configuration::deleteByName('PAYU_TRANSACTION_TYPE') 
			OR !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$this->_html = '<h2>payU</h2>';
		if (isset($_POST['submitpayU']))
		{
			if (empty($_POST['safe_key']))
				$this->_postErrors[] = $this->l('payU Safe Key is required.');
			if (empty($_POST['soap_username']))
				$this->_postErrors[] = $this->l('payU SOAP Username is required.');
			if (!isset($_POST['sandbox']))
				$_POST['sandbox'] = 1;
			if (!sizeof($this->_postErrors))
			{
				 
				Configuration::updateValue('PAYU_SAFE_KEY', strval($_POST['safe_key']));
				Configuration::updateValue('PAYU_SOAP_USERNAME', strval($_POST['soap_username']));
				Configuration::updateValue('PAYU_SOAP_PASSWORD', strval($_POST['soap_password']));				 
				Configuration::updateValue('PAYU_SANDBOX', intval($_POST['sandbox']));				
				Configuration::updateValue('PAYU_WHERE_TO_PAY', strval($_POST['where_to_pay']));
				Configuration::updateValue('PAYU_PAYMENT_METHOD', strval($_POST['payment_method']));
				Configuration::updateValue('PAYU_BILLING_CURRENCY', strval($_POST['billing_Currency']));
				Configuration::updateValue('PAYU_INVOICE', strval($_POST['payU_invoice_description_prepend']));
				//Configuration::updateValue('PAYU_CANCEL_URL', strval($_POST['cancel_url']));
				Configuration::updateValue('PAYU_TRANSACTION_TYPE', strval($_POST['payU_transcation_type']));
				
				$this->displayConf();
				 
			}
			else
				$this->displayErrors();
		}

		$this->displaypayU();
		$this->displayFormSettings();
		return $this->_html;
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="alert error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}
	
	
	public function displaypayU()
	{
		$this->_html .= '
		<div style="float: right; width: 440px; height: 150px; border: dashed 1px #666; padding: 8px; margin-left: 12px;">
			<h2>'.$this->l('Open/Access your payU Account').'</h2>
			<div style="clear: both;"></div></b><br />
			<p>'.$this->l('Click on the payU Logo Below to register or edit your payU Account').'</p>
			<p style="text-align: center;"><a href="https://www.payu.co.za/signup.do"><img src="../modules/payU/payU.gif" alt="payU" style="margin-top: 12px;" /></a></p>
			<div style="clear: right;"></div>
		</div>
		<b></b><br />
		<b>'.$this->l('This module allows you to accept payments by payU.').'</b><br /><br /><br />
		'.$this->l('If the client chooses this payment mode, your payU account will be automatically credited.').'<br /><br />
		'.$this->l('You need to configure your payU account first before using this module.').'
		<div style="clear:both;">&nbsp;</div>';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('PAYU_SAFE_KEY', 'PAYU_SOAP_USERNAME', 'PAYU_SOAP_PASSWORD', 'PAYU_SANDBOX','PAYU_WHERE_TO_PAY','PAYU_PAYMENT_METHOD','PAYU_BILLING_CURRENCY','PAYU_INVOICE','PAYU_CANCEL_URL'));
		 
		$where_to_pay = array_key_exists('where_to_pay', $_POST) ? $_POST['where_to_pay'] : (array_key_exists('PAYU_WHERE_TO_PAY', $conf) ? $conf['PAYU_WHERE_TO_PAY'] : '');
		$payment_method = array_key_exists('payment_method', $_POST) ? $_POST['payment_method'] : (array_key_exists('PAYU_PAYMENT_METHOD', $conf) ? $conf['PAYU_PAYMENT_METHOD'] : '');
		$billing_Currency = array_key_exists('billing_Currency', $_POST) ? $_POST['billing_Currency'] : (array_key_exists('PAYU_BILLING_CURRENCY', $conf) ? $conf['PAYU_BILLING_CURRENCY'] : '');
		$payU_invoice_description_prepend = array_key_exists('payU_invoice_description_prepend', $_POST) ? $_POST['payU_invoice_description_prepend'] : (array_key_exists('PAYU_INVOICE', $conf) ? $conf['PAYU_INVOICE'] : '');
		//$cancel_url=array_key_exists('cancel_url', $_POST) ? $_POST['cancel_url'] : (array_key_exists('PAYU_CANCEL_URL', $conf) ? $conf['PAYU_CANCEL_URL'] : '');
		
		$payU_transcation_type = array_key_exists('payU_transcation_type', $_POST) ? $_POST['payU_transcation_type'] : (array_key_exists('PAYU_TRANSACTION_TYPE', $conf) ? $conf['PAYU_TRANSACTION_TYPE'] : '');
		
		$safe_key = array_key_exists('safe_key', $_POST) ? $_POST['safe_key'] : (array_key_exists('PAYU_SAFE_KEY', $conf) ? $conf['PAYU_SAFE_KEY'] : '');
		$soap_username = array_key_exists('soap_username', $_POST) ? $_POST['soap_username'] : (array_key_exists('PAYU_SOAP_USERNAME', $conf) ? $conf['PAYU_SOAP_USERNAME'] : '');
		$soap_password = array_key_exists('soap_password', $_POST) ? $_POST['soap_password'] : (array_key_exists('PAYU_SOAP_PASSWORD', $conf) ? $conf['PAYU_SOAP_PASSWORD'] : '');		 
		$sandbox = array_key_exists('sandbox', $_POST) ? $_POST['sandbox'] : (array_key_exists('PAYU_SANDBOX', $conf) ? $conf['PAYU_SANDBOX'] : '');
		

		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="clear: both;">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
			
			
			<label>'.$this->l('Where to Pay.').'</label>
			<div class="margin-form"><input type="text" size="40" name="where_to_pay" value="'.htmlentities($where_to_pay, ENT_COMPAT, 'UTF-8').'" /> * </div>
			
			
			<label>'.$this->l('Safe Key').'</label>
			<div class="margin-form"><input type="text" size="40" name="safe_key" value="'.htmlentities($safe_key, ENT_COMPAT, 'UTF-8').'" /> * </div>
			
			<label>'.$this->l('SOAP Username').'</label>
			<div class="margin-form"><input type="text" size="40" name="soap_username" value="'.htmlentities($soap_username, ENT_COMPAT, 'UTF-8').'" /> * </div>
			
			<label>'.$this->l('SOAP Password').'</label>
			<div class="margin-form"><input type="text" size="40" name="soap_password" value="'.htmlentities($soap_password, ENT_COMPAT, 'UTF-8').'" /> * </div>
		
		
			<label>'.$this->l('Payment Method').'</label>
			<div class="margin-form"><input type="text" size="40" name="payment_method" value="'.htmlentities($payment_method, ENT_COMPAT, 'UTF-8').'" /> * </div>
			';
			
		 if($payU_transcation_type == 'RESERVE'){	
			$this->_html .= '	
				<label>'.$this->l('Transcation Type').'</label>
				<div class="margin-form">
						<select name="payU_transcation_type">
							<option selected="selected" value="RESERVE">RESERVE</option>
							<option value="PAYMENT">PAYMENT</option>
						</select>
						
				</div>
				';
		}else{
			$this->_html .= '	
				<label>'.$this->l('Transcation Type').'</label>
				<div class="margin-form">
						<select name="payU_transcation_type">
							<option value="RESERVE">RESERVE</option>
							<option selected="selected" value="PAYMENT">PAYMENT</option>
						</select>
						
				</div>
				';
		}
			
		$this->_html .= '	
			<label>'.$this->l('Billing Currency').'</label>
			<div class="margin-form"><input type="text" size="40" name="billing_Currency" value="'.htmlentities($billing_Currency, ENT_COMPAT, 'UTF-8').'" /> * </div>
			
			<label>'.$this->l('PayU Invoice Description Prepend').'</label>
			<div class="margin-form"><input type="text" size="40" name="payU_invoice_description_prepend" value="'.htmlentities($payU_invoice_description_prepend, ENT_COMPAT, 'UTF-8').'" /> * </div>
			
			
			
		
		
			<label>'.$this->l('Transaction Server').'</label>
			<div class="margin-form">
				<input type="radio" name="sandbox" value="1" '.($sandbox ? 'checked="checked"' : '').' /> <label class="t">'.$this->l('Sandbox (Test)').'</label><br /><br />
				<input type="radio" name="sandbox" value="0" '.(!$sandbox ? 'checked="checked"' : '').' /> <label class="t">'.$this->l('Live').'</label><br /><br />
			<p class="hint clear" style="display: block; width: 501px;">'.$this->l('Select which payU Server you would like to use. Remember to change the default details to your own if you select the Live Server. ').'</p></div><br />
			<br /><center><input type="submit" name="submitpayU" value="'.$this->l('Update settings').'" class="button" /></center>
		</fieldset>
		</form><br /><br />
		';
		/*
		<label>'.$this->l('Cancel Url').'</label>
			<div class="margin-form"><input type="text" size="40" name="cancel_url" value="'.htmlentities($cancel_url, ENT_COMPAT, 'UTF-8').'" /> * </div>
		
		$this->_html .= '
		<fieldset class="width3">
			<legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
			'.$this->l('All fields with an * must be filled in for this module to work properly in both Live and Sandbox Modes. The Merchant Email field is for if you want to be sent a confirmation email after a transaction. Email confirmation is automatically sent to the customer.In order to use this module in live mode, you need to first have a payU account. You can use the sandbox without an account but only use it for testing perposes.').'<br /><br />
			<b>'.$this->l('In order to use sand box use these default values').' : </b><br /><br />
			- <b>'.$this->l('Safe Key').'</b> : '.$this->l('10000100').'<br />
			- <b>'.$this->l('SOAP UserName').'</b> : '.$this->l('46f0cd694581a').'<br />
			- <b>'.$this->l('SOAP Password').'</b> : '.$this->l('0a1e2e10-03a7-4928-af8a-fbdfdfe31d43').'<br />
			<br /><br />
			<b>'.$this->l('Use the following customer login credentials').' : </b><br /><br /> 
			- <b>'.$this->l('Username').'</b> : '.$this->l('sbtu01@payU.co.za').'<br />
			- <b>'.$this->l('Password').'</b> : '.$this->l('clientpass').'<br />
		</fieldset>';
		*/
		
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return ;
		 
		global $smarty;

		$address = new Address(intval($params['cart']->id_address_invoice));
		$customer = new Customer(intval($params['cart']->id_customer));
		$safe_key = Configuration::get('PAYU_SAFE_KEY');
		$soap_username = Configuration::get('PAYU_SOAP_USERNAME');
		$soap_password = Configuration::get('PAYU_SOAP_PASSWORD');
		
		$PAYU_WHERE_TO_PAY = Configuration::get('PAYU_WHERE_TO_PAY');
		$PAYU_PAYMENT_METHOD = Configuration::get('PAYU_PAYMENT_METHOD');
		$PAYU_BILLING_CURRENCY = Configuration::get('PAYU_BILLING_CURRENCY');
		$PAYU_INVOICE = Configuration::get('PAYU_INVOICE');
		$PAYU_CANCEL_URL = Configuration::get('PAYU_CANCEL_URL');
		$PAYU_TRANSACTION_TYPE = Configuration::get('PAYU_TRANSACTION_TYPE');
				 
		$currency = $this->getCurrency();
		 
		if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($currency))
			return $this->l('payU error: (invalid address or customer)');
			
		$products = $params['cart']->getProducts();

		foreach ($products as $key => $product)
		{
			$products[$key]['name'] = str_replace('"', '\'', $product['name']);
			if (isset($product['attributes']))
				$products[$key]['attributes'] = str_replace('"', '\'', $product['attributes']);
			$products[$key]['name'] = htmlentities(utf8_decode($product['name']));
			$products[$key]['payUAmount'] = number_format(Tools::convertPrice($product['price_wt'], $currency), 2, '.', '');
		}
		
	
		
		
		/*  Connecting with soap  */
		
		global $HTTP_POST_VARS;

		if(Configuration::get('PAYU_SANDBOX')){
			$baseUrl = 'http://staging.payu.co.za';
		} else {
			$baseUrl = 'http://secure.payu.co.za';
		}
		$soapWdslUrl = $baseUrl.'/service/PayUAPI?wsdl';		 
		$payuRppUrl = $baseUrl.'/rpp.do?PayUReference=';
		$apiVersion = 'ONE_ZERO';
		
		//echo "<pre>"; print_r($order); die();
		
		$safeKey = $safe_key;
		$soapUsername = $soap_username;
		$soapPassword = $soap_password;
		$merchantReference = '1360396745';	
			
		$setTransactionArray = array();
		$setTransactionArray['Api'] = $apiVersion;
		$setTransactionArray['Safekey'] = $safeKey;
		$setTransactionArray['TransactionType'] = $PAYU_PAYMENT_METHOD;		
		$setTransactionArray['TransactionType'] = $PAYU_TRANSACTION_TYPE;
	
		$setTransactionArray['AdditionalInformation']['merchantReference'] = $merchantReference;
		$setTransactionArray['AdditionalInformation']['demoMode'] = 'true';
		//$setTransactionArray['AdditionalInformation']['cancelUrl'] = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php?step=3';
		//$setTransactionArray['AdditionalInformation']['returnUrl'] = MODULE_PAYMENT_PAYU_SOAP_RETURN_URL;
		$setTransactionArray['AdditionalInformation']['cancelUrl'] = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?id_cart='.intval($params['cart']->id).'&id_module='.intval($this->id).'&key='.$customer->secure_key;
		
		$setTransactionArray['AdditionalInformation']['returnUrl'] = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?id_cart='.intval($params['cart']->id).'&id_module='.intval($this->id).'&key='.$customer->secure_key;
		$setTransactionArray['AdditionalInformation']['supportedPaymentMethods'] = $PAYU_PAYMENT_METHOD;
		
		$setTransactionArray['Basket']['description'] = "Product Description";
		$setTransactionArray['Basket']['amountInCents'] =(int)(number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, '.', ''));
		$setTransactionArray['Basket']['currencyCode'] = $PAYU_BILLING_CURRENCY;
	 
		$setTransactionArray['Customer']['merchantUserId'] = "7";
		$setTransactionArray['Customer']['email'] = stripslashes($customer->email);
		$setTransactionArray['Customer']['firstName'] =stripslashes($customer->firstname);
		$setTransactionArray['Customer']['lastName'] =stripslashes($customer->lastname);
		$setTransactionArray['Customer']['mobile'] = stripslashes('0211234567');//$customer->telephone;
		$setTransactionArray['Customer']['regionalId'] = '1234512345122';
		$setTransactionArray['Customer']['countryCode'] = '27';
		 
		 
		  
		// 2. Creating a XML header for sending in the soap heaeder (creating it raw a.k.a xml mode)
		$headerXml = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
		$headerXml .= '<wsse:UsernameToken wsu:Id="UsernameToken-9" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
		$headerXml .= '<wsse:Username>'.$soapUsername.'</wsse:Username>';
		$headerXml .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$soapPassword.'</wsse:Password>';
		$headerXml .= '</wsse:UsernameToken>';
		$headerXml .= '</wsse:Security>';
		 
		$headerbody = new SoapVar($headerXml, XSD_ANYXML, null, null, null);
		
		// 3. Create Soap Header.        
		$ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'; //Namespace of the WS. 
		$header = new SOAPHeader($ns, 'Security', $headerbody, true);        
		 
		// 4. Make new instance of the PHP Soap client
		$soap_client = new SoapClient($soapWdslUrl, array("trace" => 1, "exception" => 0)); 
 
		// 5. Set the Headers of soap client. 
		$soap_client->__setSoapHeaders($header); 
 		 	 
		// 6. Do the setTransaction soap call to PayU
		$soapCallResult = $soap_client->setTransaction($setTransactionArray); 
		
		
		
		// 7. Decode the Soap Call Result
		$returnData = json_decode(json_encode($soapCallResult),true);
		 
		
		//$decodedXmlData = json_decode(json_encode((array) simplexml_load_string($returnData)),true); 
				
		//$this->after_process($returnData['return']['payUReference'],$returnData['return']['successful']);		
		//print_r($returnData);die();		
		
		//=============================================================================================
		$payUReference = $returnData['return']['payUReference'];
		$returnData = $this->secondSoapRequest($safe_key,$soap_username,$soap_password,$returnData['return']['payUReference']);
		//print_r($returnData); die();
		
		//===============================================================================================
		
		
		$confirmPayment = 0;
		if($payUReference!=''){
			if(Configuration::get('PAYU_SANDBOX')){
				$confirmUrl = 'https://staging.payu.co.za/rpp.do?PayUReference='.$payUReference;
				
				Configuration::updateValue('CONFIRMATIONURL', $confirmUrl);
			} else {
				$this->conirmationUrl=$confirmUrl = 'https://secure.payu.co.za/rpp.do?PayUReference='.$payUReference;
				Configuration::updateValue('CONFIRMATIONURL', $confirmUrl);
			}
			$confirmPayment =1;		
		} 
	
		/*  End script   */
		$smarty->assign(array(
			'address' => $address,
			'country' => new Country(intval($address->id_country)),
			'customer' => $customer,
			'safe_key' => $safe_key,
			'soap_username' => $soap_username,
			'soap_password' => $soap_password,
			'currency' => $currency,
			'payUUrl' => $this->getpayUUrl($returnData['return']['payUReference']),
			// products + discounts - shipping cost
			'amount' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 4), $currency), 2, '.', ''),
			// shipping cost + wrapping
			'shipping' =>  number_format(Tools::convertPrice(($params['cart']->getOrderShippingCost() + $params['cart']->getOrderTotal(true, 6)), $currency), 2, '.', ''),
			'discounts' => $params['cart']->getDiscounts(),
			'products' => $products,
			// products + discounts + shipping cost
			'total' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, '.', ''),
			'id_cart' => intval($params['cart']->id),
			'goBackUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order-confirmation.php?id_cart='.intval($params['cart']->id).'&id_module='.intval($this->id).'&id_order='.intval($params['cart']->id).'&key='.$customer->secure_key,
			'notify' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/payU/validation.php',
			'cancelUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php?step=3',
			'this_path' => $this->_path,
			'confirmUrl'=>$confirmUrl,
			'confirmPayment' => $confirmPayment
		));


		return $this->display(__FILE__, 'payU.tpl');
	}
	
	private function secondSoapRequest($safe_key,$soap_username,$soap_password,$returnData){
		
		if(Configuration::get('PAYU_SANDBOX')){
			$baseUrl = 'http://staging.payu.co.za';
		} else {
			$baseUrl = 'http://secure.payu.co.za';
		}
		$soapWdslUrl = $baseUrl.'/service/PayUAPI?wsdl';
		$payuRppUrl = $baseUrl.'/rpp.do?PayUReference=';
		$apiVersion = 'ONE_ZERO';
		
		$safeKey = $safe_key;
		$soapUsername = $soap_username;
		$soapPassword = $soap_password;
		$payUReference = $returnData;
		
		// 1. Building the Soap array  of data to send
		$soapDataArray = array();
		$soapDataArray['Api'] = $apiVersion;
		$soapDataArray['Safekey'] = $safeKey;
		$soapDataArray['AdditionalInformation']['payUReference'] = $payUReference;
		
		// 2. Creating a XML header for sending in the soap heaeder (creating it raw a.k.a xml mode)
		$headerXml = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
		$headerXml .= '<wsse:UsernameToken wsu:Id="UsernameToken-9" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
		$headerXml .= '<wsse:Username>'.$soapUsername.'</wsse:Username>';
		$headerXml .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$soapPassword.'</wsse:Password>';
		$headerXml .= '</wsse:UsernameToken>';
		$headerXml .= '</wsse:Security>';
		$headerbody = new SoapVar($headerXml, XSD_ANYXML, null, null, null);
	
	 
	
		// 3. Create Soap Header.        
		$ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'; //Namespace of the WS. 
		$header = new SOAPHeader($ns, 'Security', $headerbody, true);        
	
		// 4. Make new instance of the PHP Soap client
		$soap_client = new SoapClient($soapWdslUrl, array("trace" => 1, "exception" => 0)); 
	
		// 5. Set the Headers of soap client. 
		$soap_client->__setSoapHeaders($header); 
	
		// 6. Do the setTransaction soap call to PayU
		$soapCallResult = $soap_client->getTransaction($soapDataArray); 
		
		// 7. Decode the Soap Call Result
    	$returnData = json_decode(json_encode($soapCallResult),true);	 
		return $returnData;
	}
	
	private function _updatePaymentStatusOfOrder($id_order,$params)
	{
		 
		$objOrder = new Order($id_order); //order with id=$_GET["action"]	
		
		 
		$history = new OrderHistory();
		$history->id_order = (int)$objOrder->id;

		$history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), (int)($objOrder->id)); //order status=3
		
		Db::getInstance()->execute('
		insert into `'._DB_PREFIX_.'order_history` (id_order_state,id_order,id_employee,date_add) values ("'.(int)Configuration::get('PS_OS_PAYMENT').'","'.(int)($objOrder->id).'","'.$params['objOrder']->id_customer.'","'.date("Y-m-d H:i:s").'")');
		
	/*	print_R($history);
		print_R($objOrder);
 */
		return true; 
	}
	
	
	private function _updatePaymentStatusOfOrderCancelled($id_order,$params)
	{
		
		$order = new Order($id_order); 
		/*$history = new OrderHistory();
		$this->validateOrder($_GET['id_cart'], Configuration::get('PS_OS_CANCELED'), $params['total_to_pay'], $this->displayName, NULL, array(), (int)$params['objOrder']->id_currency, false, $params['objOrder']->secure_key);		
		$order = new Order($id_order);
		*/
		$objOrder = new Order($id_order); //order with id=$_GET["action"]	
		
		 
		$history = new OrderHistory();
		$history->id_order = (int)$objOrder->id;
		$history->id_order_state = (int)Configuration::get('PS_OS_CANCELED');	
		$history->id_employee = $params['objOrder']->id_customer;
		
		$history->changeIdOrderState((int)Configuration::get('PS_OS_CANCELED'), (int)($objOrder->id)); 
		 
		Db::getInstance()->execute('
		insert into `'._DB_PREFIX_.'order_history` (id_order_state,id_order,id_employee,date_add) values ("'.(int)Configuration::get('PS_OS_CANCELED').'","'.(int)($objOrder->id).'","'.$params['objOrder']->id_customer.'","'.date("Y-m-d H:i:s").'")');
		 
	/*	print_R($history);
		print_R($objOrder);
		1 _PS_OS_CHEQUE_ : wailting for cheque payment
2_PS_OS_PAYMENT_ : payement successful
3_PS_OS_PREPARATION_ : preparing order
4_PS_OS_SHIPPING_ : order shipped
5_PS_OS_DELIVERED_ : order delivered
6_PS_OS_CANCELED_ : order canceled
7_PS_OS_REFUND_ : order refunded
8_PS_OS_ERROR_ : payement error
9_PS_OS_OUTOFSTOCK_ : product out of stock
10_PS_OS_BANKWIRE_ : wailting for bank wire 


 */
		return true; 
	}
	
	
	public function confirmationUrl(){
	 
		$this->conirmationUrl = Configuration::GET('CONFIRMATIONURL');
		
		if($this->conirmationUrl!=''){
			Tools::redirect($this->conirmationUrl);
			exit;
		}
		return true;
	}
	 
	public function hookPaymentReturn($params)
	{
		
		if (!$this->active)
			return ;
		$safe_key = Configuration::get('PAYU_SAFE_KEY');
		$soap_username = Configuration::get('PAYU_SOAP_USERNAME');
		$soap_password = Configuration::get('PAYU_SOAP_PASSWORD');
		
		$returnData = $this->secondSoapRequest($safe_key,$soap_username,$soap_password,$_GET['PayUReference']);
		
		if(is_array($returnData) && count($returnData)>0 && isset($returnData['return']) && is_array($returnData['return'])){
	
		
			$returnCode=(isset($returnData['return']['resultCode']) && $returnData['return']['resultCode']!='')?$returnData['return']['resultCode']:1; 
			
			
			switch($returnCode){
			
				case 'P003':		 
					// Failed Payment
					$this->_updatePaymentStatusOfOrderCancelled($params['objOrder']->id,$params);
					$cancelUrl = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?controller=order&submitReorder=&id_order='.$params['objOrder']->id; 				
					Tools::redirect($cancelUrl);
					exit; 
					//return $this->display(__FILE__, 'failed.tpl');	
					
					break;
					
				case '301':			
					// Cancel Payment
					//$this->_updatePaymentStatusOfOrderCancelled($params['objOrder']->id,$params);
					 $cancelUrl = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?controller=order&submitReorder=&id_order='.$params['objOrder']->id; 				
					Tools::redirect($cancelUrl);
					exit;
					//return $this->display(__FILE__, 'cancel.tpl');	
					
					break;
				
				case '00':
					//Successfull Payment
					$this->_updatePaymentStatusOfOrder($params['objOrder']->id,$params);
					return $this->display(__FILE__, 'confirmation.tpl');
					
					break;
				default:
					
					return $this->display(__FILE__, 'cancel.tpl');
				break;
					 
			}
		}else{
		
			return $this->display(__FILE__, 'cancel.tpl');		
		}
 		
	}
	
	public function hookCancelProduct($params)
	{
		 
		 
 
	}
	
	public function getL($key)
	{
		$translations = array(
			'mc_gross' => $this->l('payU key \'mc_gross\' not specified, can\'t control amount paid.'),
			'payment_status' => $this->l('payU key \'payment_status\' not specified, can\'t control payment validity'),
			'payment' => $this->l('Payment: '),
			'custom' => $this->l('payU key \'custom\' not specified, can\'t rely to cart'),
			'txn_id' => $this->l('payU key \'txn_id\' not specified, transaction unknown'),
			'mc_currency' => $this->l('payU key \'mc_currency\' not specified, currency unknown'),
			'cart' => $this->l('Cart not found'),
			'order' => $this->l('Order has already been placed'),
			'transaction' => $this->l('payU Transaction ID: '),
			'verified' => $this->l('The payU transaction could not be VERIFIED.'),
			'connect' => $this->l('Problem connecting to the payU server.'),
			'nomethod' => $this->l('No communications transport available.'),
			'socketmethod' => $this->l('Verification failure (using fsockopen). Returned: '),
			'curlmethod' => $this->l('Verification failure (using cURL). Returned: '),
			'curlmethodfailed' => $this->l('Connection using cURL failed'),
		);
		return $translations[$key];
	}

	 
    
	public function checkCurrency($cart)
	{
		return true;
	}
	public function hookDisplayLeftColumn($params)
	{
	  $this->context->smarty->assign(
		  array(
			  'my_module_name' => Configuration::get('MYMODULE_NAME'),
			  'my_module_link' => $this->context->link->getModuleLink('mymodule', 'display')
		  )
	  );
	  return $this->display(__FILE__, 'mymodule.tpl');
	}
	   
	public function hookDisplayRightColumn($params)
	{
	  return $this->hookDisplayLeftColumn($params);
	}
}