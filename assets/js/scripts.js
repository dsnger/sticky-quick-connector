document.addEventListener("DOMContentLoaded", function () {
  const container = document.querySelector(".fixed-contact-button");
  const toggleButton = document.getElementById("toggle-contact-options");
  const options = document.querySelectorAll(".contact-option");

  // Determine whether the fixed button is closer to the top or bottom.
  function getButtonVerticalPosition() {
    const mainButtonRect = container.getBoundingClientRect();
    const distanceFromTop = mainButtonRect.top;
    const distanceFromBottom = window.innerHeight - mainButtonRect.bottom;
    return distanceFromTop < distanceFromBottom ? "top" : "bottom";
  }

  // Update the positions of the options based on the current vertical position.
  function updateOptionsPositions() {
    if (!container.classList.contains("active")) return;

    const positionY = getButtonVerticalPosition();
    // Update container class to adjust layout via CSS if needed.
    if (positionY === "top") {
      container.classList.add("top");
    } else {
      container.classList.remove("top");
    }

    const mainButtonRect = container.getBoundingClientRect();
    const gap = 14;

    options.forEach((option, index) => {
      const optionRect = option.getBoundingClientRect();
      const optionFullHeight = optionRect.height * 4;
      let offset;

      if (positionY === "top") {
        // Expand options downward if the container is near the top.
        offset = (mainButtonRect.height + gap) + (optionFullHeight + gap) * index;
      } else {
        // Expand options upward if the container is near the bottom.
        offset = -((optionFullHeight + gap) * index) - (mainButtonRect.height + gap);
      }

      option.style.setProperty("--translateY", `${offset}px`);
      option.style.opacity = "1";
      option.style.transform = `translate(-50%, ${offset}px) scale(1)`;
      option.style.transitionDelay = `${index * 0.1}s`;
    });
  }

  // Listen for clicks to toggle the contact options menu.
  toggleButton.addEventListener("click", function () {
    const positionY = getButtonVerticalPosition();
    const mainButtonRect = container.getBoundingClientRect();

    if (!container.classList.contains("active")) {
      container.classList.add("active");

      if (positionY === "top") {
        container.classList.add("top");
      } else {
        container.classList.remove("top");
      }

      toggleButton.style.backgroundColor = toggleButton.dataset.bgActive;
      toggleButton.style.color = toggleButton.dataset.textActive;

      setTimeout(() => {
        options.forEach((option, index) => {
          const optionRect = option.getBoundingClientRect();
          const gap = 14;
          const optionFullHeight = optionRect.height * 4;
          let offset;

          if (positionY === "top") {
            offset = (mainButtonRect.height + gap) + (optionFullHeight + gap) * index;
          } else {
            offset = -((optionFullHeight + gap) * index) - (mainButtonRect.height + gap);
          }

          option.style.setProperty("--translateY", `${offset}px`);
          option.style.opacity = "1";
          option.style.transform = `translate(-50%, ${offset}px) scale(1)`;
          option.style.transitionDelay = `${index * 0.1}s`;
        });
      }, 10);
    } else {
      container.classList.remove("active");

      toggleButton.style.backgroundColor = toggleButton.dataset.bgDefault;
      toggleButton.style.color = toggleButton.dataset.textDefault;

      options.forEach((option, index) => {
        option.style.opacity = "0";
        option.style.transform = "translate(-50%, 0) scale(0.25)";
        option.style.transitionDelay = `${(options.length - index - 1) * 0.1}s`;
      });

      setTimeout(() => {
        options.forEach(option => {
          option.style.transitionDelay = "0s";
        });
      }, (options.length * 100) + 300);
    }
  });

  // Recalculate the vertical position on window resize if the menu is active.
  window.addEventListener("resize", updateOptionsPositions);
});
