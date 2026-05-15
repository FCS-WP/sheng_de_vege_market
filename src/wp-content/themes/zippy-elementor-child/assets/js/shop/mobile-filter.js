function initZippyMobileNavigation() {
  const hamburger = document.getElementById("mobile-hamburger-menu");

  if (!hamburger) {
    return;
  }

  const stopNativeMenuToggle = (event) => {
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
  };

  const setCategoryLinkUrl = (value) => {
    const filterForm = document.querySelector(".zippy-shop-filter__search");
    const url = new URL(filterForm?.action || window.location.href, window.location.origin);

    if (value) {
      url.searchParams.set("post_type", "product");
      url.searchParams.set("product_cat", value);
    } else {
      url.searchParams.delete("product_cat");
    }

    return url.toString();
  };

  const findHeaderLink = (selectors, textPattern) => {
    for (const selector of selectors) {
      const link = document.querySelector(selector);

      if (link?.href) {
        return link.href;
      }
    }

    const header = document.querySelector(".elementor-location-header") || document;
    const link = Array.from(header.querySelectorAll("a")).find((item) =>
      textPattern.test(item.textContent || "")
    );

    return link?.href || "";
  };

  const createLinkItem = (label, href, className = "", iconName = "") => {
    const item = document.createElement("li");
    const link = document.createElement("a");

    item.className = `menu-item menu-item-type-custom ${className}`.trim();
    link.href = href;

    if (iconName) {
      const icon = document.createElement("span");
      const text = document.createElement("span");

      icon.className = `zippy-mobile-nav-icon zippy-mobile-nav-icon--${iconName}`;
      icon.setAttribute("aria-hidden", "true");
      text.textContent = label;
      link.append(icon, text);
    } else {
      link.textContent = label;
    }

    item.appendChild(link);

    return item;
  };

  const createProductCategoryMenu = () => {
    const categoryControls = Array.from(
      document.querySelectorAll(".zippy-shop-filter__category")
    );

    if (!categoryControls.length) {
      return null;
    }

    const item = document.createElement("li");
    const title = document.createElement("span");
    const list = document.createElement("ul");
    const seeMoreItem = document.createElement("li");
    const seeMoreButton = document.createElement("button");

    item.className = "menu-item menu-item-type-custom zippy-mobile-product-categories";
    title.className = "zippy-mobile-product-categories__title";
    title.textContent = "Product Category";
    list.className = "zippy-mobile-product-categories__list";

    categoryControls.forEach((categoryControl, index) => {
      const input = categoryControl.querySelector('input[name="zippy_filter_category"]');
      const label = categoryControl.textContent.trim().replace(/\s+/g, " ");

      if (!input || !label) {
        return;
      }

      const categoryItem = document.createElement("li");
      const categoryLink = document.createElement("a");

      categoryItem.className = "menu-item menu-item-type-taxonomy";
      categoryLink.href = setCategoryLinkUrl(input.value);
      categoryLink.textContent = label;

      if (index >= 12) {
        categoryItem.classList.add("is-extra-category");
        categoryItem.hidden = true;
      }

      if (input.checked) {
        categoryItem.classList.add("current-menu-item");
      }

      categoryItem.appendChild(categoryLink);
      list.appendChild(categoryItem);
    });

    item.append(title, list);

    if (categoryControls.length > 12) {
      seeMoreItem.className = "zippy-mobile-category-more";
      seeMoreButton.type = "button";
      seeMoreButton.textContent = "See More";
      seeMoreButton.setAttribute("aria-expanded", "false");
      seeMoreItem.appendChild(seeMoreButton);
      item.appendChild(seeMoreItem);

      seeMoreButton.addEventListener("click", () => {
        const isOpen = item.classList.toggle("is-showing-more");

        item.querySelectorAll(".is-extra-category").forEach((categoryItem) => {
          categoryItem.hidden = !isOpen;
        });

        seeMoreButton.textContent = isOpen ? "See Less" : "See More";
        seeMoreButton.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });
    }

    return item;
  };

  const createDividerItem = () => {
    const item = document.createElement("li");

    item.className = "zippy-mobile-nav-divider";
    item.setAttribute("aria-hidden", "true");

    return item;
  };

  const createAccountMenu = () => {
    const isLoggedIn = document.body.classList.contains("logged-in");
    const loginUrl =
      findHeaderLink(["#login-button-header a", "#login-button-header"], /log\s*in|login/i) ||
      "/my-account/";
    const signupUrl =
      findHeaderLink(
        ["#register-button-header a", "#register-button-header"],
        /sign\s*up|register/i
      ) || "/my-account/#myacc-register";

    if (!isLoggedIn) {
      const fragment = document.createDocumentFragment();

      fragment.append(
        createLinkItem("Login", loginUrl, "zippy-mobile-auth-link"),
        createLinkItem("Sign Up", signupUrl, "zippy-mobile-auth-link")
      );

      return fragment;
    }

    const adminBarName = document.querySelector(
      "#wp-admin-bar-my-account .display-name"
    );
    const adminBarLogout = document.querySelector("#wp-admin-bar-logout a");
    const headerUserName = document.querySelector(".zippy-header-user__name");
    const headerLogout = document.querySelector(".zippy-header-user-menu__logout");
    const userName =
      adminBarName?.textContent?.trim() ||
      headerUserName?.textContent?.trim() ||
      "User";
    const logoutUrl =
      adminBarLogout?.href || headerLogout?.href || "/my-account/customer-logout/";
    const iconUrl =
      "https://zippy-staging6.theshin.info/wp-content/uploads/2026/04/user.png";
    const item = document.createElement("li");
    const toggle = document.createElement("a");
    const icon = document.createElement("img");
    const name = document.createElement("span");
    const submenu = document.createElement("ul");
    const logoutItem = document.createElement("li");
    const logoutLink = document.createElement("a");

    item.className =
      "menu-item menu-item-type-custom menu-item-has-children zippy-mobile-account-menu";
    toggle.className = "zippy-mobile-account-menu__toggle";
    toggle.href = "#";
    toggle.setAttribute("aria-expanded", "false");

    icon.src = iconUrl;
    icon.alt = "";
    icon.loading = "lazy";
    icon.decoding = "async";

    name.className = "zippy-mobile-account-menu__name";
    name.textContent = userName;

    submenu.className = "sub-menu zippy-mobile-account-menu__submenu";
    logoutLink.href = logoutUrl;
    logoutLink.textContent = "Logout";

    toggle.append(icon, name);
    logoutItem.appendChild(logoutLink);
    submenu.appendChild(logoutItem);
    item.append(toggle, submenu);

    return item;
  };

  const existingDrawer = document.querySelector("[data-zippy-mobile-nav-drawer]");
  const drawer = existingDrawer || document.createElement("nav");
  const existingOverlay = document.querySelector(".zippy-mobile-nav-overlay");
  const overlay = existingOverlay || document.createElement("button");

  overlay.type = "button";
  overlay.className = "zippy-mobile-nav-overlay";
  overlay.setAttribute("aria-label", "Close menu");
  drawer.className = "zippy-mobile-nav-drawer";
  drawer.dataset.zippyMobileNavDrawer = "true";
  drawer.setAttribute("aria-hidden", "true");
  drawer.setAttribute("aria-label", "Mobile navigation");
  hamburger.setAttribute("aria-expanded", "false");

  if (!existingOverlay) {
    document.body.appendChild(overlay);
  }

  if (!existingDrawer) {
    document.body.appendChild(drawer);
  }

  if (!drawer.querySelector(".zippy-mobile-nav-menu")) {
    const menu = document.createElement("ul");
    const categoryMenu = createProductCategoryMenu();

    menu.className = "zippy-mobile-nav-menu";

    if (categoryMenu) {
      menu.appendChild(categoryMenu);
    }

    menu.append(
      createDividerItem(),
      createLinkItem("Go to home", "/", "zippy-mobile-home-link", "home"),
      createLinkItem("Need help", "/contact-us", "zippy-mobile-help-link", "help"),
      createDividerItem(),
      createAccountMenu()
    );
    drawer.textContent = "";
    drawer.appendChild(menu);
    document.body.appendChild(drawer);
  }

  const closeMenu = () => {
    document.body.classList.remove("zippy-mobile-menu-open");
    drawer.classList.remove("is-open");
    overlay.classList.remove("is-open");
    drawer.setAttribute("aria-hidden", "true");
    hamburger.setAttribute("aria-expanded", "false");
  };

  const openMenu = () => {
    document.body.classList.add("zippy-mobile-menu-open");
    drawer.classList.add("is-open");
    overlay.classList.add("is-open");
    drawer.setAttribute("aria-hidden", "false");
    hamburger.setAttribute("aria-expanded", "true");
  };

  const toggleMenu = (event) => {
    stopNativeMenuToggle(event);

    if (drawer.classList.contains("is-open")) {
      closeMenu();
      return;
    }

    openMenu();
  };

  const handleHamburgerClick = (event) => {
    if (!event.target.closest?.("#mobile-hamburger-menu")) {
      return;
    }

    toggleMenu(event);
  };

  const handleHamburgerPointerEvent = (event) => {
    if (!event.target.closest?.("#mobile-hamburger-menu")) {
      return;
    }

    event.stopPropagation();
    event.stopImmediatePropagation();
  };

  Array.from(drawer.querySelectorAll(".menu-item"))
    .filter(
      (item) =>
        item.classList.contains("menu-item-has-children") ||
        item.querySelector(":scope > .sub-menu")
    )
    .forEach((item) => {
      const toggle = item.querySelector(":scope > a");
      const submenu = item.querySelector(":scope > .sub-menu");

      if (!toggle || !submenu) {
        return;
      }

      item.classList.add("zippy-mobile-nav-parent");
      toggle.setAttribute(
        "aria-expanded",
        item.classList.contains("current-menu-ancestor") ? "true" : "false"
      );

      toggle.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();

        const isOpen = item.classList.toggle("is-open");
        toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });
    });

  document.addEventListener("click", handleHamburgerClick, true);
  ["touchstart", "pointerdown", "mousedown"].forEach((eventName) => {
    document.addEventListener(eventName, handleHamburgerPointerEvent, true);
  });
  overlay.addEventListener("click", closeMenu);

  drawer.addEventListener("click", (event) => {
    const link = event.target.closest("a");
    const isSubmenuToggle =
      link?.parentElement?.classList.contains("zippy-mobile-nav-parent") &&
      link.nextElementSibling?.classList.contains("sub-menu");

    if (link && !isSubmenuToggle) {
      closeMenu();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && drawer.classList.contains("is-open")) {
      closeMenu();
    }
  });
}

document.addEventListener("DOMContentLoaded", initZippyMobileNavigation);
