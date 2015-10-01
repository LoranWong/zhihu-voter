$(function() {

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
        });


    $("#github-btn").click(function() {
        window.location.href ="https://github.com/JackWong025";
    });

})
