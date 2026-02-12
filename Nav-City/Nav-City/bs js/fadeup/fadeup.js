(function () {
  'use strict';

  // Fade-up script
  const elements = document.querySelectorAll('.fade-up');
  let windowHeight = window.innerHeight;

  function checkPosition() {
    elements.forEach(element => {
      const positionFromTop = element.getBoundingClientRect().top;
      const triggerPoint = windowHeight * 0.75; // 80% of the screen height
      if (positionFromTop <= triggerPoint && !element.classList.contains('viewed')) {
        element.classList.add('viewed', 'show');
      }
    });
  }

  window.addEventListener('scroll', checkPosition);
  window.addEventListener('resize', () => {
    windowHeight = window.innerHeight;
    checkPosition();
  });

  checkPosition();
})();
