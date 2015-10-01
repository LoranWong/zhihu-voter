<?php
require_once "functions.php";

$email = $_POST["email"];
$answerId = $_POST["answerId"];
$answerTitle = $_POST["answerTitle"];

$bmobObj = new BmobObject("ZUser");

//查询用户自身送出
$res=$bmobObj->get("",array('where={"email":"'.$email.'"}'));

if(count($res->results)){
    $objectId = $res->results[0]->objectId;
    $res=$bmobObj->update($objectId,array("answerId"=>$answerId,"answerTitle"=>$answerTitle));
    if($res != null){
        echoTrueAndExit();
    }else{
        echoFalseAndExit();
    }
}else{
    echoFalseAndExit();
}


