<?php
include_once 'lib/BmobObject.class.php';
include_once 'lib/BmobUser.class.php';
include_once 'lib/BmobException.class.php';
include_once 'lib/BmobRestClient.class.php';
include_once 'lib/BmobCloudCode.class.php';

/**隔行遍历,设置本地 Cookie , 注意在输出其他内容前调用
 * @param $header
 */
function setLocalCookie($header){
    $header_arr = explode("\n",$header);
    foreach($header_arr as $header_each){
        if (strpos($header_each, 'Set-Cookie') !== false) {
            $p1 = strpos($header_each,"=");
            $p2 = strpos($header_each,";");
            $name = substr($header_each,12,$p1 - 12);
            $value = substr($header_each,$p1 + 1,$p2-$p1-1);
            $expires = null;
            switch ($name){
                case "cap_id":
                case "_xsrf":
                    // 30 days
                    $expires = time()+60*60*24*30;
                    break;
                case "q_c1":
                case "_za":
                case "z_c0":
                    // 3 years
                    $expires = time()+60*60*24*365*3;
                    break;
                case "unlock_ticket":
                    // 4 hours
                    $expires = time()+60*60*4;
                    break;
            }
            setrawcookie($name , $value , $expires , "/");
        }
    }
}

/**传入http返回信息,解析获得header以及body
 * @param $response
 * @return array
 */
function getHeaderAndBody($response){
    //划分header以及body
    $header_and_body_arr = explode("\n\r\n",$response);
    //存储对应信息
    $header = $header_and_body_arr[0];
    $body = $header_and_body_arr[1];
    if(strpos($header,"Continue")){
        $header = $header_and_body_arr[1];
        $body = $header_and_body_arr[2];
    }
    return array("header"=>$header , "body"=> $body);

}


function curlGet($url , $post_data=null){
    $ch = curl_init();
    $request_header = array(
        'Cookie: '.getallheaders()["Cookie"],
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36'
    );

    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLINFO_, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //返回header信息
    curl_setopt($ch, CURLOPT_HEADER, 1);

    if($post_data != null){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }

    $response = curl_exec($ch);
    $response_arr = getHeaderAndBody($response);
    $header = $response_arr["header"];
    $body = $response_arr["body"];

    //解析header数组 设置Cookie
    setLocalCookie($header);
//
//        echo "  以下 ⬇️ curl_getinfo ";
//        print_r(curl_getinfo($ch, CURLINFO_HEADER_OUT));
//        echo '  以下 ⬇️ $post_data ';
//        print_r($post_data);
//        echo '  以下 ⬇️ $header ';
//        echo $header;
//        echo '  以下 ⬇️ $body ';
//        echo $body;

    curl_close($ch);
    return $body;
}


/**
 * 检查是否需要注册用户
 * @param $email
 */
function checkSignUp($email){

    $bmobObj = new BmobObject("ZUser");
    $res=$bmobObj->get("",array('where={"email":"'.$email.'"}'));
    if(count($res->results)){
        //已注册 不做操作
    }else{
        //未注册,新建对象
        $res = $bmobObj->create(array("email"=>$email , "voteCount"=>0 , "getVoteCount"=>0 , "voteLeft" => 0)); //添加对象
    }

}


/**
 * 搜寻一个最适合的AnswerId来进行点赞
 * @param $loc
 * @param $email
 * @return mixed -1代表,所有点赞完成  -2代表查询出错.
 */
function searchAnswerId($loc,$email){
    $bmobObj = new BmobObject("ZUser");
    $res=$bmobObj->get("",array('order=-voteLeft','limit='.$loc ,'keys=answerId,email'));
    if(count($res->results)){
        if(count($res->results)<$loc){
            //点赞完成,loc上限达到最大
            return -1;
        }else{
            //取出最后一个,并且返回,尝试进行点赞
            $answer = $res->results[count($res->results)-1]->answerId;
            $temail = $res->results[count($res->results)-1]->email;
            //如果该记录answerId为空,则递归进入下一条
            if($answer == null){
                return -4;
            }else if(isVoteRecordExist($email,$temail,$answer)){
                //如果该answer已经被点赞过
                return -5;
            }else{
                $result = array("answer"=>$answer,"temail"=>$temail);
            }
            return $result;
        }
    }else{
        return -2;
    }
}

function isVoteRecordExist($email,$temail,$answer){

    $bmobObj = new BmobObject("Vote");
    $res=$bmobObj->get("",array('where={"email":"'.$email.'","temail":"'.$temail.'","answerId":"'.$answer.'"}'));
    if(count($res->results)){
        return true;
    }else{
        return false;
    }
}


/**
 * 添加点赞数据到数据表,并同步用户数量信息
 * @param $email
 * @param $answer
 * @param $temail
 */
function addVoteRecord($email,$answer,$temail){
    $bmobObj = new BmobObject("Vote");
    $bmobObj->create(array("email"=>$email , "answerId"=>$answer , "temail"=>$temail)); //添加对象

    //同步点赞方数据
    $bmobObj = new BmobObject("ZUser");
    $res = $bmobObj->get("",array('where={"email":"'.$email.'"}'));
    if(count($res->results)){
        $voteCount = $res->results[0]->voteCount;
        $voteLeft = $res->results[0]->voteLeft;
        $objectId = $res->results[0]->objectId;
        $voteCount++;
        $voteLeft++;
        $bmobObj->update($objectId, array("voteCount"=>$voteCount,"voteLeft"=>$voteLeft));
    }else{
        return;
    }


    //同步被点赞方数据
    $bmobObj = new BmobObject("ZUser");
    $res = $bmobObj->get("",array('where={"email":"'.$temail.'"}'));
    if(count($res->results)){
        $getVoteCount = $res->results[0]->getVoteCount;
        $voteLeft = $res->results[0]->voteLeft;
        $objectId = $res->results[0]->objectId;
        $getVoteCount++;
        $voteLeft--;
        $bmobObj->update($objectId, array("getVoteCount"=>$getVoteCount,"voteLeft"=>$voteLeft));
    }else{
        return;
    }
}


function echoFalseAndExit(){
    echo "false";
    exit();
}



function echoTrueAndExit(){
    echo "true";
    exit();
}