document.addEventListener('DOMContentLoaded', function () {
  var burger = document.querySelector('.burger');
  var nav = document.querySelector('.main-nav');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      nav.classList.toggle('open-mobile');
      nav.style.display = nav.classList.contains('open-mobile') ? 'flex' : '';
      nav.style.flexDirection = 'column';
      nav.style.position = 'absolute';
      nav.style.top = '72px';
      nav.style.insetInlineStart = '0';
      nav.style.insetInlineEnd = '0';
      nav.style.background = 'var(--navy-900)';
      nav.style.padding = '10px 20px 20px';
    });
  }

  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var code = btn.getAttribute('data-copy');
      var gotoUrl = btn.getAttribute('data-goto');

      function proceed() {
        var original = btn.getAttribute('data-original-html') || btn.innerHTML;
        btn.setAttribute('data-original-html', original);
        btn.innerHTML = btn.getAttribute('data-copied-label') || 'Copied!';
        btn.classList.add('is-copied');
        if (gotoUrl) {
          window.open(gotoUrl, '_blank', 'noopener');
        }
        setTimeout(function () {
          btn.innerHTML = original;
          btn.classList.remove('is-copied');
        }, 1800);
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(proceed).catch(proceed);
      } else {
        proceed();
      }
    });
  });
});
