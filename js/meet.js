$(document).ready(function(){
    $(".button").click(function(){
      $(".header4").show();
      $(".header4").animate({left: '0px'}, 800);
    });

    $(".btn").click(function(){
        $(".header4").animate({left: '100%'}, 800, function(){
            $(".header4").hide();
        });
      });
  });