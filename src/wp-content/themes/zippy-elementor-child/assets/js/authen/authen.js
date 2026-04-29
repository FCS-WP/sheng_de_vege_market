function initZippySignupForm() {
  const forms = document.querySelectorAll("[data-zippy-signup-form]");

  if (!forms.length) {
    return;
  }

  forms.forEach((form) => {
    const password = form.querySelector("#zippy_signup_password");
    const confirmPassword = form.querySelector("#zippy_signup_confirm_password");
    const message = form.querySelector("[data-zippy-password-message]");
    const notice = form
      .closest(".zippy-signup")
      ?.querySelector("[data-zippy-signup-notice]");
    const submitButton = form.querySelector(".zippy-signup__submit");
    const submitText = submitButton?.querySelector(".zippy-signup__submit-text");
    const formControls = form.querySelectorAll("input, button, select, textarea");

    if (!password || !confirmPassword || !message) {
      return;
    }

    const showNotice = (type, html) => {
      if (!notice) {
        return;
      }

      notice.className = `zippy-signup__notice zippy-signup__notice--${type}`;
      notice.innerHTML = `<p>${html}</p>`;
    };

    const setLoading = (isLoading) => {
      form.classList.toggle("is-loading", isLoading);

      formControls.forEach((control) => {
        control.disabled = isLoading;
      });

      if (!submitButton) {
        return;
      }

      submitButton.classList.toggle("is-loading", isLoading);
      submitButton.setAttribute("aria-busy", isLoading ? "true" : "false");

      if (submitText) {
        submitText.textContent = isLoading
          ? submitButton.dataset.loadingText || "Registering..."
          : submitButton.dataset.defaultText || "Register";
      }
    };

    const validatePasswordMatch = () => {
      const passwordsDoNotMatch =
        confirmPassword.value.length > 0 && password.value !== confirmPassword.value;

      confirmPassword.setCustomValidity(
        passwordsDoNotMatch ? "Passwords do not match." : ""
      );
      message.textContent = passwordsDoNotMatch ? "Passwords do not match." : "";

      return !passwordsDoNotMatch;
    };

    password.addEventListener("input", validatePasswordMatch);
    confirmPassword.addEventListener("input", validatePasswordMatch);

    form.addEventListener("submit", async (event) => {
      event.preventDefault();

      if (!validatePasswordMatch() || !form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const ajaxUrl = form.dataset.ajaxUrl;

      if (!ajaxUrl) {
        form.submit();
        return;
      }

      const formData = new FormData(form);
      formData.set("action", form.dataset.ajaxAction || "zippy_signup_register");

      setLoading(true);

      try {
        const response = await fetch(ajaxUrl, {
          method: "POST",
          body: formData,
          credentials: "same-origin",
        });
        const result = await response.json().catch(() => null);

        if (!result) {
          showNotice("error", "Registration request failed. Please refresh and try again.");
          return;
        }

        if (result.success) {
          showNotice("success", result.data.message);
          form.reset();
          message.textContent = "";
          window.setTimeout(() => {
            window.location.href = result.data.redirect_url || "/my-account/";
          }, 2200);
          return;
        }

        showNotice("error", result.data?.message || "Registration failed.");
      } catch (error) {
        showNotice("error", "Something went wrong. Please try again.");
      } finally {
        setLoading(false);
      }
    });
  });
}

function initZippyLoginLoading() {
  const forms = document.querySelectorAll(
    "form.woocommerce-form-login, form.login, .myacc-login-form"
  );

  forms.forEach((form) => {
    form.addEventListener("submit", () => {
      if (typeof form.checkValidity === "function" && !form.checkValidity()) {
        return;
      }

      const submitButton = form.querySelector(
        'button[name="login"], .woocommerce-form-login__submit, .myacc-login-btn'
      );

      if (!submitButton || submitButton.classList.contains("is-loading")) {
        return;
      }

      const originalText = submitButton.textContent.trim();
      const spinner = document.createElement("span");
      const text = document.createElement("span");

      spinner.className = "zippy-login-spinner";
      spinner.setAttribute("aria-hidden", "true");
      text.className = "zippy-login-button-text";
      text.textContent = submitButton.dataset.loadingText || "Logging in...";

      submitButton.dataset.defaultText = originalText;
      submitButton.classList.add("is-loading");
      submitButton.setAttribute("aria-busy", "true");
      submitButton.setAttribute("aria-disabled", "true");
      submitButton.textContent = "";
      submitButton.append(spinner, text);
    });
  });
}

function initZippyHeaderAccountIcon() {
  const isLoggedIn = document.body.classList.contains("logged-in");
  const iconUrl =
    "https://zippy-staging6.theshin.info/wp-content/uploads/2026/04/user.png";
  const adminBarName = document.querySelector(
    "#wp-admin-bar-my-account .display-name"
  );
  const adminBarLogout = document.querySelector("#wp-admin-bar-logout a");
  const userName = adminBarName?.textContent?.trim() || "User";
  const logoutUrl = adminBarLogout?.href || "/my-account/customer-logout/";

  if (!isLoggedIn) {
    return;
  }

  const createAccountMenu = (index) => {
    const menu = document.createElement("div");
    const accountLink = document.createElement("a");
    const icon = document.createElement("img");
    const name = document.createElement("span");
    const dropdown = document.createElement("div");
    const dropdownLink = document.createElement("a");
    const logoutLink = document.createElement("a");

    menu.className = "zippy-header-user-menu";
    menu.dataset.zippyHeaderUser = String(index);

    accountLink.className = "zippy-header-user";
    accountLink.href = "/my-account/";
    accountLink.setAttribute("aria-label", "My account");

    icon.src = iconUrl;
    icon.alt = "";
    icon.loading = "lazy";
    icon.decoding = "async";

    name.className = "zippy-header-user__name";
    name.textContent = userName;

    dropdown.className = "zippy-header-user-menu__dropdown";
    dropdownLink.href = "/my-account/";
    dropdownLink.textContent = "My account";
    logoutLink.href = logoutUrl;
    logoutLink.className = "zippy-header-user-menu__logout";
    logoutLink.textContent = "Logout";

    accountLink.append(icon, name);
    dropdown.append(dropdownLink, logoutLink);
    menu.append(accountLink, dropdown);

    return menu;
  };

  const createMobileAccountItem = (index) => {
    const item = document.createElement("li");
    const accountLink = document.createElement("a");
    const label = document.createElement("span");
    const submenu = document.createElement("ul");
    const accountItem = document.createElement("li");
    const logoutItem = document.createElement("li");
    const accountSubLink = document.createElement("a");
    const logoutSubLink = document.createElement("a");

    item.className =
      "menu-item menu-item-type-custom menu-item-has-children zippy-mobile-user-menu";
    item.dataset.zippyHeaderUser = String(index);

    accountLink.className = "zippy-mobile-user-menu__toggle";
    accountLink.href = "#";
    accountLink.setAttribute("aria-label", "Profile menu");
    accountLink.setAttribute("aria-expanded", "false");

    label.className = "zippy-mobile-user-menu__name";
    label.textContent = "Profile";

    submenu.className = "sub-menu zippy-mobile-user-menu__submenu";
    accountSubLink.href = "/my-account/";
    accountSubLink.textContent = "My Account";
    logoutSubLink.href = logoutUrl;
    logoutSubLink.textContent = "Logout";

    accountLink.append(label);
    accountItem.appendChild(accountSubLink);
    logoutItem.appendChild(logoutSubLink);
    submenu.append(accountItem, logoutItem);
    item.append(accountLink, submenu);

    accountLink.addEventListener("click", (event) => {
      event.preventDefault();

      const isOpen = item.classList.toggle("is-open");
      accountLink.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });

    return item;
  };

  const header = document.querySelector(".elementor-location-header") || document;
  const directTargets = Array.from(
    document.querySelectorAll("#login-button-header, #register-button-header")
  );
  const fallbackTargets = Array.from(
    header.querySelectorAll('a[href*="/my-account"], a[href*="my-account"]')
  )
    .filter(
      (link) =>
        !link.closest("#menu-mobile-main-menu, .jkit-menu") &&
        /log\s*in|sign\s*up/i.test(link.textContent || "")
    )
    .map(
      (link) =>
        link.closest(".elementor-widget-button, .elementor-element") || link
  );
  const loginButtons = Array.from(new Set([...directTargets, ...fallbackTargets]));

  loginButtons.forEach((loginButton) => {
    loginButton.classList.add("is-hidden-after-login");
  });

  if (loginButtons.length) {
    const existingMenu = header.querySelector(".zippy-header-user-menu");

    if (existingMenu) {
      existingMenu.hidden = false;
    } else {
      const anchorTarget = loginButtons[loginButtons.length - 1];
      anchorTarget.insertAdjacentElement("afterend", createAccountMenu(0));
    }
  }

  const mobileMenus = document.querySelectorAll(
    "#menu-mobile-main-menu, .jkit-menu"
  );

  mobileMenus.forEach((menu, index) => {
    const authItems = Array.from(menu.querySelectorAll(":scope > li")).filter(
      (item) => {
        const link = item.querySelector(":scope > a");
        const href = link?.getAttribute("href") || "";
        const text = link?.textContent || "";

        return (
          (/my-account/i.test(href) && /log\s*in|login|登入/i.test(text)) ||
          (/register/i.test(href) && /register|sign\s*up|立即注册/i.test(text))
        );
      }
    );

    if (!authItems.length || menu.querySelector(":scope > .zippy-mobile-user-menu")) {
      return;
    }

    authItems.forEach((item) => {
      item.classList.add("is-hidden-after-login");
    });

    menu.appendChild(createMobileAccountItem(index));
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initZippySignupForm();
  initZippyLoginLoading();
  initZippyHeaderAccountIcon();
});
