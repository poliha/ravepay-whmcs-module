<?php
/**
 * RavePay Payment Gateway Module for WHMCS 
 * Author: Peter Oliha
 * Twitter: @PeterOliha
 *
 */

// TO DO
// - support payment in other currencies


if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * @return array
 */
function ravepay_MetaData()
{
    return array(
        'DisplayName' => 'Ravepay',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 *
 * @return array
 */
function ravepay_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Credit/Debit cards - Ravepay',
        ),
        
        'testSecretKey' => array(
            'FriendlyName' => 'Test Secret Key',
            'Type' => 'password',
            'Size' => '64',
            'Default' => '',
            'Description' => 'Enter test secret key here',
        ),
        
        'testPublicKey' => array(
            'FriendlyName' => 'Test Public Key',
            'Type' => 'password',
            'Size' => '64',
            'Default' => '',
            'Description' => 'Enter test public key here',
        ),
        'liveSecretKey' => array(
            'FriendlyName' => 'Live Secret Key',
            'Type' => 'password',
            'Size' => '64',
            'Default' => '',
            'Description' => 'Enter live secret key here',
        ),
        
        'livePublicKey' => array(
            'FriendlyName' => 'Live Public Key',
            'Type' => 'password',
            'Size' => '64',
            'Default' => '',
            'Description' => 'Enter test public key here',
        ),        
        // the yesno field type displays a single checkbox option
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
            'Default' => '0',
        ),
        
        
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see http://docs.whmcs.com/Payment_Gateway_Module_Parameters
 *
 * @return string
 */
function ravepay_link($params)
{
    // Gateway Configuration Parameters
    
    
    $testMode = $params['testMode'];

    if ($testMode == 'on') {
        
        $secretKey = $params['testSecretKey'];
        $publicKey = $params['testPublicKey'];
    } else {
        $secretKey = $params['liveSecretKey'];
        $publicKey = $params['livePublicKey'];
        
    }
    
    
    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];
    $callbackUrl = $systemUrl . '/modules/gateways/callback/ravepay.php';
  
    //payment Parameters
    $txRef = md5(uniqid(rand(),true));
    $koboAmount = $amount*100;

    if (strtoupper($currencyCode) == 'NGN') {
        $htmlOutput =   '
            <script src="https://js.ravepay.co/v1/inline.js"></script>
            <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
            <script>
              function payWithRavepay(){
                var handler = RavepayPop.setup({
                  key: "'.$publicKey.'",
                  email: "'.$email.'",
                  amount: '.$koboAmount.',
                  ref: "'.$txRef.'",
                  callback: function(response){
                      $("#ravepayMsg").html("<h5>Transaction ref is "+response.trxref+". </h5>Please wait while we pprocess your payment ...");
                      
                      verifyRavepayPayment(response.trxref);
                  },
                  onClose: function(){
                      ravepayClosed();
                      
                  }
                });
                handler.openIframe();
              }

              function verifyRavepayPayment(ref){
                $.post("'.$callbackUrl.'",
                {
                    ref: ref,
                    amount: '.$koboAmount.',
                    invoice_id: '.$invoiceId.'
                },
                function(data, status){
                    $("#ravepayMsg").html(data);
                    location.reload();
                });
              }

              function ravepayClosed(){
                 $("#ravepayMsg").html("Payment Cancelled");
              }

            </script>
            
            <div id="ravepayMsg"></div>
            <form >
              
              <button type="button" onclick="payWithRavepay()"> Pay via Credit/Debit card</button> 
            </form>';

    } else {
        $htmlOutput = "<h2>Payment only supported in Nigerian Naira(NGN)</h2>";
    }
    


    return $htmlOutput;
  

}



