const HOME_BANNER_SECONDARY_BUTTON_TEXT = "View Delivery Details";
const HOME_BANNER_SECONDARY_BUTTON_URL = "/contact-us";

function initHomeSliderBannerButtons() {
  const banner = document.getElementById("home-slider-banner");

  if (!banner) {
    return;
  }

  const slideContents = banner.querySelectorAll(".swiper-slide-contents");

  slideContents.forEach((slideContent) => {
    const primaryButton = slideContent.querySelector(".elementor-slide-button");

    if (
      !primaryButton ||
      slideContent.querySelector(".zippy-home-banner-secondary-button")
    ) {
      return;
    }

    let buttonGroup = slideContent.querySelector(".zippy-home-banner-buttons");

    primaryButton.classList.add("zippy-home-banner-primary-button");

    if (!buttonGroup) {
      buttonGroup = document.createElement("div");
      buttonGroup.className = "zippy-home-banner-buttons";
      primaryButton.parentNode.insertBefore(buttonGroup, primaryButton);
      buttonGroup.appendChild(primaryButton);
    }

    const secondaryButton = document.createElement("div");
    secondaryButton.className =
      "elementor-button elementor-slide-button elementor-size-xs zippy-home-banner-secondary-button";
    secondaryButton.setAttribute("role", "link");
    secondaryButton.setAttribute("tabindex", "0");
    secondaryButton.textContent = HOME_BANNER_SECONDARY_BUTTON_TEXT;

    const goToSecondaryButtonUrl = (event) => {
      event.preventDefault();
      event.stopPropagation();
      window.location.href = HOME_BANNER_SECONDARY_BUTTON_URL;
    };

    secondaryButton.addEventListener("click", goToSecondaryButtonUrl);
    secondaryButton.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        goToSecondaryButtonUrl(event);
      }
    });

    buttonGroup.appendChild(secondaryButton);
  });
}

document.addEventListener("DOMContentLoaded", initHomeSliderBannerButtons);
window.addEventListener("load", initHomeSliderBannerButtons);
