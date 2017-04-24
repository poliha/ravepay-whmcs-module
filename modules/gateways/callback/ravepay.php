<?php
/**
 * Ravepay Payment Gateway Callback File
 * Author: Peter Oliha
 * Twitter: @PeterOliha
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Retrieve data returned in payment gateway callback
$success = false; //assume failed verification
$txRef = $_POST["ref"];
$invoiceId = $_POST["invoice_id"];
$paymentAmount = $_POST["amount"];

/**
 * Validate callback authenticity.
 */

// $txRef = '1ed5ce371610281c32c6505103559730';

 $testMode = $gatewayParams['testMode'];

    if ($testMode == 'on') {
        $secretKey = $gatewayParams['testSecretKey'];
        $publicKey = $gatewayParams['testPublicKey'];

    } else {
        $secretKey = $gatewayParams['liveSecretKey'];
        $publicKey = $gatewayParams['livePublicKey'];
    }


// $auth_header = 'Authorization: Bearer '.$secretKey;
// $verify_url = 'https://api.ravepay.co/transaction/verify/'.$txRef;

// $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $verify_url);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $secretKey"]);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     $rdata = curl_exec($ch);

//     curl_close($ch);

// $output = json_decode($rdata);



// $verifyStatus = $output->status;
// $verifyMessage = $output->message;
// $txStatus = $output->data->status;
// $txAmount = $output->data->amount;
$verifyStatus =false;
if ($verifyStatus) {

    if ($txStatus == 'success' && $txAmount == $paymentAmount) 
    {
        $success = true;
    }
    
} 
else {
    $success = false;
}






if ($success) {

    /**
     * Validate Callback Invoice ID.
     *
     * Checks invoice ID is a valid invoice number. Note it will count an
     * invoice in any status as valid.
     *
     * Performs a die upon encountering an invalid Invoice ID.
     *
     * Returns a normalised invoice ID.
     */
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

    /**
     * Check Callback Transaction ID.
     *
     * Performs a check for any existing transactions with the same given
     * transaction number.
     *
     * Performs a die upon encountering a duplicate.
     */
    checkCbTransID($txRef);

    /**
     * Log Transaction.
     *
     * Add an entry to the Gateway Log for debugging purposes.
     *
     * The debug data can be a string or an array. In the case of an
     * array it will be
     *
     * @param string $gatewayName        Display label
     * @param string|array $debugData    Data to log
     * @param string $transactionStatus  Status
     */
    logTransaction($gatewayParams['name'], $_POST, $verifyMessage);

    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    
    //convert amount back to decimals
    $paymentAmount = floatval($paymentAmount/100);

    addInvoicePayment(
        $invoiceId,
        $txRef,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );

    // Return success message
    $msg = '<h1 class="text-success">Payment Successful</h1>
            <p>'.$verifyMessage.'</p>';
    
    print_r($msg);
}else {
    // Return failure message
    $msg = '<h1 class="text-danger">Payment Failed</h1>
            <p>'.$verifyMessage.'</p>';
    
    print_r($msg);
}

