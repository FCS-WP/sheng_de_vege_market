function initZippyCheckoutDeliveryArea() {
  const checkoutForm = document.querySelector("form.checkout");

  if (!checkoutForm || typeof jQuery === "undefined") {
    return;
  }

  checkoutForm.addEventListener("change", (event) => {
    if (event.target.name !== "zippy_priority_delivery_area") {
      return;
    }

    jQuery(document.body).trigger("update_checkout");
  });
}

document.addEventListener("DOMContentLoaded", initZippyCheckoutDeliveryArea);
