<?php

$base = '/banksampah/';

$uri = $_SERVER['REQUEST_URI'] ?? '';
$in  = fn($folder) => strpos($uri, $base . $folder . '/') !== false;

$isTransaksi = $in('transaksi');
$isNasabah   = $in('nasabah');
$isKategori  = $in('kategori');
$isPetugas   = $in('petugas');
$isHome      = !($isTransaksi || $isNasabah || $isKategori || $isPetugas);

$active = fn($cond) => $cond ? ' active' : '';
$subActive = fn($file) => strpos($uri, $base . 'transaksi/' . $file) !== false ? ' active' : '';
?>
<style>
  :root {
    --sb-bg: #ffffff;
    --sb-text: #14161a;
    --sb-muted: #9aa0ab;
    --sb-hover: #f3f4f8;
    --sb-active-bg: #eceeff;
    --sb-active-fg: #4453d6;
    --sb-line: #e6e8ee;
    --sb-w: 240px;
    --sb-w-min: 72px;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --sb-bg: #1b1d22;
      --sb-text: #f1f2f5;
      --sb-muted: #7d8391;
      --sb-hover: #262930;
      --sb-active-bg: #2a2f5c;
      --sb-active-fg: #aab3ff;
      --sb-line: #2d3038;
    }
  }

  .app { display: flex; min-height: 100vh; gap: 16px; padding: 16px; box-sizing: border-box; }
  .content { flex: 1; min-width: 0; }

  .sidebar {
    position: sticky; top: 16px;
    width: var(--sb-w); height: calc(100vh - 32px);
    background: var(--sb-bg); color: var(--sb-text);
    border-radius: 24px; padding: 16px 14px;
    box-sizing: border-box; flex-shrink: 0;
    display: flex; flex-direction: column;
    font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
    transition: width .25s ease;
    overflow: hidden;
  }
  .sidebar.collapsed { width: var(--sb-w-min); }

  .sb-top { display: flex; justify-content: flex-end; height: 28px; }
  .sb-toggle {
    width: 26px; height: 26px; border-radius: 50%;
    border: 1px solid var(--sb-line); background: var(--sb-bg); color: var(--sb-text);
    display: grid; place-items: center; cursor: pointer; padding: 0;
  }
  .sb-toggle svg { transition: transform .25s ease; }
  .sidebar.collapsed .sb-toggle svg { transform: rotate(180deg); }
  .sidebar.collapsed .sb-top { justify-content: center; }

  .sb-label { font-size: 12px; color: var(--sb-muted); margin: 14px 10px 8px; white-space: nowrap; }
  .sidebar.collapsed .sb-label { visibility: hidden; height: 0; margin: 10px 0; }

  .sb-nav { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 2px; }

  .sb-item {
    display: flex; align-items: center; gap: 14px;
    width: 100%; padding: 11px 12px; border-radius: 12px;
    color: var(--sb-text); text-decoration: none;
    font-size: 14px; font-weight: 600; white-space: nowrap;
    background: none; border: 0; cursor: pointer; font-family: inherit;
    box-sizing: border-box; text-align: left;
  }
  .sb-item:hover { background: var(--sb-hover); }
  .sb-item.active { background: var(--sb-active-bg); color: var(--sb-active-fg); }
  .sb-item:focus-visible, .sb-toggle:focus-visible { outline: 2px solid var(--sb-active-fg); outline-offset: 2px; }
  .sb-item svg.ic { flex-shrink: 0; }
  .sb-item .txt { flex: 1; }
  .sb-item .caret { transition: transform .2s ease; color: var(--sb-muted); flex-shrink: 0; }
  .has-sub.open > .sb-item .caret { transform: rotate(180deg); }

  .sidebar.collapsed .sb-item { justify-content: center; padding: 11px 0; }
  .sidebar.collapsed .txt, .sidebar.collapsed .caret { display: none; }

  .sb-sub {
    list-style: none; margin: 2px 0 4px 26px; padding: 0 0 0 14px;
    border-left: 1px solid var(--sb-line);
    display: none; flex-direction: column; gap: 2px;
  }
  .has-sub.open > .sb-sub { display: flex; }
  .sidebar.collapsed .sb-sub { display: none !important; }
  .sb-sub a {
    display: block; padding: 8px 12px; border-radius: 10px;
    font-size: 13.5px; font-weight: 500; color: var(--sb-muted); text-decoration: none;
  }
  .sb-sub a:hover { background: var(--sb-hover); color: var(--sb-text); }
  .sb-sub a.active { color: var(--sb-active-fg); background: var(--sb-active-bg); font-weight: 600; }

  @media (max-width: 720px) {
    .sidebar { width: var(--sb-w-min); }
    .sidebar .txt, .sidebar .caret, .sidebar .sb-label { display: none; }
    .sidebar .sb-item { justify-content: center; padding: 11px 0; }
    .sidebar .sb-sub { display: none !important; }
    .sb-top { display: none; }
  }
</style>

<aside class="sidebar" id="sidebar">
  <div class="sb-top">
    <button class="sb-toggle" id="sbToggle" type="button" aria-label="Ciutkan sidebar">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
  </div>

  <div class="sb-label">Menu</div>
  <ul class="sb-nav">

    <li>
      <a class="sb-item<?= $active($isHome) ?>" href="<?= $base ?>index.php" title="Home">
        <svg class="ic" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
        <span class="txt">Home</span>
      </a>
    </li>

    <li class="has-sub<?= $isTransaksi ? ' open' : '' ?>">
      <button class="sb-item<?= $active($isTransaksi) ?>" type="button" id="trxBtn" title="Transaksi" aria-expanded="<?= $isTransaksi ? 'true' : 'false' ?>">
        <svg class="ic" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="3"/><path d="M2.5 10h19"/><path d="M7 15h3"/></svg>
        <span class="txt">Transaksi</span>
        <svg class="caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <ul class="sb-sub">
        <li><a class="<?= trim($subActive('setor')) ?>"   href="<?= $base ?>transaksi/setor.php">Setor</a></li>
        <li><a class="<?= trim($subActive('tarik')) ?>"   href="<?= $base ?>transaksi/tarik.php">Tarik</a></li>
        <li><a class="<?= trim($subActive('riwayat')) ?>" href="<?= $base ?>transaksi/riwayat.php">Riwayat</a></li>
      </ul>
    </li>

    <li>
      <a class="sb-item<?= $active($isNasabah) ?>" href="<?= $base ?>nasabah/index.php" title="Nasabah">
        <svg class="ic" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.6a3.5 3.5 0 010 6.8"/><path d="M18 14.3c2 .7 3.5 2.4 3.5 5.7"/></svg>
        <span class="txt">Nasabah</span>
      </a>
    </li>

    <li>
      <a class="sb-item<?= $active($isKategori) ?>" href="<?= $base ?>kategori/index.php" title="Kategori">
        <svg class="ic" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12.5V4a1 1 0 011-1h8.5a1 1 0 01.7.3l7.5 7.5a1 1 0 010 1.4l-8.5 8.5a1 1 0 01-1.4 0l-7.5-7.5a1 1 0 01-.3-.7z"/><circle cx="8" cy="8" r="1.4"/></svg>
        <span class="txt">Kategori</span>
      </a>
    </li>

    <li>
      <a class="sb-item<?= $active($isPetugas) ?>" href="<?= $base ?>petugas/index.php" title="Petugas">
        <svg class="ic" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
        <span class="txt">Petugas</span>
      </a>
    </li>

  </ul>
</aside>

<script>
(function () {
  var sb = document.getElementById('sidebar');
  var toggle = document.getElementById('sbToggle');
  var trxBtn = document.getElementById('trxBtn');
  var KEY = 'sb-collapsed';

  try { 
    var savedState = localStorage.getItem(KEY);
    // Jika belum pernah disimpan sama sekali ATAU statusnya '1', buat jadi collapsed (tertutup)
    if (savedState === '1' || savedState === null) {
      sb.classList.add('collapsed');
    }
  } catch (e) {}

  toggle.addEventListener('click', function () {
    sb.classList.toggle('collapsed');
    try { localStorage.setItem(KEY, sb.classList.contains('collapsed') ? '1' : '0'); } catch (e) {}
  });

  trxBtn.addEventListener('click', function () {
    if (sb.classList.contains('collapsed')) {
      sb.classList.remove('collapsed');
      try { localStorage.setItem(KEY, '0'); } catch (e) {}
    }
    var li = trxBtn.parentElement;
    li.classList.toggle('open');
    trxBtn.setAttribute('aria-expanded', li.classList.contains('open'));
  });
})();
</script>