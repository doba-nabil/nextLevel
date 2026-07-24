// Function to equalize heights of all slider items


//loader
$(function() {
    $('.loader-container').fadeOut();
  })

  // sidebar menu toggle
  $(document).on('click', '#sidebar_toggler', function() {
    $('#mainSide_menu').addClass('sidebar-show');
    $('body').addClass('overBody__hidden');
     $('.mob-overlay').addClass('active');
  });

  $(document).on('click', '.openCart_menu', function() {
    $('#sideCart_menu').addClass('sidebar-show');
    $('body').addClass('overBody__hidden');
     $('.mob-overlay').addClass('active');
  });

  $(document).on('click', '.burgerBtn', function() {
    $(this).closest('.sidebar-wrapper').removeClass('sidebar-show');
    $('body').removeClass('overBody__hidden');
    $('.mob-overlay').removeClass('active');
  });

  $(document).on('click', '.backMob_Bttn', function() {
    $(this).closest('.sidebar-wrapper').removeClass('sidebar-show');
  });


  $(document).on('click', '.mob-overlay', function() {
    $('#mainSide_menu').removeClass('sidebar-show');
    $('#sideCart_menu').removeClass('sidebar-show');
    $('body').removeClass('overBody__hidden');
    $('.mob-overlay').removeClass('active');
  });

  $(document).on('click', '.read_more', function() {
    $(this).parent('.text_cont').find('p').css('display', ' block')
  });

  $(document).on('click', '.SToggle_PickMenu', function() {
    $('.pickUPMenu_details').slideToggle();
  });

  // scroll top button
  $(function () {

    var scrollButton = $('.go-top');

    $(window).scroll(function () {

      if($(window).scrollTop() >= 500) {
        scrollButton.show();
      }else {
        scrollButton.hide();
      }
    });

    scrollButton.click(function () {
      $('html, body').animate({scrollTop: 0});
    })
  });

  // intro slider
  $(function(){

    var is_rtl = $("html[lang='ar']").length > 0;

    $('.intro_slider').slick({
      infinite: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      rtl: is_rtl,
      dots: true,
      arrows: false,
      loop: true,
      autoplay: true,
      autoplaySpeed: 5000,
      speed: 1000,
    });
  });

  // trending slider
  $(function(){

    var is_rtl = $("html[lang='ar']").length > 0;

    $('.trending_slider').slick({
      infinite: true,
      slidesToShow: 4,
      slidesToScroll: 1,
      rtl: is_rtl,
      dots: false,
      arrows: true,
      autoplay: false,
      autoplaySpeed: 5000,
      speed: 500,
      nextArrow: '<button type="button" class="slick-next"><i class="fa-solid fa-chevron-right"></i></button>',
      prevArrow: '<button type="button" class="slick-prev"><i class="fa-solid fa-chevron-left"></i></button>',
      responsive: [
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 3,
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 3,
          }
        },
        {
          breakpoint: 575,
          settings: {
            slidesToShow: 2,
          }
        },
      ]
    });
  });

  // healthy burger slider
  $(function(){

    var is_rtl = $("html[lang='ar']").length > 0;

    $('.healthy_slider').slick({
      infinite: true,
      slidesToShow: 4,
      slidesToScroll: 1,
      rtl: is_rtl,
      dots: false,
      arrows: true,
      autoplay: true,
      autoplaySpeed: 5000,
      speed: 500,
      nextArrow: '<button type="button" class="slick-next"><i class="fa-solid fa-chevron-right"></i></button>',
      prevArrow: '<button type="button" class="slick-prev"><i class="fa-solid fa-chevron-left"></i></button>',
      responsive: [
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 3,
            arrows: false,
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 2,
            arrows: false,
          }
        },
        {
          breakpoint: 575,
          settings: {
            slidesToShow: 2,
            arrows: false,
          }
        },
      ]
    });
  });


  // offers slider
  $(function(){

    var is_rtl = $("html[lang='ar']").length > 0;

    $('.offers_slider').slick({
      infinite: true,
      slidesToShow: 2,
      slidesToScroll: 1,
      rtl: is_rtl,
      dots: false,
      arrows: true,
      autoplay: true,
      autoplaySpeed: 5000,
      centerMode: true,
      speed: 500,
      nextArrow: '<button type="button" class="slick-next"><i class="fa-solid fa-chevron-right"></i></button>',
      prevArrow: '<button type="button" class="slick-prev"><i class="fa-solid fa-chevron-left"></i></button>',
      responsive: [
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 1,
            arrows: false,
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 1,
          }
        },
        {
          breakpoint: 400,
          settings: {
            slidesToShow: 1,
          }
        },
      ]
    });
  });

  // products slider
  $(function(){

    var is_rtl = $("html[lang='ar']").length > 0;

    $('.products_slider').slick({
      infinite: true,
      slidesToShow: 4,
      slidesToScroll: 1,
      rtl: is_rtl,
      dots: false,
      arrows: false,
      autoplay: false,
      responsive: [
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 1,
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2,
          }
        },
        {
          breakpoint: 575,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2,
          }
        },
      ]
    });
  });

  // BoxeSS slider
  $(function(){

    var is_rtl = $("html[lang='ar']").length > 0;

    $('.BoxeSS_slider').slick({
      infinite: true,
      slidesToShow: 4,
      slidesToScroll: 1,
      rtl: is_rtl,
      dots: false,
      arrows: false,
      autoplay: false,
      autoplaySpeed: 5000,
      speed: 500,
      responsive: [
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 3,
            arrows: false,
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 3,
          }
        },
        {
          breakpoint: 575,
          settings: {
            slidesToShow: 2,
          }
        },
      ]
    });
  });

  // cateGories slider
  $(function(){

    var is_rtl = $("html[lang='ar']").length > 0;

    $('.cateGories_Slider').slick({
      infinite: false,
      loop: false,
      slidesToShow: 4,
      slidesToScroll: 1,
      rtl: is_rtl,
      dots: false,
      arrows: false,
      autoplay: false,
      responsive: [
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 5.5,
            slidesToScroll: 5.5,
          }
        }
      ]
    });
  });

  // related products slider
  $(function(){

    var is_rtl = $("html[lang='ar']").length > 0;

    $('.related_products_slider').slick({
      infinite: true,
      slidesToShow: 4,
      slidesToScroll: 1,
      rtl: is_rtl,
      dots: false,
      arrows: false,
      autoplay: false,
      autoplaySpeed: 5000,
      speed: 500,
      responsive: [
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 1,
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2,
          }
        },
        {
          breakpoint: 575,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2,
          }
        },
      ]
    });
  });

  // ordItems slider
$(function(){

  var is_rtl = $("html[lang='ar']").length > 0;

  $('.ordItems_Slider').slick({
    infinite: true,
    slidesToShow: 2,
    slidesToScroll: 1,
    rtl: is_rtl,
    dots: false,
    arrows: true,
    autoplay: true,
    autoplaySpeed: 5000,
    speed: 500,
      nextArrow: '<button type="button" class="slick-next"><i class="fa-solid fa-chevron-right"></i></button>',
  	prevArrow: '<button type="button" class="slick-prev"><i class="fa-solid fa-chevron-left"></i></button>',
    responsive: [
      {
      breakpoint: 1200,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    },
    {
      breakpoint: 575,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    },
  ]
  });
});


  // product  plus and minus
//   var numberSpinner = (function() {
//     $('.number__spinner .ns-btn a').click(function() {
//       var btn = $(this),
//         oldValue = btn.closest('.number__spinner').find('input').val().trim(),
//         newVal = 0;

//       if (btn.attr('data-dir') === 'up') {
//         newVal = parseInt(oldValue) + 1;
//       } else {
//         if (oldValue > 1) {
//           newVal = parseInt(oldValue) - 1;
//         } else {
//           newVal = 1;
//         }
//       }
//       btn.closest('.number__spinner').find('input').val(newVal);
//     });

//   })();

  $(document).ready(function(){

    // delete order card
    // $(document).on('click', '.remove_order', function() {
    //   const $cardWish = $(this).closest('.ordItem_cardNSM');

    //   Swal.fire({
    //     title: "Remove from wishlist",
    //     text: "Are you sure you want to remove order",
    //     icon: "warning",
    //     showCancelButton: true,
    //     showCloseButton: true,
    //     confirmButtonColor: "#AACC3B",
    //     cancelButtonColor: "transparent",
    //     confirmButtonText: "Remove"
    //   }).then((result) => {
    //     if (result.isConfirmed) {
    //       Swal.fire({
    //         title: "Deleted!",
    //         text: "order has been deleted.",
    //         icon: "success",
    //         confirmButtonColor: "#000",
    //       });
    //       $cardWish.remove();
    //     }
    //   });
    // });

      // Profile edit functionality is now handled in profile-scripts.blade.php
      // Old code removed to prevent conflicts with new implementation

  });
