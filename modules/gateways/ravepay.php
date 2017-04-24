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
        
        'livePublicKey' => array(
            'FriendlyName' => 'Live Public Key',
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

        
        // the yesno field type displays a single checkbox option
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
            'Default' => '0',
        ),

        // the yesno field type displays a single checkbox option
        'enableUSD' => array(
            'FriendlyName' => 'Enable USD',
            'Type' => 'yesno',
            'Description' => 'Tick to enable USD payments',
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
        // test details as specified by flutterwave
        $secretKey = 'FLWSECK-bb971402072265fb156e90a3578fe5e6-X';
        $publicKey = 'FLWPUBK-e634d14d9ded04eaf05d5b63a0a06d2f-X';
        $payBaseUrl = '<script type="text/javascript" src="http://flw-pms-dev.eu-west-1.elasticbeanstalk.com/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>';
    } else {
        $secretKey = $params['liveSecretKey'];
        $publicKey = $params['livePublicKey'];
        $payBaseUrl = '<script type="text/javascript" src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script> ';

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

    $jqueryUrl = '<script  src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="  crossorigin="anonymous"></script>';

    $postfields = array();
    $postfields['PBFPubKey'] = $publicKey;
    $postfields['txref'] = $txRef;
    $postfields['amount'] = $koboAmount;
    $postfields['username'] = $username;
    $postfields['currency'] = strtoupper($currencyCode);
    $postfields['country'] = $country;
    $postfields['customer_email'] = $email;
    $postfields['customer_firstname'] = $firstname;
    $postfields['customer_lastname'] = $lastname;
    $postfields['redirect_url'] = "";
    $postfields['customer_phone'] = $phone;
    // optional Params
    $postfields['pay_button_text'] = "";
    $postfields['custom_title'] = "";
    $postfields['custom_description'] = "";
    $postfields['custom_logo'] = "";
    $postfields['meta-invoice_id'] = $invoiceId;
    $postfields['meta-description'] = $description;
    $postfields['meta-address1'] = $address1;
    $postfields['meta-address2'] = $address2;
    $postfields['meta-city'] = $city;
    $postfields['meta-state'] = $state;
    $postfields['meta-postcode'] = $postcode;




    if (strtoupper($currencyCode) == 'NGN') {
        
        $htmlOutput = $payBaseUrl;
        $htmlOutput .= $jqueryUrl;
        $htmlOutput .= '<script>
              function setupRavepay(){
                getpaidSetup({
                    customer_email: "'.$email.'",
                    customer_lastname: "'.$lastname.'",
                    customer_firstname: "'.$firstname.'",
                    currency: "'.strtoupper($currencyCode).'",
                    amount: "'.$amount.'",
                    txref: "'.$txRef.'",
                    PBFPubKey: "'.$publicKey.'",

                    onclose:function(){
                        ravepayClosed();
                    },
                    callback:function(response){
                        console.log("d:",response);
                        if (response.tx) {
                            $("#ravepayMsg").html("<h5>Transaction status: "+response.tx.status+". </h5><h5>Transaction ref is "+response.tx.txRef+". </h5><h5>Response: "+response.tx.vbvrespmessage+". </h5>Please wait while we process your Invoice ...");
                        } else {
                            $("#ravepayMsg").html("<h5>Transaction status: "+response.data.data.status+". </h5><h5>Response: "+response.data.data.message+". </h5>");
                        }

                    }
                });


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

              <button type="button" onclick="setupRavepay()"> Pay via Credit/Debit card</button>
            </form>';

    } else {
        $htmlOutput = "<h2>Payment only supported in Nigerian Naira(NGN)</h2>";
    }



    return $htmlOutput;


}



