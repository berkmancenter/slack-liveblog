// @TODO
// Remove when https://github.com/mastodon/mastodon/pull/22141/files is deployed

mastodonEmbedInitiallyLoaded = false;

function checkForNewMastodonIframes() {
  const iframes = document.querySelectorAll('iframe.mastodon-embed:not(.mastodon-embed-done)');

  iframes.forEach((_iframe, _index) => {
    mastodon_embed_init(true);
  });
}

function mastodon_embed_init(loadNow = false) {
  console.log(111);
  /**
   * @param {() => void} loaded
   */
  var ready = function (loaded) {
    if (document.readyState === 'complete') {
      loaded();
    } else {
      document.addEventListener('readystatechange', function () {
        if (document.readyState === 'complete') {
          loaded();
        }
      });
    }

    if (loadNow) {
      loaded();
    }
  };

  ready(function () {
    /** @type {Map<number, HTMLIFrameElement>} */
    var iframes = new Map();

    window.addEventListener('message', function (e) {
      var data = e.data || {};

      if (typeof data !== 'object' || data.type !== 'setHeight' || !iframes.has(data.id)) {
        return;
      }

      var iframe = iframes.get(data.id);

      if ('source' in e && iframe.contentWindow !== e.source) {
        return;
      }

      iframe.height = data.height;
    });

    [].forEach.call(document.querySelectorAll('iframe.mastodon-embed:not(.mastodon-embed-done)'), function (iframe) {
      // select unique id for each iframe
      var id = 0, failCount = 0, idBuffer = new Uint32Array(1);
      while (id === 0 || iframes.has(id)) {
        id = crypto.getRandomValues(idBuffer)[0];
        failCount++;
        if (failCount > 100) {
          // give up and assign (easily guessable) unique number if getRandomValues is broken or no luck
          id = -(iframes.size + 1);
          break;
        }
      }

      iframes.set(id, iframe);

      iframe.scrolling = 'no';
      iframe.style.overflow = 'hidden';
      iframe.classList.add('mastodon-embed-done');

      iframe.onload = function () {
        iframe.contentWindow.postMessage({
          type: 'setHeight',
          id: id,
        }, '*');
      };

      iframe.onload();
    });

    if (mastodonEmbedInitiallyLoaded === false) {
      mastodonEmbedInitiallyLoaded = true;
      setInterval(checkForNewMastodonIframes, 1000);
    }
  });
}

(function () {
  mastodon_embed_init();
})();
