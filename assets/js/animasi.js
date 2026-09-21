/* Modul animasi GSAP — entrance & scroll-reveal untuk SIMAK.
   Dimuat setelah gsap (+ ScrollTrigger opsional) dari CDN.
   Aman offline: tidak jalan bila `window.gsap` tidak ada.
   Menghormati `prefers-reduced-motion`. */
(function () {
  'use strict';

  if (!window.gsap) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var hasScrollTrigger = !!window.ScrollTrigger;
  if (hasScrollTrigger) gsap.registerPlugin(ScrollTrigger);

  // Judul halaman: fade + geser ke bawah sedikit
  var titles = document.querySelectorAll('.m3-page-title, .m3-headline-small, .m3-auth__title');
  if (titles.length) {
    gsap.from(titles, { y: 18, autoAlpha: 0, duration: 0.55, ease: 'power2.out', overwrite: 'auto' });
  }

  // Kartu statistik: muncul berurutan
  var stats = document.querySelectorAll('.m3-stat');
  if (stats.length) {
    gsap.from(stats, {
      y: 24, autoAlpha: 0, duration: 0.5, ease: 'power2.out',
      stagger: 0.08, delay: 0.1, overwrite: 'auto', clearProps: 'transform,opacity,visibility'
    });
  }

  // Banner info/error: fade-in lembut
  var banners = document.querySelectorAll('.m3-banner');
  if (banners.length) {
    gsap.from(banners, { autoAlpha: 0, duration: 0.4, ease: 'power1.out', clearProps: 'opacity,visibility' });
  }

  // Kartu konten: sembunyikan dulu, reveal saat masuk viewport.
  // batch otomatis memicu kartu yang sudah terlihat saat load.
  var cards = gsap.utils.toArray('.m3-card, .m3-table-wrap');
  if (cards.length) {
    if (hasScrollTrigger) {
      gsap.set(cards, { y: 26, autoAlpha: 0 });
      ScrollTrigger.batch(cards, {
        start: 'top 92%',
        once: true,
        onEnter: function (els) {
          gsap.to(els, {
            y: 0, autoAlpha: 1, duration: 0.55, ease: 'power2.out',
            stagger: 0.07, overwrite: 'auto', clearProps: 'transform,opacity,visibility'
          });
        }
      });
    } else {
      gsap.from(cards, {
        y: 26, autoAlpha: 0, duration: 0.55, ease: 'power2.out',
        stagger: 0.07, overwrite: 'auto', clearProps: 'transform,opacity,visibility'
      });
    }
  }
})();
