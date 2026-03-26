<button class="emms__cta" id="copy-referral-btn">COPIAR LINK</button>

<script type="module">
  if (!window.__referralCaptureOnce) {
    window.__referralCaptureOnce = true;

    function getEncodedEmail() {
      return localStorage.getItem('dplrid');
    }

    function generateReferralUrl() {
      const encodedEmail = getEncodedEmail();
      if (!encodedEmail) return null;
      const currentEvent = window.APP.EVENTS.CURRENT;
      const baseUrl = window.location.origin;
      const registrationPath = currentEvent.pages.unregistered.url;
      return `${baseUrl}/${registrationPath}?emms_ref=${encodedEmail}`;
    }

    async function copyToClipboard(btn) {
      const referralUrl = generateReferralUrl();
      if (!referralUrl) return;

      try {
        await navigator.clipboard.writeText(referralUrl);
      } catch {
        const tmp = document.createElement('input');
        tmp.value = referralUrl;
        document.body.appendChild(tmp);
        tmp.select();
        document.execCommand('copy');
        tmp.remove();
      }

      const originalText = btn.textContent;
      btn.textContent = '¡COPIADO!';
      setTimeout(() => { btn.textContent = originalText; }, 2000);
    }

    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('#copy-referral-btn');
      if (!btn) return;
      e.preventDefault();
      e.stopPropagation();
      await copyToClipboard(btn);
    }, true);
  }
</script>
