"use strict";
(() => {
  // Keep this value synchronized with the pinned OpenPGP.js URLs and SRI hashes
  // in keys.php and openpgp-message.php.
  const INSTALLED_VERSION = "6.3.2";
  const REGISTRY_URL = "https://registry.npmjs.org/openpgp/latest";
  const RELEASES_URL = "https://github.com/openpgpjs/openpgpjs/releases";
  const el = document.getElementById("openpgpUpdateStatus");
  if (!el) return;

  function versionParts(value) {
    const match = /^v?(\d+)\.(\d+)\.(\d+)$/.exec(value || "");
    return match ? match.slice(1).map(Number) : null;
  }
  function compareVersions(a, b) {
    const left = versionParts(a), right = versionParts(b);
    if (!left || !right) throw new Error("Formato versione non riconosciuto.");
    for (let i = 0; i < 3; i++) {
      if (left[i] !== right[i]) return left[i] < right[i] ? -1 : 1;
    }
    return 0;
  }
  function show(message, kind) {
    el.className = "notice" + (kind ? " " + kind : "");
    el.textContent = message;
  }

  show("OpenPGP.js installato: v" + INSTALLED_VERSION + ". Controllo aggiornamenti…", "info");
  fetch(REGISTRY_URL, { cache: "no-store", mode: "cors", credentials: "omit" })
    .then(response => {
      if (!response.ok) throw new Error("Controllo non disponibile.");
      return response.json();
    })
    .then(data => {
      if (!data || typeof data.version !== "string") throw new Error("Risposta del registro non valida.");
      const latest = data.version;
      const comparison = compareVersions(INSTALLED_VERSION, latest);
      if (comparison < 0) {
        show("Aggiornamento OpenPGP.js disponibile: installata v" + INSTALLED_VERSION +
          ", ultima versione stabile v" + latest +
          ". Non viene installato automaticamente: consulta le note di rilascio e le istruzioni di aggiornamento nel repository EPiccion, quindi verifica il nuovo hash SRI.", "update-available");
        const link = document.createElement("a");
        link.href = RELEASES_URL;
        link.target = "_blank";
        link.rel = "noopener noreferrer";
        link.textContent = "Apri le note di rilascio ufficiali";
        const paragraph = document.createElement("p");
        paragraph.appendChild(link);
        el.appendChild(paragraph);
      } else if (comparison === 0) {
        show("OpenPGP.js v" + INSTALLED_VERSION + " è allineato all'ultima versione stabile pubblicata al momento del controllo.", "info");
      } else {
        show("La versione installata di OpenPGP.js (v" + INSTALLED_VERSION +
          ") è più recente della versione restituita dal registro (v" + latest +
          "). Verifica manualmente le fonti ufficiali prima di intervenire.", "info");
      }
    })
    .catch(() => {
      show("Impossibile verificare gli aggiornamenti di OpenPGP.js. La versione installata resta v" +
        INSTALLED_VERSION + ". Controlla manualmente le note ufficiali di rilascio.", "info");
    });
})();
