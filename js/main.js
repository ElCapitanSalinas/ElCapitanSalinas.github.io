$(document).ready(function(){
    $( ".buttonss" ).click(function() {
        var button = $(this).attr("data-info")
        $(".container-transition").show();
        $(".container-transition").animate({top: '-200%'}, 1500);
        setTimeout(redirect(button), 1500);
    });

    document.body.style.zoom="70%"
});

function redirect(button) {  
    $('body').css({"background-image": "url()"});
    setTimeout(function(){ window.location.href = "./"+ button +".html"; }, 2300);
}