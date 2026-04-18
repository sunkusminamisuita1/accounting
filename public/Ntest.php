<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
$_SESSION['ErrMsg'] ="";
$_SESSION['ErrMsg'] = "test message" ;
require_once __DIR__.'/../config/bootstrap.php';
require_once __DIR__.'./../app/repositories/voucherRepository.php';


requireLogin();
$acctrepo = new VoucherRepository();   
$accounts =  $acctrepo->getAccounts();
require_once __DIR__.'/../views/voucher/Ncreate.php';
?>