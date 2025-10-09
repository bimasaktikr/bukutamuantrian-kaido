(() => {
    if (!('speechSynthesis' in window)) {
      console.warn('Speech Synthesis API tidak didukung browser ini.');
      return;
    }

    const state = {
      enabled: JSON.parse(localStorage.getItem('tts-enabled') || 'false'),
      voice: null,
      lastSpoken: '',
      lastTime: 0,
      minIntervalMs: 900, // cegah spam suara saat mouse gerak cepat
      lang: 'id-ID',
      rate: 1.0,
      pitch: 1.0,
      volume: 1.0,
    };

    const byId = (id) => document.getElementById(id);

    function pickIndonesianVoice() {
      const voices = speechSynthesis.getVoices();
      // Cari voice Indonesia jika tersedia
      let v = voices.find(v => v.lang?.toLowerCase() === 'id-id');
      // fallback: cari voice yang mengandung "Indonesia"
      if (!v) v = voices.find(v => /indo/i.test(v.name));
      // fallback terakhir: biarkan null (browser pilih default)
      return v || null;
    }

    function initVoices() {
      state.voice = pickIndonesianVoice();
    }
    speechSynthesis.onvoiceschanged = initVoices;
    initVoices();

    function speak(text) {
      if (!text) return;
      const now = Date.now();
      const normalized = text.replace(/\s+/g, ' ').trim().slice(0, 220);
      if (normalized === state.lastSpoken && now - state.lastTime < 2500) return;

      speechSynthesis.cancel();
      const u = new SpeechSynthesisUtterance(normalized);
      u.lang   = state.lang;
      u.rate   = state.rate;
      u.pitch  = state.pitch;
      u.volume = state.volume;
      if (state.voice) u.voice = state.voice;

      speechSynthesis.speak(u);
      state.lastSpoken = normalized;
      state.lastTime = now;
    }

    function extractText(target) {
      // Prioritaskan elemen yang memang diberi data-tts
      const el = target.closest('[data-tts]');
      if (el && el.getAttribute('data-tts')) return el.getAttribute('data-tts');

      // fallback aksesibel
      const attrText = target.getAttribute?.('aria-label') || target.alt || target.title;
      if (attrText) return attrText;

      // terakhir, ambil innerText bersih
      return (target.innerText || '').replace(/\s+/g, ' ').trim();
    }

    function onHoverOrFocus(e) {
      if (!state.enabled) return;
      const t = e.target;
      const text = extractText(t);
      if (text) speak(text);
    }

    function onLeave(e) {
      // opsional: hentikan bicara saat mouse keluar
      // speechSynthesis.cancel();
    }

    // Toggle button
    function syncToggleUI() {
      const btn = byId('tts-toggle');
      if (!btn) return;
      btn.setAttribute('aria-pressed', String(state.enabled));
      btn.textContent = state.enabled ? '🔇 Matikan suara' : '🔊 Aktifkan suara';
    }

    function toggle() {
      state.enabled = !state.enabled;
      localStorage.setItem('tts-enabled', JSON.stringify(state.enabled));
      syncToggleUI();
      if (!state.enabled) speechSynthesis.cancel();
    }

    // Pasang event global (delegation)
    document.addEventListener('mouseover', onHoverOrFocus, { passive: true });
    document.addEventListener('focusin',   onHoverOrFocus, { passive: true });
    document.addEventListener('mouseout',  onLeave,        { passive: true });

    // Tombol toggle siap pakai (perlu interaksi user untuk iOS/Safari)
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('#tts-toggle');
      if (btn) {
        // Ketika user klik, aman untuk inisialisasi voice di iOS
        if (!state.voice) initVoices();
        toggle();
      }
    });

    // Set UI awal setelah DOM siap
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', syncToggleUI);
    } else {
      syncToggleUI();
    }
  })();
