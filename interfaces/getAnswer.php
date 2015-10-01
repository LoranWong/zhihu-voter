<?php
require_once "functions.php";

$email = $_POST["email"];

$bmobObj = new BmobObject("ZUser");

//查询用户自身送出
$res=$bmobObj->get("",array('where={"email":"'.$email.'"}'));

if(count($res->results)){
    $answerTitle = $res->results[0]->answerTitle;
    echo $answerTitle;
}else{
    echoFalseAndExit();
}


