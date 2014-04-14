<?php
    /**
     * MOLPay ECShop Plugin
     * 
     * @package Payment Method
     * @author MOLPay Technical Team <technical@molpay.com>
     * @version 1.1
     * 
     */
    if (!defined('IN_ECS')) {
        die('Hacking attempt');
    }

    $payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/molpay.php';

    if (file_exists($payment_lang)) {
        global $_LANG;
        include_once($payment_lang);
    }

    if (isset($set_modules) && $set_modules == TRUE) {
        $i = isset($modules) ? count($modules) : 0;

        $modules[$i]['code']    = basename(__FILE__, '.php');
        $modules[$i]['desc']    = 'molpay_desc';
        $modules[$i]['is_cod']  = '0';
        $modules[$i]['is_online']  = '1';
        $modules[$i]['author']  = 'MOLPay Technical Team';
        $modules[$i]['website'] = 'http://www.molpay.com';
        $modules[$i]['version'] = '1.1';
        $modules[$i]['config'] = array(

        array('name' => 'molpay_account', 'type' => 'text', 'value' => ''),
        array('name' => 'molpay_key', 'type' => 'text', 'value' => ''),
        array('name' => 'molpay_currency', 'type' => 'select', 'value' => 'MYR')
        );
        return;
    }

    class molpay {
        function molpay() {
        }

        function __construct() {
            $this->molpay();
        }


        function get_code($order, $payment) {
            $account   = $payment['molpay_account'];				// MOLPay merchant ID
            $verifyk   = $payment['molpay_key'];                                // MOLPay verify key
            $amount    = $order['order_amount'];						
            $orderid   = urlencode($order['log_id']);	
            $bill_name = urlencode($order['consignee']);
            $bill_email = $order['email'];
            $bill_mobile = $order['mobile'];
            $bill_desc = urlencode("Order number : $order[order_sn]");
            $cur    = $payment['molpay_currency'];
            $returnurl = return_url(basename(__FILE__, '.php'));
            $vcode     = md5($amount.$account.$orderid.$verifyk);

            $def_url="<a href=https://www.onlinepayment.com.my/MOLPay/pay/".$account."/?amount=".$amount."&orderid=".$orderid."&bill_name=".$bill_name."&bill_email=".$bill_email."&bill_mobile=".$bill_mobile."&bill_desc=".$bill_desc."&vcode=".$vcode."&returnurl=".$returnurl."&currency=".$cur."><img src='http://molpay.com/home/pic/molpay/molpayhor01_V2.gif' alt='MOLPay Online Payment Gateway' title='MOLPay Online Payment Gateway' border=0></a>";

            return $def_url;
        }

        function respond() {
            $payment = get_payment('molpay');
            $vkey="$payment[molpay_key]";
            
           //------ below don't change ---------------
            $tranID =$_POST['tranID'];
            $orderid =$_POST['orderid'];
            $status =$_POST['status'];
            $domain =$_POST['domain'];
            $amount =$_POST['amount'];
            $currency =$_POST['currency'];
            $appcode =$_POST['appcode'];
            $paydate =$_POST['paydate'];
            $skey =$_POST['skey'];

           // All undeclared variables below are coming from POST method
            $key0 = md5( $tranID.$orderid.$status.$domain.$amount.$currency );
            $key1 = md5( $paydate.$domain.$key0.$appcode.$vkey );

           //var_dump($_POST); var_dump($key1); exit();

            if( $skey != $key1 ) $status= -1; // invalid transaction

           //-------------------------------------------
            If ( $status == "00" ){
                order_paid($orderid, PS_PAYED, $action_note);
                return true;

           } else {
                return false;
            }
        }
    }
?>