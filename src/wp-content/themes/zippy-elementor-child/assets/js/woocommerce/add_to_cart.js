jQuery(document).ready(function ($) {
  $(document).on("click", ".qty-plus-btn, .qty-minus-btn", function () {
    var pid = $(this).data("product_id");
    var $input = $("#qty-" + pid);
    var val = parseInt($input.val());

    if ($(this).hasClass("qty-plus-btn")) {
      val++;
    } else {
      if (val > 1) val--;
    }
    $input.val(val);

    $('.ajax_add_to_cart[data-product_id="' + pid + '"]').attr(
      "data-quantity",
      val,
    );
  });
});
