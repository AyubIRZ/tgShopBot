<?php
require_once 'config.php';
define('API_TOKEN', 'ENTER YOUR TOKEN');
////////////////////////// Functions /////////////////////////
function bot($data){
    return json_decode(file_get_contents("https://api.telegram.org/bot".API_TOKEN."/".$data),true);
}
function message($chat_id,$msg,$markup=null,$parse_mode=null){
    if($parse_mode!=null){
        if($markup!=null)
        {
            bot("sendMessage?chat_id=".$chat_id."&text=".$msg."&reply_markup=".$markup."&parse_mode=".$parse_mode);
        }
        else
        {
            bot("sendMessage?chat_id=".$chat_id."&text=".$msg."&parse_mode=".$parse_mode);
        }
    }else{
        if($markup!=null)
        {
            bot("sendMessage?chat_id=".$chat_id."&text=".$msg."&reply_markup=".$markup);
        }
        else
        {
            bot("sendMessage?chat_id=".$chat_id."&text=".$msg);
        }
    }
}

function forwardMessage($user_id,$message_id,$from_chat_id){
    bot("forwardMessage?chat_id=".$user_id."&from_chat_id=".$from_chat_id."&message_id=".$message_id);
}

function editMessage($chat_id,$message_id,$msg){
        bot("editMessageText?chat_id=".$chat_id."&message_id=".$message_id."&text=".$msg);
}

function deleteMessage($chat_id,$message_id){
    bot("deleteMessage?chat_id=".$chat_id."&message_id=".$message_id);
}

function photo($chat_id,$photo_link,$caption=null)
{
    bot("sendPhoto?chat_id=".$chat_id."&photo=".$photo_link."&caption=".$caption);
}
function video($chat_id,$video_link,$caption=null)
{
    bot("sendVideo?chat_id=".$chat_id."&video=".$video_link."&caption=".$caption);
}

function send_file($chat_id,$file_id,$caption=null)
{
    bot("sendDocument?chat_id=".$chat_id."&document=".$file_id."&caption=".$caption);
}

function action($chat_id,$action)
{
    bot("sendChatAction?chat_id=".$chat_id."&action=".$action);
}

function answer_query($query_id,$text,$show_alert=false)
{
    bot("answerCallbackQuery?callback_query_id=".$query_id."&text=".$text."&show_alert=".$show_alert);
}

function getFileLink($file_id)
{
    $array=bot("getFile?file_id=".$file_id);
    $link="https://api.telegram.org/file/bot".API_TOKEN."/".$array['result']['file_path'];
    return $link;
}
function getStep($user_id){
    global $db;
    $query="select step from users WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    $res=mysqli_fetch_assoc($res);
    return $res['step'];
}

function setStep($user_id,$step){
    global $db;
    $query="update users set step='".$step."' WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    return $res;
}

function getAdminStep($user_id){
    global $db;
    $query="select step from admin WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    $res=mysqli_fetch_assoc($res);
    return $res['step'];
}

function setAdminStep($user_id,$step){
    global $db;
    $query="update admin set step='".$step."' WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    return $res;
}

function getUserByUsername($username){
    global $db;
    $query="select * from users WHERE username='".$username."'";
    $res=mysqli_query($db, $query);
    $res=mysqli_fetch_assoc($res);
    return $res;
}

function setSearch($user_id,$search_string){
    global $db;
    $query="update users set last_search='".$search_string."' WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    return $res;
}

function getSearch($user_id){
    global $db;
    $query="select last_search from users WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    $res=mysqli_fetch_assoc($res);
    return $res['last_search'];
}

function getProduct($product_id){
    global $db;
    $query="select * from product WHERE id=".$product_id;
    $res=mysqli_query($db, $query);
    $res=mysqli_fetch_assoc($res);
    return $res;
}

function getCategory($cat_id=null){
    global $db;
    if($cat_id!=null){
        $query="select * from category WHERE id=".$cat_id;
        $res=mysqli_query($db, $query);
        $res=mysqli_fetch_assoc($res);
        return $res;
    }else{
        $query="select * from category";
        $res=mysqli_query($db, $query);
        $category=array();
        while ($curr=mysqli_fetch_assoc($res)){
            $category[]=$curr;
        }
        return $category;
    }
}

function getCart($user_id){
    global $db;
    $query="select * from cart WHERE user_id=".$user_id." or id=".$user_id;
    $res=mysqli_query($db, $query);
    $res=mysqli_fetch_assoc($res);
    return $res;
}
function addCart($user_id,$product_id){
    global $db;
    $query="insert into cart(user_id,product_id) VALUES('$user_id','$product_id')";
    $res=mysqli_query($db, $query);
    return mysqli_insert_id($db);
}

function addPayedCart($user_id,$product_id,$timestamp){
    global $db;
    $query="insert into payed_cart(user_id,product_id,pay_time) VALUES('$user_id','$product_id','$timestamp')";
    $res=mysqli_query($db, $query);
    return mysqli_insert_id($db);
}
function getPayedCart($user_id,$payedCart_id=null){
    global $db;
    if($payedCart_id==null){
        $query="select * from payed_cart WHERE user_id=".$user_id;
        $res=mysqli_query($db, $query);
        $info=array();
        while ($res=mysqli_fetch_assoc($res)){
            $info[]=$res;
        }
        return $info;
    }else{
        global $db;
        $query="select * from payed_cart WHERE user_id=".$user_id." and id=".$payedCart_id;
        $res=mysqli_query($db, $query);
        $res=mysqli_fetch_assoc($res);
        return $res;
    }
    
}
function setCart($user_id,$new_json_of_products){
    global $db;
    $query="update cart set product_id='".$new_json_of_products."' WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    return $res;
}

function deleteCart($user_id){
    global $db;
    $query="delete from cart WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    return $res;
}

function setAdminInfo($user_id,$field,$value){
    global $db;
    $query="update admin set ".$field."='".$value."' WHERE user_id=".$user_id;
    $res=mysqli_query($db, $query);
    return $res;
}

function getAdminInfo($user_id){
    global $db;
    $query="select * from admin WHERE user_id='$user_id'";
    $res=mysqli_query($db, $query);
    $res=mysqli_fetch_assoc($res);
    return $res;
}
function addCategory($cat_name,$cat_desc){
    global $db;
    $query="insert into category(cat_name,cat_description) VALUES('$cat_name','$cat_desc')";
    $res=mysqli_query($db, $query);
    return mysqli_insert_id($db);
}
function setCategoryInfo($cat_id,$field,$value){
    global $db;
    $query="update category set ".$field."='".$value."' WHERE id=".$cat_id;
    $res=mysqli_query($db, $query);
    return $res;
}

function addProduct($cat_id,$name,$desc,$price,$photo_link,$download_link){
    global $db;
    $query="insert into product(cat_id,name,description,price,photo_link,download_link) VALUES('$cat_id','$name','$desc',".$price.",'$photo_link','$download_link')";
    $res=mysqli_query($db, $query);
    return mysqli_insert_id($db);
}
function inline_btn($i){
    $ar=array();
    $button=array();
    for($c=0;$c<count($i);$c=$c+2)
    {
        $button[$c/2 % 2]=array("text"=>urlencode($i[$c]),"callback_data"=>$i[$c+1]);
        if($c/2 % 2){
            array_push($ar,array($button[0],$button[1]));
            $button=array();
        }elseif(count($i)-$c<=2){
            array_push($ar,array($button[0]));
            $button=array();
        }
    }
    return "&reply_markup=".json_encode(array("inline_keyboard"=>$ar));
}

function isMember($user_id,$chat_id){
    $status=bot("getChatMember?chat_id=".$chat_id."&user_id=".$user_id);
    return $status['result']['status'];
}

function getMemberCount(){
    global $db;
    $query="select * from users";
    $res=mysqli_query($db, $query);
    $res=mysqli_num_rows($res);
    return $res;
}

function getUser($user_id){
    global $db;
    $query="select * from users WHERE user_id='$user_id' OR hash_id='$user_id'";
    $res=mysqli_query($db, $query);
    $res=mysqli_fetch_assoc($res);
    return $res;
}

function getOrderCount(){
    global $db;
    $query="select * from payed_cart";
    $res=mysqli_query($db, $query);
    $res=mysqli_num_rows($res);
    return $res;
}

function mainMenu(){
    $markup=array('keyboard'=>array(array('🔍 جستجو 🔍','🛍 محصولات 🛍'),array('🛒 سبد خرید 🛒'),array('🗂 سابقه ی خریدها 🗂','❓ راهنما ❓')),'resize_keyboard'=>true);
    return json_encode($markup);
}

function  goToMainMenu(){
    $markup=array('keyboard'=>array(array('رفتن به منوی اصلی')),'resize_keyboard'=>true);
    return json_encode($markup);
}

function nameMenu(){
    $markup=array('keyboard'=>array(array('لغو و بازگشت به منوی تنظیمات')),'resize_keyboard'=>true);
    return json_encode($markup);
}

function acceptMenu(){
    $markup=array('keyboard'=>array(array('❌ لغوش کن ❌','✅ حله بفرست ✅')),'resize_keyboard'=>true);
    return json_encode($markup);
}

function privacyMenu($status){
    if($status){
        $markup=array('keyboard'=>array(array("دریافت فقط از طریق لینک"),array('بازگشت به منوی تنظیمات')),'resize_keyboard'=>true);
    }else{
        $markup=array('keyboard'=>array(array("دریافت آزاد از همه"),array('بازگشت به منوی تنظیمات')),'resize_keyboard'=>true);
    }
    return json_encode($markup);
}

function adminMainMenu(){
    $markup=array('keyboard'=>array(array('آمار کاربران','سفارشات'),array('دسته بندی ها','محصولات'),array('ارسال پیام'),array('برگشت به فروشگاه')),'resize_keyboard'=>true);
    return json_encode($markup);
}

function adminProductMenu(){
    $markup=array('keyboard'=>array(array('افزودن محصول','حذف محصول'),array('بروزرسانی محصول','تعداد محصولات'),array('منوی اصلی')),'resize_keyboard'=>true);
    return json_encode($markup);
}

function adminCategoryMenu(){
    $markup=array('keyboard'=>array(array('افزودن دسته','حذف دسته'),array('بروزرسانی دسته'),array('منوی اصلی')),'resize_keyboard'=>true);
    return json_encode($markup);
}

