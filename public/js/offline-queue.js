/**
 * NORSU OJT DTR - Offline queue for time-in/time-out
 * Stores pending actions in IndexedDB and syncs when back online.
 * Optional snapshot_jpeg_base64: verification photo captured at clock time; replayed via multipart on sync.
 */
(function () {
  const DB_NAME = 'norsu-dtr-offline';
  const DB_VERSION = 1;
  const STORE = 'pending_actions';
  let db = null;

  function base64ToBlob(base64, mimeType) {
    const mime = mimeType || 'image/jpeg';
    try {
      const bin = atob(base64);
      const len = bin.length;
      const arr = new Uint8Array(len);
      for (let i = 0; i < len; i++) {
        arr[i] = bin.charCodeAt(i);
      }
      return new Blob([arr], { type: mime });
    } catch (e) {
      return null;
    }
  }

  function openDB() {
    return new Promise((resolve, reject) => {
      if (db) return resolve(db);
      const req = indexedDB.open(DB_NAME, DB_VERSION);
      req.onerror = () => reject(req.error);
      req.onsuccess = () => {
        db = req.result;
        resolve(db);
      };
      req.onupgradeneeded = (e) => {
        const database = e.target.result;
        if (!database.objectStoreNames.contains(STORE)) {
          database.createObjectStore(STORE, { keyPath: 'id', autoIncrement: true });
        }
      };
    });
  }

  function addPending(action) {
    return openDB().then((database) => {
      return new Promise((resolve, reject) => {
        const tx = database.transaction(STORE, 'readwrite');
        const store = tx.objectStore(STORE);
        const req = store.add({
          action_type: action.action_type,
          face_encoding: action.face_encoding,
          recorded_at: action.recorded_at,
          _token: action._token,
          time_in_url: action.time_in_url,
          time_out_url: action.time_out_url,
          lunch_break_url: action.lunch_break_url,
          verification_confidence: action.verification_confidence,
          snapshot_jpeg_base64: action.snapshot_jpeg_base64 || null,
          created_at: new Date().toISOString()
        });
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
      });
    });
  }

  function getAllPending() {
    return openDB().then((database) => {
      return new Promise((resolve, reject) => {
        const tx = database.transaction(STORE, 'readonly');
        const store = tx.objectStore(STORE);
        const req = store.getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => reject(req.error);
      });
    });
  }

  function removePending(id) {
    return openDB().then((database) => {
      return new Promise((resolve, reject) => {
        const tx = database.transaction(STORE, 'readwrite');
        const store = tx.objectStore(STORE);
        const req = store.delete(id);
        req.onsuccess = () => resolve();
        req.onerror = () => reject(req.error);
      });
    });
  }

  function isOnline() {
    return typeof navigator !== 'undefined' && navigator.onLine === true;
  }

  /**
   * Sync one pending item to the server.
   * @param {object} item - { id, action_type, face_encoding, recorded_at, _token, time_in_url, time_out_url }
   * @returns {Promise<boolean>} - true if sync succeeded
   */
  function syncOne(item) {
    let url = item.time_out_url;
    if (item.action_type === 'timein') {
      url = item.time_in_url;
    } else if (item.action_type === 'lunchbreak') {
      url = item.lunch_break_url || item.time_out_url;
    }
    const headersBase = {
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'text/html',
      'X-CSRF-TOKEN': item._token
    };

    if (item.snapshot_jpeg_base64) {
      const imageBlob = base64ToBlob(item.snapshot_jpeg_base64, 'image/jpeg');
      if (!imageBlob || imageBlob.size === 0) {
        return Promise.resolve(false);
      }
      const formData = new FormData();
      formData.append('_token', item._token);
      formData.append('face_encoding', item.face_encoding);
      formData.append('recorded_at', item.recorded_at);
      if (item.verification_confidence != null && item.verification_confidence !== '') {
        formData.append('verification_confidence', String(item.verification_confidence));
      }
      const fname = 'verification-' + (item.action_type || 'record') + '.jpg';
      formData.append('verification_snapshot', imageBlob, fname);
      return fetch(url, {
        method: 'POST',
        headers: headersBase,
        body: formData,
        credentials: 'same-origin'
      }).then((res) => {
        if (res.ok || res.redirected) return true;
        return res.text().then(() => false);
      }).catch(() => false);
    }

    const params = {
      _token: item._token,
      face_encoding: item.face_encoding,
      recorded_at: item.recorded_at
    };
    if (item.verification_confidence != null && item.verification_confidence !== '') {
      params.verification_confidence = item.verification_confidence;
    }
    const body = new URLSearchParams(params);
    return fetch(url, {
      method: 'POST',
      headers: {
        ...headersBase,
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: body.toString(),
      credentials: 'same-origin'
    }).then((res) => {
      if (res.ok || res.redirected) return true;
      return res.text().then(() => false);
    }).catch(() => false);
  }

  /**
   * Process queue: send each item to server, remove on success.
   */
  function processQueue() {
    return getAllPending().then((items) => {
      return items.reduce((p, item) => {
        return p.then(() => syncOne(item)).then((ok) => {
          if (ok) return removePending(item.id);
        });
      }, Promise.resolve());
    });
  }

  function onOnline() {
    processQueue().then(() => {
      if (typeof window.dtrOfflineQueueOnSynced === 'function') {
        window.dtrOfflineQueueOnSynced();
      }
    }).catch((err) => console.warn('Offline queue sync error', err));
  }

  if (typeof window !== 'undefined') {
    window.addEventListener('online', onOnline);
    window.DtrOfflineQueue = {
      isOnline,
      addPending,
      getAllPending,
      removePending,
      processQueue,
      openDB
    };
  }
})();
