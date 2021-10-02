$(document).ready(function(){
    $( ".buttonss" ).click(function() {
        var button = $(this).attr("data-info")
        $('.transition').css({"animation-name": "move" });
        $('.content').css({"animation-name": "hide", "animation-duration": "1s", "opacity": "0%" });
        setTimeout(redirect(button), 1500);
    });

    document.body.style.zoom="70%"
});

function redirect(button) {  
    $('body').css({"background-image": "url()"});
    setTimeout(function(){ window.location.href = "./"+ button +".html"; }, 2300);
}