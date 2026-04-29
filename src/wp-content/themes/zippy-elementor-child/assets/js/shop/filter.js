function initZippyShopFilter() {
  const widgets = document.querySelectorAll("[data-zippy-shop-filter]");

  widgets.forEach((widget) => {
    const form = widget.querySelector(".zippy-shop-filter__search");
    const drawer = widget.querySelector("[data-zippy-filter-drawer]");
    const categoryInput = widget.querySelector("[data-zippy-category-input]");
    const openButton = widget.querySelector("[data-zippy-filter-open]");
    const closeButtons = widget.querySelectorAll("[data-zippy-filter-close]");
    const applyButton = widget.querySelector("[data-zippy-filter-apply]");
    const resetButton = widget.querySelector("[data-zippy-filter-reset]");

    if (!form || !drawer || !categoryInput || !openButton) {
      return;
    }

    const openDrawer = () => {
      drawer.classList.add("is-open");
      drawer.setAttribute("aria-hidden", "false");
      document.body.classList.add("zippy-filter-open");
    };

    const closeDrawer = () => {
      drawer.classList.remove("is-open");
      drawer.setAttribute("aria-hidden", "true");
      document.body.classList.remove("zippy-filter-open");
    };

    const submitWithSelectedCategory = () => {
      const selectedCategory = widget.querySelector(
        'input[name="zippy_filter_category"]:checked'
      );

      categoryInput.value = selectedCategory ? selectedCategory.value : "";
      form.submit();
    };

    openButton.addEventListener("click", openDrawer);
    closeButtons.forEach((button) => button.addEventListener("click", closeDrawer));

    applyButton?.addEventListener("click", submitWithSelectedCategory);

    resetButton?.addEventListener("click", () => {
      const allProducts = widget.querySelector('input[name="zippy_filter_category"][value=""]');

      if (allProducts) {
        allProducts.checked = true;
      }

      categoryInput.value = "";
      form.submit();
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && drawer.classList.contains("is-open")) {
        closeDrawer();
      }
    });
  });
}

document.addEventListener("DOMContentLoaded", initZippyShopFilter);
