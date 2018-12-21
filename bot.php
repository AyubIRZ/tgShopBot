<?php
require_once 'function.php';
global $db;
date_default_timezone_set("Asia/Tehran");
$input=file_get_contents("php://input");
file_put_contents('result.txt', $input.PHP_EOL.PHP_EOL,FILE_APPEND);
$update=json_decode($input,true);
$api_url="https://api.telegram.org/bot".API_TOKEN."/";
///////////////////////////// Admin info /////////////////////////////
$channel_id="@nashenas_7learn";
$bot_username="file_shop_7learn_bot";
$admin_user_id=252519699;
$limit_musics=10;
$pay_time_days=90*24*60*60;
$bot_directory="http://fbf55e78.ngrok.io/telegram-bot-course/project3/";
/////////////////////////////////////////////////////////////////////
if(array_key_exists('message', $update)){
    $user_id=$update['message']['from']['id'];
    $chat_id=$update['message']['chat']['id'];
    $message_id=$update['message']['message_id'];
    $username=(array_key_exists('username',$update['message']['from']))?$update['message']['from']['username']:null;
    $last_name=(array_key_exists('last_name',$update['message']['from']))?$update['message']['from']['last_name']:null;
    $first_name=$update['message']['from']['first_name'];
    $text=$update['message']['text'];
    $audio=(array_key_exists('audio',$update['message']))?$update['message']['audio']['file_id']:null;
    $caption=$update['message']['caption'];
}elseif (array_key_exists('callback_query', $update)){
    $callback_id=$update['callback_query']['id'];
    $user_id=$update['callback_query']['from']['id'];
    $chat_id=$update['callback_query']['message']['chat']['id'];
    $message_id=$update['callback_query']['message']['message_id'];
    $username=(array_key_exists('username',$update['callback_query']['from']))?$update['callback_query']['from']['username']:null;
    $first_name=$update['callback_query']['from']['first_name'];
    $last_name=(array_key_exists('last_name',$update['callback_query']['from']))?$update['callback_query']['from']['last_name']:null;
    $text=$update['callback_query']['data'];
}

///////////////////////////////////////////////////////////////
if(strpos($text, '/start')!==false){
    if($text=='/start'){
        action($chat_id,'typing');
        $query="select * from users WHERE user_id=".$user_id;
        $res=mysqli_query($db, $query);
        $num=mysqli_num_rows($res);
        if($num==0){
            $hash_id=md5($user_id);
            $query="insert into users(user_id,hash_id,first_name,last_name,username,step) VALUES( '$user_id' ,'$hash_id','$first_name','$last_name','$username','home')";
            $res=mysqli_query($db, $query);
        }
        $msg=urlencode("به فروشگاه فایل 7لرن خوش آمدید!".PHP_EOL.PHP_EOL."لطفا از منوی زیر انتخاب کنید:");
        message($chat_id, $msg,mainMenu());
        setStep($user_id, 'home');
    }
}else {
    $step = getStep($user_id);
    switch ($step) {
        case 'home': {
            switch ($text) {
                case '❓ راهنما ❓': {
                    $msg=urlencode("یک متن پیش فرض برای راهنمایی کاربران ربات اینجا قرار میگیرد!");
                    message($chat_id, $msg);
                }
                    break;
                
                case '🔍 جستجو 🔍': {
                    action($chat_id, 'typing');
                    $msg=urlencode("لطفا نام محصول مورد نظرتان را وارد کنید که نتایج به شما نمایش داده شود:");
                    message($chat_id, $msg,goToMainMenu());
                    setStep($user_id, 'search_product');
                }
                    break;

                case '🛍 محصولات 🛍': {
                    $category=getCategory();
                    $keys = array("inline_keyboard" => array());
                    foreach ($category as $cat){
                        $keys['inline_keyboard'][][]=array('text'=>$cat['cat_name'],'callback_data'=>'/category_'.$cat['id']);
                    }
                    $keys=json_encode($keys);
                    $msg=urlencode("لطفا یکی از دسته بندی های زیر را انتخاب کنید:");
                    message($chat_id, $msg,$keys);
                }
                    break;

                case '🛒 سبد خرید 🛒': {
                    action($chat_id,'typing');
                    $cart_info=getCart($user_id);
                    $cart_products=json_decode($cart_info['product_id'],true);
                    $num=count($cart_products);
                    if($num>0 and $cart_products!=null){
                        $product_array=array();
                        $all_price=0;
                        foreach ($cart_products as $productID){
                            $info=getProduct($productID);
                            $product_array[]=$info;
                            $all_price+=$info['price'];
                        }
                        $result="👇 محصولات موجود در سبد 👇".PHP_EOL.PHP_EOL;
                        $cnt=($num>=$limit_musics)?$limit_musics:$num;
                        for ($i=1;$i<=$cnt;$i++){
                            $product_id=$product_array[$i-1]['id'];
                            $product_name=$product_array[$i-1]['name'];
                            $product_price=$product_array[$i-1]['price'];
                            $result.=$i.". "."نام محصول: ".$product_name.PHP_EOL."💰 قیمت(تومان): ".$product_price.PHP_EOL."حذف از سبد 👈 /del_".$product_id.PHP_EOL."------------------------".PHP_EOL;
                        }
                        if($num>$limit_musics){
                            $result.="🔍 $num محصول پیدا شد 🔍".PHP_EOL."قیمت کل(تومان): ".$all_price;
                            message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','/nextCart_'.$limit_musics,'💰 تکمیل خرید','/payCart','❌ خالی کردن سبد','/delCart')));
                        }else{
                            $result.="🔍 $num محصول پیدا شد 🔍".PHP_EOL."قیمت کل(تومان): ".$all_price;
                            message($chat_id, urlencode($result).inline_btn(array('💰 تکمیل خرید','/payCart','❌ خالی کردن سبد','/delCart')));
                        }

                    }else{
                        $msg="♻️ سبد خرید شما خالی است!".PHP_EOL.PHP_EOL."شما میتوانید با استفاده از دکمه ی محصولات یا دکمه ی جستجو محصولات مورد نظرتان را پیدا کنید و به سبد خرید اضافه کنید!";
                        message($chat_id, urlencode($msg));
                    }

                }
                    break;

                case '🗂 سابقه ی خریدها 🗂': {
                    action($chat_id,'typing');
                    $payedCart_info=getPayedCart($user_id);

                    if($payedCart_info!=null){
                        $msg='لیست صورتحساب های پرداخت شده:'.PHP_EOL.PHP_EOL;
                        $counter=1;
                        foreach ($payedCart_info as $payedCart){
                            $msg.=$counter.". تاریخ و زمان خرید: ".date("Y-m-d H:i:s",$payedCart['pay_time']).PHP_EOL."/factor_".$payedCart['id'].PHP_EOL."----------------".PHP_EOL;
                            $counter++;
                        }
                        $msg.="قیمت کل(تومان): ".$all_price;
                        message($chat_id, urlencode($msg));
                    }else{
                        $msg="♻️ شما هیچ سابقه ی خرید قبلی ندارید!".PHP_EOL.PHP_EOL."شما میتوانید با استفاده از دکمه ی محصولات یا دکمه ی جستجو محصولات مورد نظرتان را پیدا کنید و به سبد خرید اضافه کنید!";
                        message($chat_id, urlencode($msg));
                    }
                }
                    break;

                case strpos($text, '/factor_')!==false:{
                    action($chat_id, 'typing');
                    $factorNumber = explode('_', $text)[1];
                    $cart_info=getPayedCart($user_id,$factorNumber);
                    $cart_products=json_decode($cart_info['product_id'],true);
                    $all_price=0;
                    $msg='صورتحساب و لینک دانلود محصولات:'.PHP_EOL.PHP_EOL;
                    $counter=1;
                    foreach ($cart_products as $productID){
                        $info=getProduct($productID);
                        $all_price+=$info['price'];
                        $msg.=$counter.". ".$info['name'].PHP_EOL."لینک: ".$info['download_link'].PHP_EOL."----------------".PHP_EOL;
                        $counter++;
                    }
                    $msg.="قیمت کل(تومان): ".$all_price;
                    message($cart_info['user_id'], urlencode($msg));
                }break;

                case strpos($text, '/del_')!==false: {
                    action($chat_id, 'typing');
                    $productID = explode('_', $text)[1];
                    $cart=getCart($user_id);
                    if($cart!=false) {
                        $cart_products = json_decode($cart['product_id']);
                        $new_products = array();
                        foreach ($cart_products as $item) {
                            if ($item != $productID) {
                                $new_products[] = $item;
                            }
                        }
                        $new_products = json_encode($new_products);
                        setCart($user_id, $new_products);
                        $msg = urlencode("محصول مورد نظر با موفقیت از سبد خرید شما حذف شد!\n\nبرای مشاهده ی سبد خرید دوباره دکمه ی سبد خرید را لمس کنید.");
                        message($chat_id, $msg);
                    }else{
                        $msg="♻️ سبد خرید شما خالی است!".PHP_EOL.PHP_EOL."شما میتوانید با استفاده از دکمه ی محصولات یا دکمه ی جستجو محصولات مورد نظرتان را پیدا کنید و به سبد خرید اضافه کنید!";
                        message($chat_id, urlencode($msg));
                    }
                }break;
                
                case '/delCart':{
                    action($chat_id, 'typing');
                    deleteCart($user_id);
                    $msg=urlencode("♻️ سبد خرید شما با موفقیت خالی شد و تمامی محصولات آن حذف شدند!");
                    answer_query($callback_id, $msg,true);
                }break;

                case '/payCart':{
                    action($chat_id, 'typing');
                    $cart_info=getCart($user_id);
                    if($cart_info!=false){
                        $cart_products=json_decode($cart_info['product_id'],true);
                        $product_array=array();
                        $all_price=0;
                        foreach ($cart_products as $productID){
                            $info=getProduct($productID);
                            $product_array[]=$info;
                            $all_price+=$info['price'];
                        }
                        include_once("functions.php");
                        $api = 'test';
                        $amount =$all_price;
                        $redirect = $bot_directory.'verify.php?factor='.$cart_info['id'];
                        $factorNumber = $cart_info['id'];
                        $result = send($api,$amount,$redirect,$factorNumber);
                        $result = json_decode($result);
                        if($result->status) {
                            $go = "https://pay.ir/payment/gateway/$result->transId";
                            $msg=urlencode("برای پرداخت صورتحساب به مبلغ $all_price لطفا روی دکمه ی \"پرداخت صورتحساب\" کلیک کنید و در درگاه امن بانکی پرداخت خود را انجام دهید.".PHP_EOL.PHP_EOL."بعد از پرداخت موفق آمیز و تایید پرداخت،لینکهای دانلود محصولات به شما ارسال میگردد.");
                            $keys = array("inline_keyboard" => array(array(array('text'=>'پرداخت صورتحساب','url'=>$go))));
                            $keys=json_encode($keys);
                            message($chat_id, $msg,$keys);
                        } else {
                            $msg=$result->errorMessage;
                            message($admin_user_id, urlencode($msg));
                        }
                    }else{
                        $msg="♻️ سبد خرید شما خالی است!".PHP_EOL.PHP_EOL."شما میتوانید با استفاده از دکمه ی محصولات یا دکمه ی جستجو محصولات مورد نظرتان را پیدا کنید و به سبد خرید اضافه کنید!";
                        message($chat_id, urlencode($msg));
                    }
                }break;

                case strpos($text, '/category_')!==false:{
                    action($chat_id, 'typing');
                    $cat_id=explode('_',$text)[1];
                    $query="select * from product WHERE cat_id='".$cat_id."'";
                    $res=mysqli_query($db, $query);
                    $num=mysqli_num_rows($res);
                    if($num>0){
                        setSearch($user_id,$cat_id);
                        $result="👇 محصولات یافت شده 👇".PHP_EOL.PHP_EOL;
                        $cnt=($num>=$limit_musics)?$limit_musics:$num;
                        for ($i=1;$i<=$cnt;$i++){
                            $fetch=mysqli_fetch_assoc($res);
                            $product_id=$fetch['id'];
                            $product_name=$fetch['name'];
                            $product_price=$fetch['price'];
                            $result.=$i.". "."نام محصول: ".$product_name.PHP_EOL."💰 قیمت(تومان): ".$product_price.PHP_EOL."نمایش جزئیات 👈 /pro_".$product_id.PHP_EOL."------------------------".PHP_EOL;
                        }
                        if($num>$limit_musics){
                            $result.="🔍 $num محصول پیدا شد 🔍";
                            message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','/nextProduct_'.$limit_musics)));
                        }else{
                            $result.="🔍 $num محصول پیدا شد 🔍";
                            message($chat_id, urlencode($result));
                        }

                    }else{
                        $msg="متاسفانه هیچ محصولی در این دسته بندی یافت نشد 🔍

برای یافتن محصول مورد نظر میتوانید دسته های دیگر را بررسی کنید یا از قسمت جستجو نام محصول را جستجو نمایید. 📝";
                        message($chat_id, urlencode($msg));
                    }
                }break;

                case strpos($text, '/addToCart_')!==false: {
                    $product_id = explode('_', $text)[1];
                    $cart_info=getCart($user_id);
                    if($cart_info==false){
                        $product_id=array($product_id);
                        addCart($user_id, json_encode($product_id));
                        answer_query($callback_id, urlencode("محصول به سبد خرید اضافه شد!".PHP_EOL."میتوانید به انتخاب محصولات ادامه دهید یا با مراجعه به سبد خرید سفارش خود را تکمیل کنید!"),true);
                    }else{
                        $product_array=json_decode($cart_info['product_id'],true);
                        if(in_array($product_id,$product_array)){
                            answer_query($callback_id, urlencode("این محصول از قبل در سبد خرید شما وجود دارد!"),true);
                        }else{
                            $product_array[]=$product_id;
                            setCart($user_id,json_encode($product_array));
                            answer_query($callback_id, urlencode("محصول به سبد خرید اضافه شد!".PHP_EOL."میتوانید به انتخاب محصولات ادامه دهید یا با مراجعه به سبد خرید سفارش خود را تکمیل کنید!"),true);
                        }
                    }
                }break;

                case strpos($text, '/pro_')!==false:{
                    $product_id=explode('_',$text )[1];
                    $product_info=getProduct($product_id);
                    $msg="🏷  نام محصول: "."<b>".$product_info['name']."</b>".PHP_EOL.PHP_EOL."📝 توضیحات محصول: ".PHP_EOL.$product_info['description'].PHP_EOL.PHP_EOL."💰 قیمت(تومان): "."<b>".$product_info['price']."</b>".PHP_EOL.PHP_EOL;
                    if($product_info['photo_link']!=null){
                        $msg.="<a href='".$product_info['photo_link']."'>🆔</a> @".$bot_username;
                    }else{
                        $msg.="🆔 @".$bot_username;
                    }
                    //message($chat_id, $product_info['photo_link']);
                    message($chat_id, urlencode($msg).inline_btn(array('🛒 اضافه به سبد خرید 🛒','/addToCart_'.$product_id)),null,'HTML');
                }break;

                case strpos($text, '/nextProduct_')!==false: {
                    $data=explode('_',$text );
                    $last_id=$data[1];
                    $cat_id=getSearch($user_id);
                    $query="select * from product WHERE cat_id='".$cat_id."'";
                    $res=mysqli_query($db, $query);
                    $num=mysqli_num_rows($res);
                    $records=array();
                    while ($fetch=mysqli_fetch_assoc($res)){
                        $records[]=$fetch;
                    }
                    if($last_id+$limit_musics<$num){
                        $endponit=$last_id+$limit_musics;
                    }else{
                        $endponit=$num;
                    }
                    $result="👇 نتایج بعدی به شرح زیر است.👇".PHP_EOL.PHP_EOL;
                    $cnt=($num>=$limit_musics)?$limit_musics:$num;
                    for ($i=$last_id;$i<$endponit;$i++){
                        $product_id=$records[$i]['id'];
                        $product_name=$records[$i]['name'];
                        $product_price=$records[$i]['price'];
                        $result.=$i.". "."نام محصول: ".$product_name.PHP_EOL."💰 قیمت(تومان): ".$product_price.PHP_EOL."نمایش جزئیات 👈 /pro_".$product_id.PHP_EOL."------------------------".PHP_EOL;
                    }
                    if($num>$last_id+$limit_musics){
                        $result.="🔍 $num محصول پیدا شد 🔍";
                        message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','/nextProduct_'.$endponit,'صفحه ی قبل','/prevProduct_'.$endponit)));
                    }else{
                        $result.="🔍 $num محصول پیدا شد 🔍";
                        message($chat_id, urlencode($result).inline_btn(array('صفحه ی قبل','/prevProduct_'.$endponit)));
                    }

                }break;

                case strpos($text, '/prevProduct_')!==false: {
                    $data=explode('_',$text );
                    $last_id=$data[1];
                    $cat_id=getSearch($user_id);
                    $query="select * from product WHERE cat_id='".$cat_id."'";
                    $res=mysqli_query($db, $query);
                    $num=mysqli_num_rows($res);
                    $records=array();
                    while ($fetch=mysqli_fetch_assoc($res)){
                        $records[]=$fetch;
                    }
                    if($last_id%$limit_musics==0){
                        $endponit=$last_id-$limit_musics;
                    }else{
                        $last_id=$last_id-($last_id%$limit_musics);
                        $endponit=$last_id;
                    }
                    $result="👇 نتایج بعدی به شرح زیر است.👇".PHP_EOL.PHP_EOL;
                    $cnt=($num>=$limit_musics)?$limit_musics:$num;
                    for ($i=$endponit-$limit_musics;$i<=$endponit;$i++){
                        $product_id=$records[$i]['id'];
                        $product_name=$records[$i]['name'];
                        $product_price=$records[$i]['price'];
                        $result.=$i.". "."نام محصول: ".$product_name.PHP_EOL."💰 قیمت(تومان): ".$product_price.PHP_EOL."نمایش جزئیات 👈 /pro_".$product_id.PHP_EOL."------------------------".PHP_EOL;
                    }
                    if($num>$last_id and $endponit-$limit_musics>0){
                        $result.="🔍 $num محصول پیدا شد 🔍";
                        message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','/nextProduct_'.$endponit,'صفحه ی قبل','/prevProduct_'.$endponit)));
                    }else{
                        $result.="🔍 $num محصول پیدا شد 🔍";
                        message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','/nextProduct_'.$endponit)));
                    }

                }break;

                case '/admin': {
                    if($user_id==$admin_user_id){
                        action($chat_id,'typing');
                        $query="select * from admin WHERE user_id=".$user_id;
                        $res=mysqli_query($db, $query);
                        $num=mysqli_num_rows($res);
                        if($num==0){
                            $query="insert into admin(user_id,step) VALUES( '$user_id' ,'admin_home')";
                            $res=mysqli_query($db, $query);
                        }
                        message($user_id, urlencode('حالت ادمین فعال شد!'),adminMainMenu());
                        setStep($user_id, 'admin');
                        setAdminStep($user_id, "admin_home");
                    }else{
                        message($chat_id, 'دستور یافت نشد!');
                    }
                }
                    break;
            }
        }
            break;
        
        case 'search_product':{
            switch ($text){
                case 'رفتن به منوی اصلی':{
                    $msg=urlencode("منوی اصلی:");
                    message($chat_id, $msg,mainMenu());
                    setStep($user_id, 'home');
                }break;

                case strpos($text, '/addToCart_')!==false: {
                    $product_id = explode('_', $text)[1];
                    $cart_info=getCart($user_id);
                    if($cart_info==false){
                        $product_id=array($product_id);
                        addCart($user_id, json_encode($product_id));
                        answer_query($callback_id, urlencode("محصول به سبد خرید اضافه شد!".PHP_EOL."میتوانید به انتخاب محصولات ادامه دهید یا با مراجعه به سبد خرید سفارش خود را تکمیل کنید!"),true);
                    }else{
                        $product_array=json_decode($cart_info['product_id'],true);
                        if(in_array($product_id,$product_array)){
                            answer_query($callback_id, urlencode("این محصول از قبل در سبد خرید شما وجود دارد!"),true);
                        }else{
                            $product_array[]=$product_id;
                            setCart($user_id,json_encode($product_array));
                            answer_query($callback_id, urlencode("محصول به سبد خرید اضافه شد!".PHP_EOL."میتوانید به انتخاب محصولات ادامه دهید یا با مراجعه به سبد خرید سفارش خود را تکمیل کنید!"),true);
                        }
                    }
                }break;
                
                case strpos($text, '/pro_')!==false:{
                    $product_id=explode('_',$text )[1];
                    $product_info=getProduct($product_id);
                    $msg="🏷  نام محصول: "."<b>".$product_info['name']."</b>".PHP_EOL.PHP_EOL."📝 توضیحات محصول: ".PHP_EOL.$product_info['description'].PHP_EOL.PHP_EOL."💰 قیمت(تومان): "."<b>".$product_info['price']."</b>".PHP_EOL.PHP_EOL;
                    if($product_info['photo_link']!=null){
                        $msg.="<a href='".$product_info['photo_link']."'>🆔</a> @".$bot_username;
                    }else{
                        $msg.="🆔 @".$bot_username;
                    }
                    //message($chat_id, $product_info['photo_link']);
                    message($chat_id, urlencode($msg).inline_btn(array('🛒 اضافه به سبد خرید 🛒','/addToCart_'.$product_id)),null,'HTML');
                }break;
                
                case strpos($text, 'nextProduct_')!==false: {
                    $data=explode('_',$text );
                    $last_id=$data[1];
                    $search=getSearch($user_id);
                    $query="select * from product WHERE name='".$text."' or name LIKE '% ".$text." %' or name LIKE '% ".$text."%' or name LIKE '%".$text." %' or description LIKE '%".$text." %' or description LIKE '% ".$text."%' or description LIKE '% ".$text." %' or description='".$text."'";
                    $res=mysqli_query($db, $query);
                    $num=mysqli_num_rows($res);
                    $records=array();
                    while ($fetch=mysqli_fetch_assoc($res)){
                        $records[]=$fetch;
                    }
                    if($last_id+$limit_musics<$num){
                        $endponit=$last_id+$limit_musics;
                    }else{
                        $endponit=$num;
                    }
                    $result="👇 نتایج بعدی به شرح زیر است.👇".PHP_EOL.PHP_EOL;
                    $cnt=($num>=$limit_musics)?$limit_musics:$num;
                    for ($i=$last_id;$i<$endponit;$i++){
                        $product_id=$records[$i]['id'];
                        $product_name=$records[$i]['name'];
                        $product_price=$records[$i]['price'];
                        $result.=$i.". "."نام محصول: ".$product_name.PHP_EOL."💰 قیمت(تومان): ".$product_price.PHP_EOL."نمایش جزئیات 👈 /pro_".$product_id.PHP_EOL."------------------------".PHP_EOL;
                    }
                    if($num>$last_id+$limit_musics){
                        $result.="🔍 $num محصول پیدا شد 🔍";
                        message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','nextProduct_'.$endponit,'صفحه ی قبل','prevProduct_'.$endponit)));
                    }else{
                        $result.="🔍 $num محصول پیدا شد 🔍";
                        message($chat_id, urlencode($result).inline_btn(array('صفحه ی قبل','prevProduct_'.$endponit)));
                    }

                }break;

                case strpos($text, 'prevProduct_')!==false: {
                    $data=explode('_',$text );
                    $last_id=$data[1];
                    $search=getSearch($user_id);
                    $query="select * from product WHERE name='".$text."' or name LIKE '% ".$text." %' or name LIKE '% ".$text."%' or name LIKE '%".$text." %' or description LIKE '%".$text." %' or description LIKE '% ".$text."%' or description LIKE '% ".$text." %' or description='".$text."'";
                    $res=mysqli_query($db, $query);
                    $num=mysqli_num_rows($res);
                    $records=array();
                    while ($fetch=mysqli_fetch_assoc($res)){
                        $records[]=$fetch;
                    }
                    if($last_id%$limit_musics==0){
                        $endponit=$last_id-$limit_musics;
                    }else{
                        $last_id=$last_id-($last_id%$limit_musics);
                        $endponit=$last_id;
                    }
                    $result="👇 نتایج بعدی به شرح زیر است.👇".PHP_EOL.PHP_EOL;
                    $cnt=($num>=$limit_musics)?$limit_musics:$num;
                    for ($i=$endponit-$limit_musics;$i<=$endponit;$i++){
                        $product_id=$records[$i]['id'];
                        $product_name=$records[$i]['name'];
                        $product_price=$records[$i]['price'];
                        $result.=$i.". "."نام محصول: ".$product_name.PHP_EOL."💰 قیمت(تومان): ".$product_price.PHP_EOL."نمایش جزئیات 👈 /pro_".$product_id.PHP_EOL."------------------------".PHP_EOL;
                    }
                    if($num>$last_id and $endponit-$limit_musics>0){
                        $result.="🔍 $num محصول پیدا شد 🔍";
                        message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','nextProduct_'.$endponit,'صفحه ی قبل','prevProduct_'.$endponit)));
                    }else{
                        $result.="🔍 $num محصول پیدا شد 🔍";
                        message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','nextProduct_'.$endponit)));
                    }

                }break;

                default:{
                    if(array_key_exists('text',$update['message'])){
                        action($chat_id, 'typing');
                        $query="select * from product WHERE name='".$text."' or name LIKE '% ".$text." %' or name LIKE '% ".$text."%' or name LIKE '%".$text." %' or description LIKE '%".$text." %' or description LIKE '% ".$text."%' or description LIKE '% ".$text." %' or description='".$text."'";
                        $res=mysqli_query($db, $query);
                        $num=mysqli_num_rows($res);
                        if($num>0){
                            setSearch($user_id,$text);
                            $result="👇 محصولات یافت شده 👇".PHP_EOL.PHP_EOL;
                            $cnt=($num>=$limit_musics)?$limit_musics:$num;
                            for ($i=1;$i<=$cnt;$i++){
                                $fetch=mysqli_fetch_assoc($res);
                                $product_id=$fetch['id'];
                                $product_name=$fetch['name'];
                                $product_price=$fetch['price'];
                                $result.=$i.". "."نام محصول: ".$product_name.PHP_EOL."💰 قیمت(تومان): ".$product_price.PHP_EOL."نمایش جزئیات 👈 /pro_".$product_id.PHP_EOL."------------------------".PHP_EOL;
                            }
                            if($num>$limit_musics){
                                $result.="🔍 $num محصول پیدا شد 🔍";
                                message($chat_id, urlencode($result).inline_btn(array('صفحه ی بعد','nextProduct_'.$limit_musics)));
                            }else{
                                $result.="🔍 $num محصول پیدا شد 🔍";
                                message($chat_id, urlencode($result));
                            }

                        }else{
                            $msg="متاسفانه هیچ محصولی با این توضیحات یافت نشد 🔍

برای جستجوی بهتر،نام محصول را به انواع شکل برای ربات بفرستید. 📝";
                            message($chat_id, urlencode($msg));
                        }
                    }
                }
            }
        }break;

        case 'admin':{
            if($text=="/admin"){
                if($user_id==$admin_user_id){
                    action($chat_id,'typing');
                    $query="select * from admin WHERE user_id=".$user_id;
                    $res=mysqli_query($db, $query);
                    $num=mysqli_num_rows($res);
                    if($num==0){
                        $query="insert into admin(user_id,step) VALUES( '$user_id' ,'admin_home')";
                        $res=mysqli_query($db, $query);
                    }
                    message($user_id, urlencode('حالت ادمین فعال شد!'),adminMainMenu());
                    setStep($user_id, 'admin');
                    setAdminStep($user_id, "admin_home");
                }else{
                    message($chat_id, 'دستور یافت نشد!');
                }
            }elseif ($text=="برگشت به فروشگاه"){
                $msg=urlencode("منوی اصلی!".PHP_EOL.PHP_EOL."لطفا از منوی زیر انتخاب کنید:");
                message($chat_id, $msg,mainMenu());
                setStep($user_id, 'home');
                setAdminStep($user_id, 'admin_home');
            }
            $adminStep=getAdminStep($user_id);
            switch ($adminStep){
                case 'admin_home':{
                    switch ($text){
                        case 'آمار کاربران': {
                            action($chat_id, 'typing');
                            $count=getMemberCount();
                            $msg=urlencode("تعداد کاربران ربات شما: ".$count);
                            message($chat_id, $msg);
                        }break;

                        case 'سفارشات': {
                            action($chat_id, 'typing');
                            $count=getOrderCount();
                            $msg=urlencode("تعداد سفارشات شما: ".$count);
                            message($chat_id, $msg);
                        }break;

                        case 'دسته بندی ها': {
                            action($chat_id, 'typing');
                            $msg=urlencode("مدیریت دسته بندی ها: ".$count);
                            message($chat_id, $msg,adminCategoryMenu());
                            setAdminStep($user_id, 'admin_cat_catMenu');
                        }break;

                        case 'محصولات': {
                            action($chat_id, 'typing');
                            $msg=urlencode("مدیریت محصولات: ".$count);
                            message($chat_id, $msg,adminProductMenu());
                            setAdminStep($user_id, 'admin_product_productMenu');
                        }break;

                        case 'ارسال پیام': {
                            action($chat_id, 'typing');
                            $msg=urlencode("لطفا یک پیام را برای ارسال به همه ی کاربران وارد کنید: ".$count);
                            message($chat_id, $msg,goToMainMenu());
                            setAdminStep($user_id, 'admin_sendMessage');
                        }break;
                    }
                }break;

                case strpos($adminStep,'admin_product_')!==false:{
                    switch ($adminStep) {

                        case 'admin_product_productMenu':{
                            switch ($text){

                                case 'منوی اصلی':{
                                    message($user_id, urlencode('منوی اصلی مدیریت:'),adminMainMenu());
                                    setAdminStep($user_id, "admin_home");
                                }break;

                                case 'افزودن محصول':{
                                    action($chat_id,'typing');
                                    action($chat_id,'typing');
                                    message($user_id, urlencode('لطفا نام محصول را وارد کنید:'));
                                    setAdminStep($user_id, "admin_product_setProductName");
                                }break;

                                case 'حذف محصول':{

                                }break;

                                case 'بروزرسانی محصول':{

                                }break;

                                case 'تعداد محصولات':{

                                }break;
                            }
                        }break;

                        case 'admin_product_setProductName':{
                            action($chat_id, 'typing');
                            setAdminInfo($user_id,'tmp_name', $text);
                            message($chat_id, urlencode("لطفا توضیحات این محصول را ارسال کنید:"));
                            setAdminStep($user_id, 'admin_product_setProductDesc');
                        }break;

                        case 'admin_product_setProductDesc':{
                            action($chat_id, 'typing');
                            setAdminInfo($user_id,'tmp_desc', $text);
                            message($chat_id, urlencode("لطفا قیمت این محصول را ارسال کنید:"));
                            setAdminStep($user_id, 'admin_product_setProductPrice');
                        }break;

                        case 'admin_product_setProductPrice':{
                            action($chat_id, 'typing');
                            setAdminInfo($user_id,'tmp_price', $text);
                            message($chat_id, urlencode("لطفا عکس این محصول را ارسال کنید:"));
                            setAdminStep($user_id, 'admin_product_setProductPhoto');
                        }break;

                        case 'admin_product_setProductPhoto':{
                            action($chat_id, 'typing');
                            if(isset($update['message']['photo'])){
                                $file_id=$update['message']['photo'][1]['file_id'];
                                $link=getFileLink($file_id);
                                $url="images/".time().basename($link);
                                file_put_contents($url, file_get_contents($link));
                                setAdminInfo($user_id,'tmp_photo_link',$bot_directory.$url);
                                $category=getCategory();
                                $keys = array("inline_keyboard" => array());
                                foreach ($category as $cat){
                                    $keys['inline_keyboard'][][]=array('text'=>$cat['cat_name'],'callback_data'=>'/selectCat_'.$cat['id']);
                                }
                                $keys=json_encode($keys);
                                message($chat_id, urlencode("لطفا دسته بندی این محصول را انتخاب کنید:"),$keys);
                                setAdminStep($user_id, 'admin_product_setProductCategory');
                            }else{
                                message($chat_id, urlencode("لطفا یک عکس ارسال کنید!!"));
                            }
                        }break;

                        case 'admin_product_setProductCategory':{
                            action($chat_id, 'typing');
                            if(strpos($text, '/selectCat_')!==false){
                                $cat=explode('_',$text )[1];
                                setAdminInfo($user_id,'tmp_cat_id', $cat);
                                message($chat_id, urlencode("لطفا لینک دانلود این محصول را ارسال کنید:"));
                                setAdminStep($user_id, 'admin_product_setProductLink');
                            }else{
                                message($chat_id, urlencode("لطفا دسته های لیست را انختخاب کنید!!"));
                            }
                        }break;

                        case 'admin_product_setProductLink':{
                            action($chat_id, 'typing');
                            $admin_info=getAdminInfo($user_id);
                            addProduct($admin_info['tmp_cat_id'],$admin_info['tmp_name'] , $admin_info['tmp_desc'],$admin_info['tmp_price'] , $admin_info['tmp_photo_link'],$text);
                            message($chat_id, urlencode("محصول جدید با موفقیت اضافه شد!"));
                            setAdminStep($user_id, 'admin_product_productMenu');
                        }break;

                    }
                }break;

                case strpos($adminStep,'admin_cat_')!==false:{
                    switch ($adminStep){
                        case 'admin_cat_catMenu':{
                            switch ($text){
                                case 'منوی اصلی':{
                                    message($user_id, urlencode('منوی اصلی مدیریت:'),adminMainMenu());
                                    setAdminStep($user_id, "admin_home");
                                }break;

                                case 'افزودن دسته':{
                                    action($chat_id,'typing');
                                    message($user_id, urlencode('لطفا نام دسته بندی را وارد کنید:'));
                                    setAdminStep($user_id, "admin_cat_setCatName");
                                }break;

                                case 'حذف دسته':{
                                    //tamrin baraye shoma
                                }break;

                                case 'بروزرسانی دسته':{
                                    action($chat_id,'typing');
                                    $category=getCategory();
                                    $keys = array("inline_keyboard" => array());
                                    foreach ($category as $cat){
                                        $keys['inline_keyboard'][][]=array('text'=>$cat['cat_name'],'callback_data'=>'/editCat_'.$cat['id']);
                                    }
                                    $keys=json_encode($keys);
                                    $msg=urlencode("لطفا یکی از دسته بندی های زیر را برای بروزرسانی انتخاب کنید:");
                                    message($chat_id, $msg,$keys);
                                }break;

                                case strpos($text, '/editCat_')!==false:{
                                    action($chat_id,'typing');
                                    $cat_id=explode('_', $text)[1];
                                    $cat_info=getCategory($cat_id);
                                    $msg="مشحصات دسته بندی انتخاب شده:".PHP_EOL.PHP_EOL."نام دسته بندی: ".$cat_info['cat_name'].PHP_EOL.PHP_EOL."توضیحات دسته بندی: ".PHP_EOL.$cat_info['cat_description'].PHP_EOL.PHP_EOL."لطفا یکی از گزینه هارا انتخاب کنید:";
                                    message($chat_id, urlencode($msg).inline_btn(array('نام دسته','/editCatName_'.$cat_id,'توضیحات دسته','/editCatDesc_'.$cat_id)));
                                }break;

                                case strpos($text, '/editCatName_')!==false:{
                                    action($chat_id,'typing');
                                    $cat_id=explode('_', $text)[1];
                                    $msg="لطفا نام جدید دسته را ارسال کنید:";
                                    message($chat_id, urlencode($msg));
                                    setAdminStep($user_id, 'admin_cat_editCatName');
                                    setAdminInfo($user_id,'cat_name',$cat_id);
                                }break;
                                case strpos($text, '/editCatDesc_')!==false:{
                                    action($chat_id,'typing');
                                    $cat_id=explode('_', $text)[1];
                                    $msg="لطفا توضیحات جدید دسته را ارسال کنید:";
                                    message($chat_id, urlencode($msg));
                                    setAdminStep($user_id, 'admin_cat_editCatDesc');
                                    setAdminInfo($user_id,'cat_name',$cat_id);
                                }break;
                            }
                        }break;

                        case 'admin_cat_editCatName':{
                            action($chat_id, 'typing');
                            $cat_id=getAdminInfo($user_id)['cat_name'];
                            setCategoryInfo($cat_id,'cat_name' , $text);
                            message($chat_id, urlencode("نام این دسته با موفقیت به روزرسانی شد!"));
                            setAdminStep($user_id, 'admin_cat_catMenu');
                        }break;

                        case 'admin_cat_editCatDesc':{
                            action($chat_id, 'typing');
                            $cat_id=getAdminInfo($user_id)['cat_name'];
                            setCategoryInfo($cat_id,'cat_description' , $text);
                            message($chat_id, urlencode("توضیحات این دسته با موفقیت به روزرسانی شد!"));
                            setAdminStep($user_id, 'admin_cat_catMenu');
                        }break;

                        case 'admin_cat_setCatName':{
                            action($chat_id, 'typing');
                            setAdminInfo($user_id,'cat_name', $text);
                            message($chat_id, urlencode("لطفا توضیحات این دسته بندی را ارسال کنید:"));
                            setAdminStep($user_id, 'admin_cat_setCatDesc');
                        }break;

                        case 'admin_cat_setCatDesc':{
                            action($chat_id,'typing' );
                            $cat_name=getAdminInfo($user_id)['cat_name'];
                            addCategory($cat_name,$text);
                            message($chat_id, urlencode("دسته بندی جدید با موفقیت اضافه شد!"));
                            setAdminStep($user_id, 'admin_cat_catMenu');
                            setAdminInfo($user_id, 'cat_name', null);
                        }break;
                    }
                }break;

                case 'admin_sendMessage':{
                    switch ($text){
                        case 'منوی اصلی':{
                            message($user_id, urlencode('منوی اصلی مدیریت:'),adminMainMenu());
                            setAdminStep($user_id, "admin_home");
                        }break;

                        default:{
                            set_time_limit(0);
                            $query="select user_id from users";
                            $res=mysqli_query($db, $query);
                            $user_count=0;
                            $counter=1;
                            while($userID=mysqli_fetch_assoc($res)){
                                if($counter<=25){
                                    message($userID['user_id'], urlencode($text));
                                    $counter++;
                                    usleep(100);
                                }else{
                                    message($userID['user_id'], urlencode($text));
                                    $counter=1;
                                    sleep(1);
                                }
                                $user_count++;
                            }
                            message($admin_user_id, urlencode("پیام شما به ".$user_count." نفر ارسال شد."),adminMainMenu());
                            setAdminStep($user_id, "admin_home");

                        }break;
                    }
                }break;
            }
        }
    }
}