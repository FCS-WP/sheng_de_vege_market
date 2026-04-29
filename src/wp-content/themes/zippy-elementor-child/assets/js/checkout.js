function initZippyCheckoutDeliveryArea() {
  const wooJQuery = window.jQuery;

  if (!wooJQuery) {
    return;
  }

  const $body = wooJQuery(document.body);
  const updateCheckout = () => {
    window.setTimeout(() => {
      $body.trigger("update_checkout");
    }, 100);
  };

  $body
    .off("change.zippyDeliveryArea", 'input[name="zippy_priority_delivery_area"]')
    .on(
      "change.zippyDeliveryArea",
      'input[name="zippy_priority_delivery_area"]',
      updateCheckout
    );

  document.removeEventListener("click", handleZippyDeliveryAreaClick, true);
  document.addEventListener("click", handleZippyDeliveryAreaClick, true);

  function handleZippyDeliveryAreaClick(event) {
    const field = event.target.closest("#zippy_priority_delivery_area_field");

    if (!field) {
      return;
    }

    updateCheckout();
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initZippyCheckoutDeliveryArea);
} else {
  initZippyCheckoutDeliveryArea();
}
