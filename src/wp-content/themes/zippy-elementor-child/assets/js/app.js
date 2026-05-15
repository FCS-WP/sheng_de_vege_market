import "../lib/slick/slick.min";
import "./woocommerce/add_to_cart.js";
import "./authen/authen.js";
import "./shop/filter.js";
import "./shop/mobile-filter.js";
import "./checkout.js";
import "./page/home.js";

function initMobileSlick() {
  if ($(window).width() <= 767) {
    if (!$("#au-icons").hasClass("slick-initialized")) {
      $("#au-icons").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        infinite: true,
        dots: false,
      });
    }
  } else {
    if ($("#au-icons").hasClass("slick-initialized")) {
      $("#au-icons").slick("unslick");
    }
  }
}

$(document).ready(function () {
  initMobileSlick();
});

$(window).on("resize", function () {
  initMobileSlick();
});
