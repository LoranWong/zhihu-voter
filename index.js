/**
 * Created by BaosApple on 15/9/6.
 */
$(function(){

    if($.cookie("_xsrf") != null){
        window.location = "main.html";
    }

    //验证cookie
    email = $.cookie("email");
    pass = $.cookie("pass");
    if(email != null && pass != null){
        $("#zhihuname").val(email);
        $("#zhihupass").val(pass);
    }

    //加载验证码图片

    var timestamp = (new Date()).valueOf();
    $("#zhihucaptcha-img").attr("src","interfaces/transit.php?type=captcha&r=" + timestamp);

    $("#zhihu-login-btn").click(function (){
        //先存cookie,过期时间为一年
        $.cookie("email",$("#zhihuname").val(),{ expires: 365 });
        $.cookie("pass",$("#zhihupass").val(),{ expires: 365 });

        $.ajax({
            url: "interfaces/transit.php?type=login",
            type: 'POST',
            data: {
                type:"login",
                email: $("#zhihuname").val(),
                password:$("#zhihupass").val(),
                remember_me:"true",
                captcha:$("#zhihucaptcha").val()
            }
        })
            .done(function(response) {
                if(response.indexOf("0,")>-1){
                    location.href = "answers.html";
                }else if(response.indexOf("1,")>-1){
                    alert("验证码或者密码错误!")
                    window.history.go(0);
                }else if(response.indexOf("403")>-1){
                    alert("含有已经登录Cookie,如需切换账号请完全清除浏览器cookie后重新登录")
                    //实际上也是登录成功
                    location.href = "answers.html";
                }

            });




    });

})