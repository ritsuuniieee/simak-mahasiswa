/* Interaksi Material 3 — pengganti bundle Bootstrap.
   Tanpa dependensi, cukup ~2 KB. */
(function () {
  'use strict';

  /* ---- Navigation drawer (layar kecil) ---- */
  var drawer = document.querySelector('[data-m3-drawer]');
  var scrim = document.querySelector('[data-m3-scrim]');

  function closeDrawer() {
    if (!drawer) return;
    drawer.classList.remove('is-open');
    if (scrim) scrim.classList.remove('is-open');
  }

  document.querySelectorAll('[data-m3-drawer-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!drawer) return;
      var open = drawer.classList.toggle('is-open');
      if (scrim) scrim.classList.toggle('is-open', open);
    });
  });
  if (scrim) scrim.addEventListener('click', closeDrawer);

  /* ---- Dialog ---- */
  document.querySelectorAll('[data-m3-dialog-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dlg = document.getElementById(btn.getAttribute('data-m3-dialog-open'));
      if (dlg && typeof dlg.showModal === 'function') dlg.showModal();
    });
  });
  document.querySelectorAll('[data-m3-dialog-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dlg = btn.closest('dialog');
      if (dlg) dlg.close();
    });
  });

  /* ---- Tutup banner flash ---- */
  document.querySelectorAll('[data-m3-banner-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var banner = btn.closest('.m3-banner');
      if (banner) banner.remove();
    });
  });

  /* ---- Tabel responsif: suntik label kolom untuk mode kartu di mobile ----
     CSS di bawah (max-width 640px) menampilkan tiap baris sebagai kartu
     dengan label dari atribut data-label ini. Kolom aksi & sel colspan
     (baris kosong) tidak diberi label. */
  document.querySelectorAll('table.m3-table').forEach(function (table) {
    var headCells = table.querySelectorAll('thead th');
    if (!headCells.length) return;
    var labels = [];
    headCells.forEach(function (th) { labels.push(th.textContent.trim()); });
    table.querySelectorAll('tbody tr').forEach(function (tr) {
      var cells = tr.cells;
      for (var i = 0; i < cells.length; i++) {
        var td = cells[i];
        if (td.hasAttribute('colspan')) continue;
        if (td.classList.contains('m3-td-actions')) continue;
        if (labels[i]) td.setAttribute('data-label', labels[i]);
      }
    });
  });

  /* ---- Esc menutup drawer ---- */
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape') closeDrawer();
  });
})();
