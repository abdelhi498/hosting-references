document.addEventListener('DOMContentLoaded', function () {
  var popup = document.getElementById('promo-popup');
  if (!popup || popup.classList.contains('is-preview')) return;

  var delay = Math.max(0, parseInt(popup.getAttribute('data-delay'), 10) || 0) * 1000;
  var frequency = popup.getAttribute('data-frequency') || 'once_session';
  var template = popup.getAttribute('data-template') || 'center';
  var STORAGE_KEY = 'hr_promo_popup_last_shown';

  function alreadyShown() {
    if (frequency === 'every_visit') return false;
    if (frequency === 'once_session') {
      return sessionStorage.getItem(STORAGE_KEY) === '1';
    }
    if (frequency === 'once_day') {
      var last = parseInt(localStorage.getItem(STORAGE_KEY), 10);
      return last && (Date.now() - last) < 24 * 60 * 60 * 1000;
    }
    return false;
  }

  function markShown() {
    if (frequency === 'once_session') {
      sessionStorage.setItem(STORAGE_KEY, '1');
    } else if (frequency === 'once_day') {
      localStorage.setItem(STORAGE_KEY, String(Date.now()));
    }
  }

  function openPopup() {
    popup.hidden = false;
    // A plain rAF here can stay paused indefinitely on a backgrounded/
    // inactive tab (Chrome pauses rAF when document.visibilityState isn't
    // "visible"), leaving the card stuck at opacity:0. setTimeout doesn't
    // have that pause behavior, so use it to guarantee the transition
    // class is applied even if the tab isn't in the foreground yet.
    setTimeout(function () {
      popup.classList.add('is-open');
    }, 20);
    // Only the centered modal blocks the page (backdrop + scroll lock) —
    // the corner and banner templates are non-blocking notifications.
    if (template === 'center') {
      document.body.classList.add('promo-popup-locked');
    }
    markShown();
  }

  function closePopup() {
    popup.classList.remove('is-open');
    document.body.classList.remove('promo-popup-locked');
    setTimeout(function () {
      popup.hidden = true;
    }, 250);
  }

  if (!alreadyShown()) {
    setTimeout(openPopup, delay);
  }

  popup.querySelectorAll('[data-popup-close]').forEach(function (el) {
    el.addEventListener('click', function () {
      closePopup();
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !popup.hidden) {
      closePopup();
    }
  });
});
