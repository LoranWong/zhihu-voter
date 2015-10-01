/**
 * Created by BaosApple on 15/9/6.
 */
$(function() {

    //抓取个人域名
    $.ajax({
            url: "interfaces/transit.php?type=domain",
            type: 'GET'
        })
        .done(function(response) {
            if (response == "false") {
                alert("获取个人域名失败");
            } else {
                getMyAnswers(response);
            }
        });


    function getMyAnswers(domain) {
        $.ajax({
                url: "interfaces/transit.php?type=answers&domain=" + domain,
                type: 'GET'
            })
            .done(function(response) {
                if (response == "false") {
                    $('.answer-item').first().find('.answer-title').text("您没有回答过问题");
                    alert("您没有回答过问题,不过您可以先为别人点赞,送出的赞将会被累计。")
                    window.location.href = "main.html";

                } else {
                    var json = eval(response);
                    for (var i = 0; i < json.length; i++) {
                        item = (i == 0) ? $('.answer-item').first() : $('.answer-item').first().clone();
                        item.find('.answer-title').text(json[i].title);
                        item.find('.ui-li-count').text(json[i].vote);
                        item.attr('answer-id', json[i].answer_id);
                        item.click(function(event) {
                            var answer_id = $(this).attr('answer-id');
                            var answer_title = $(this).find(".answer-title").text();
                            //TODO
                            $.ajax({
                                    url: "interfaces/setAnswer.php",
                                    type: 'POST',
                                    data: {
                                        email: $.cookie("email"),
                                        answerId: answer_id,
                                        answerTitle: answer_title,
                                    }
                                })
                                .done(function(response) {
                                    if (response == "false") {
                                        alert("设置失败");
                                    } else {
                                        window.location.href = "main.html";
                                    }
                                });


                        });
                        item.appendTo('#main_answers_list');
                    }
                }
            });
    }


})
