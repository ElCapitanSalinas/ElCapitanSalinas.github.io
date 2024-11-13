let maps = [ // Maps url
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2879.1807504322505!2d-79.34041072322796!3d43.81061007109505!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89d4d373b2a1e6c9%3A0x8e5854394a5bce26!2sSearchKings!5e0!3m2!1sen!2sca!4v1726233530989!5m2!1sen!2sca',
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2604.341670522123!2d-123.01243192302523!3d49.25096957299875!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x548676de2704755b%3A0x530d247b48cc3312!2s3602%20Gilmore%20Wy%2C%20Burnaby%2C%20BC%20V5G%204W7%2C%20Canada!5e0!3m2!1sen!2sco!4v1731473871760!5m2!1sen!2sco'
]

let actMap = 0 // Actual Map displayed on the DOM


$(document).ready(function () {
    // Made to match the height of the image to the text
    $('#about-us-image').css('height', $('#about-us-text').height()); // Resize function for the About Us Image
    
    // Change map by pressin the button
    $('.btn-branch').click(function (e) { 
        e.preventDefault();
        let selectedMap = $(this).data('map') - 1
        let mapBtn = this
        if (selectedMap !== actMap){
            $('#map').fadeOut(300, function(){
                $('.selected').removeClass('selected');
                actMap = selectedMap
                $('#map').attr('src', maps[actMap]);
                
                setTimeout(() => {
                    console.log(actMap, maps[actMap])
                    $(mapBtn).addClass('selected');
                $('#map').fadeIn(1000);
                }, 400);
                
            });
          
        }
    });

    // Added function to show when the cart is active, press on the cart to see the bubble.
    let cartActive = false
    $('.cart').click(function (e) { 
        e.preventDefault();
        if (!cartActive) {
            $('.active-cart').fadeIn();
        } else {
            $('.active-cart').fadeOut();
        }
        cartActive = !cartActive
    });

        
    $(window).resize(function() { // Update the image size when reloading the webpage
        console.log('Window resize detected')
        $('#about-us-image').css('height', $('#about-us-text').height());
    });
});