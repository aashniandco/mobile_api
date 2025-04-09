
	$('.banner-slider').slick({
		dots: true,
		infinite: true,
		speed: 600,
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: true,
		autoplay: true,
        prevArrow: $('.home-previous'),
        nextArrow: $('.home-next'), 
		draggable: false
	});


	$('.newin-slider').slick({
        dots: false,
        infinite: true,
        slidesToShow: 5,
        slidesToScroll: 1,
        draggable: true,
        autoplay:true,
        arrows: true,
        prevArrow: $('.newin-previous'),
        nextArrow: $('.newin-next'),               
          responsive: [
           {
              breakpoint: 1025,
              settings: { 
                autoplay:false,
                infinite: true 
              }
            },
            {
              breakpoint: 768,
              settings: {
                autoplay:false,
                slidesToShow: 2.5,
                slidesToScroll: 1,
                infinite: true,
                centerMode: false,
                centerPadding: '12%',
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 1,
                slidesToShow: 1,
                centerPadding: '40px'
              }
            }
          ]
    }); 

    $('.a-coedit-slider').slick({
        dots: false,
        infinite: false,
        slidesToShow: 6,
        slidesToScroll: 1,
        draggable: true,
        autoplay:true,
        arrows: true,
        prevArrow: $('.co-edi-previous'),
        nextArrow: $('.co-edi-next'),               
          responsive: [
           {
              breakpoint: 1025,
              settings: { 
                autoplay:false,
                infinite: true 
              }
            },
            {
              breakpoint: 768,
              settings: {
                autoplay:false,
                slidesToShow: 2.5,
                slidesToScroll: 1,
                infinite: true,
                centerMode: false,
                centerPadding: '12%',
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 2.3,
                slidesToShow: 1,
                centerPadding: '40px'
              }
            }
          ]
    });

    $('.access-slider').slick({
        dots: false,
        infinite: false,
        slidesToShow: 4.9,
        slidesToScroll: 1,
        draggable: true,
        autoplay:true,
        arrows: true,
        prevArrow: $('.access-previous'),
        nextArrow: $('.access-next'),               
          responsive: [
           {
              breakpoint: 1025,
              settings: { 
                autoplay:false,
                infinite: true 
              }
            },
            {
              breakpoint: 768,
              settings: {
                autoplay:false,
                slidesToShow: 2.5,
                slidesToScroll: 1,
                infinite: true,
                centerMode: false,
                centerPadding: '12%',
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 2.3,
                slidesToShow: 1,
                centerPadding: '40px'
              }
            }
          ]
    });

    $('.bestseller-slider').slick({
        dots: false,
        infinite: false,
        slidesToShow: 6.1,
        slidesToScroll: 1,
        draggable: false,
        autoplay:true,
        arrows: true,
        prevArrow: $('.bestseller-previous'),
        nextArrow: $('.bestseller-next'),               
          responsive: [
           {
              breakpoint: 1025,
              settings: { 
                autoplay:false,
                infinite: true 
              }
            },
            {
              breakpoint: 768,
              settings: {
                autoplay:false,
                slidesToShow: 2.5,
                slidesToScroll: 1,
                infinite: true,
                centerMode: false,
                centerPadding: '12%',
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 2.3,
                slidesToShow: 1,
                centerPadding: '40px'
              }
            }
          ]
    });