/* Minimal AJAX polling helper. Skips ticks while the tab is hidden or a request is in flight. */
window.startPolling = function (url, onData, intervalMs) {
  var busy = false;
  async function tick() {
    if (busy || document.hidden) return;
    busy = true;
    try {
      var res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      });
      if (res.ok) onData(await res.json());
    } catch (e) { /* network hiccup: try again next tick */ }
    finally { busy = false; }
  }
  tick();
  return setInterval(tick, intervalMs || 3000);
};
