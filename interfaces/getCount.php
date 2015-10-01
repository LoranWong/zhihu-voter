<?php
require_once "functions.php";

$email = $_POST["email"];

$bmobObj = new BmobObject("ZUser");
//先查询总个数
$res=$bmobObj->get("",array('count=1','limit=0'));
$totalCount = $res->count;

//查询用户自身送出
$res=$bmobObj->get("",array('where={"email":"'.$email.'"}'));
if(count($res->results)){
    $voteCount = $res->results[0]->voteCount;
    $getVoteCount = $res->results[0]->getVoteCount;
    echo json_encode(array("totalCount"=>$totalCount,"voteCount"=>$voteCount,"getVoteCount"=>$getVoteCount));
}else{
    echo json_encode(array("totalCount"=>$totalCount,"voteCount"=>0,"getVoteCount"=>0));;
}


