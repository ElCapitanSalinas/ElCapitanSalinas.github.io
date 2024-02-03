
$(document).ready(function () {
    

    let navbar_toggled = false
    $('.toggle').click(function (e) { 
        e.preventDefault();
        console.log('click')
        if (!navbar_toggled) {
            $( ".navbar" ).animate({backgroundColor: '#010c18'}, 500, function() {});
            $(".navbar-content").fadeIn(400, ()=>{$( ".navbar-content" ).animate({top: '15vh'}, 500, function() {
                navbar_toggled = true
            })});
        } else {
            $( ".navbar" ).animate({backgroundColor: '#021327'}, 500, function() {});
            $( ".navbar-content" ).animate({top: '-30vh'}, 500, function() {
                $( ".navbar" ).animate({'background-color': '#021327'}, 500, function() {});
                navbar_toggled = false
                $(".navbar-content").fadeOut();
            });
        }
    
    });

    $('.hidden-right').click(function (e) { 
        e.preventDefault();
        $( ".servers-card-right" ).animate({marginRight: '20%', height: '41vh' , width: '57vw', marginTop:'4.5vh'}, 500, function() {
            // $(this).addClass('servers-card-center');
            $('.servers-card-center').addClass('hidden-center');
        })
        
        $( ".servers-card-center" ).animate({height: '50vh' , width: '69vw', marginTop:'0vh'}, 500, function() {
        });
    
        $( ".servers-card-left" ).animate({marginLeft: '-150%', height: '50vh' , width: '69vw', marginTop:'0vh'}, 500, function() {
        }); 
    });

    $('.hidden-left').click(function (e) { 
        e.preventDefault();
        $( ".servers-card-left" ).animate({marginLeft: '20%', height: '41vh' , width: '57vw', marginTop:'4.5vh'}, 500, function() {
            // $(this).addClass('servers-card-center');
            $('.servers-card-center').addClass('hidden-center');
        })
        
        $( ".servers-card-center" ).animate({height: '50vh' , width: '69vw', marginTop:'0vh'}, 500, function() {
        });
    
        $( ".servers-card-right" ).animate({marginRight: '-150%', height: '50vh' , width: '69vw', marginTop:'0vh'}, 500, function() {
        }); 
    });

    $('#center').click(function (e) { 
        e.preventDefault();
        $( ".servers-card-right" ).animate({marginRight: '-60vw', height: '50vh' , width: '69vw', marginTop:'0vh'}, 500, function() {})
        
        $( ".servers-card-center" ).animate({height: '41vh' , width: '57vw', marginTop:'4.5vh'}, 500, function() {});
    
        $( ".servers-card-left" ).animate({marginLeft: '-60vw', height: '50vh' , width: '69vw', marginTop:'0vh'}, 500, function() {}); 
    });
   
    // height: 41vh;width: 57vw;
    
});
