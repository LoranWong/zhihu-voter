/**
 * Created by BaosApple on 15/9/6.
 */
$(function() {

    //设置xsrf
    $.ajax({
            url: "interfaces/transit.php?type=xsrf",
            type: 'GET'
        })
        .done(function(response) {

        });

    //显示我的答案
    $.ajax({
            url: "interfaces/getAnswer.php",
            type: 'POST',
            data: {
                email: $.cookie("email")
            }
        })
        .done(function(response) {
            if (response != "false") {
                $('#mian-answer-title').text(response);
            }
        });



    //开始执行搜寻
    var loc = 0;
    var voteSuccessCount = 0;
    var searchInterval = 700;
    setTimeout(searchAndVote, searchInterval);

    function searchAndVote() {
        loc++;
        $('#main-state').text('搜寻第' + loc + '个中...');

        $.ajax({
                url: "interfaces/transit.php?type=vote",
                type: 'POST',
                data: {
                    email: $.cookie("email"),
                    loc: loc,
                    xsrf: $.cookie("_xsrf")
                }
            })
            .done(function(response) {
                // -1代表,所有点赞完成  -2代表查询出错.  -3代表点赞出错  0代表成功。
                //alert(response);
                switch (response) {
                    case "0":
                        //alert("点赞成功");
                        $('#main-state').text('点赞成功 ! 准备下一次搜寻...');
                        //1秒后再次执行
                        setTimeout(searchAndVote, searchInterval)
                        break;
                    case "-1":
                        $('#main-state').text('点赞已完成,待会儿再来吧');
                        //alert("所有点赞完成");
                        break;
                    case "-2":
                        $('#main-state').text('查询出错,准备下一次搜寻...');
                        setTimeout(searchAndVote, searchInterval)
                            //alert("查询出错");
                        break;
                    case "-3":
                        $('#main-state').text('点赞失败,准备下一次搜寻...');
                        setTimeout(searchAndVote, searchInterval)
                            //alert("点赞失败");
                        break;
                    case "-4":
                        $('#main-state').text('答案异常,准备下一次搜寻...');
                        setTimeout(searchAndVote, searchInterval)
                            //alert("点赞失败");
                        break;
                    case "-5":
                        $('#main-state').text('重复点赞,准备下一次搜寻...');
                        setTimeout(searchAndVote, searchInterval)
                            //alert("点赞失败");
                        break;
                }
            }).fail(function() {
                alert("fail");
            });
    }

    //开始执行同步数目
    setTimeout(syncCount,2000);
    
    function syncCount(){
        $.ajax({
            url: "interfaces/getCount.php",
            type: 'POST',
            data: {
                email: $.cookie("email")
            }
        })
        .done(function(response) {
            if (response != "false") {
                json = eval('(' + response + ')');
                $('#total-count').text(json.totalCount);
                $('#vote-count').text(json.voteCount);
                $('#get-vote-count').text(json.getVoteCount);
            }
            setTimeout(syncCount,2000);
        });
    }



    $("#change-answer").click(function() {
        //TODO 调用Logout接口
        window.location.href = "answers.html"

    });

    $("#change-user").click(function() {
        //TODO 调用Logout接口
        alert("若含有已经登录Cookie,切换账号请手动完全清除浏览器cookie后访问首页")
        window.location.href = "index.html"

    });

})
