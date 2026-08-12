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
    btn.addEventListener('click', function () {
      var code = btn.getAttribute('data-copy');
      navigator.clipboard.writeText(code).then(function () {
        var original = btn.textContent;
        btn.textContent = btn.getAttribute('data-copied-label') || 'Copied!';
        setTimeout(function () { btn.textContent = original; }, 1600);
      });
    });
  });
});
