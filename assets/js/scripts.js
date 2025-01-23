document.addEventListener("DOMContentLoaded", function () {
  const container = document.querySelector(".fixed-contact-button");
  const toggleButton = document.getElementById("toggle-contact-options");
  const contactOptions = document.getElementById("contact-options");
  const options = document.querySelectorAll(".contact-option");
  const positionY = toggleButton.getAttribute('data-position-y') || 'bottom';

  toggleButton.addEventListener("click", function () {
    const isActive = container.classList.contains("active");
    
    if (!isActive) {
      contactOptions.style.display = "flex";
      container.classList.add("active");
      if (positionY === 'top') {
        contactOptions.style.bottom = "auto";
        container.classList.add("top");
      }
      
      toggleButton.style.backgroundColor = toggleButton.dataset.bgActive;
      toggleButton.style.color = toggleButton.dataset.textActive;
      
      setTimeout(() => {
        options.forEach((option, index) => {
          const buttonHeight = option.offsetHeight;
          const gap = 10;
          let offset = -1 * (buttonHeight + gap) * (index + 1);
          if (positionY === 'top') {
            offset = offset * -1;
          }
          
          option.style.opacity = "1";
          option.style.transform = `scale(1) translateY(${offset}px)`;
          option.style.transitionDelay = `${index * 0.1}s`;
        });
      }, 10);
    } else {
      container.classList.remove("active");
      
      toggleButton.style.backgroundColor = toggleButton.dataset.bgDefault;
      toggleButton.style.color = toggleButton.dataset.textDefault;
      
      options.forEach((option, index) => {
        option.style.opacity = "0";
        option.style.transform = "scale(0.5) translateY(0)";
        option.style.transitionDelay = `${(options.length - index - 1) * 0.1}s`;
      });

      setTimeout(() => {
        contactOptions.style.display = "none";
        options.forEach(option => {
          option.style.transitionDelay = "0s";
        });
      }, (options.length * 100) + 300);
    }
  });
});
