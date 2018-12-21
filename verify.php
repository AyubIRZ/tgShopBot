<?php
include_once("functions.php");
include_once("function.php");
date_default_timezone_set("Asia/Tehran");
global $db;
$bot_username="file_shop_7learn_bot";
$api = 'test';
$transId = $_POST['transId'];
$result = verify($api,$transId);
$result = json_decode($result,true);
$cart_info=getCart($_GET['factor']);
if($result['status']==1){
    $cart_products=json_decode($cart_info['product_id'],true);
    $all_price=0;
    $msg='صورتحساب شما پرداخت شد.لینک دانلود محصولات:'.PHP_EOL.PHP_EOL;
    $counter=1;
    foreach ($cart_products as $productID){
        $info=getProduct($productID);
        $all_price+=$info['price'];
        $msg.=$counter.". ".$info['name'].PHP_EOL."لینک: ".$info['download_link'].PHP_EOL."----------------".PHP_EOL;
        $counter++;
    }
    $msg.="قیمت کل(تومان): ".$all_price;
    addPayedCart($cart_info['user_id'],$cart_info['product_id'] ,time());
    deleteCart($cart_info['user_id']);
    message($cart_info['user_id'], urlencode($msg));
    header("Location: https://t.me/".$bot_username);
}else{
    $msg=urlencode("❗️ متاسفانه پرداخت شما ناموفق بود و لینک های دانلود برای شما در دسترس نیست.");
    message($cart_info['user_id'], $msg);

}