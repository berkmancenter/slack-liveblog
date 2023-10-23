function adjustYoutubeIframeSize(iframe) {
  const parentWidth = iframe.parentElement.clientWidth;

  const iframeHeight = (parentWidth * 0.5625) + 'px';
  iframe.style.width = '100%';
  iframe.style.height = iframeHeight;
  iframe.classList.add('youtube-embed-done');
}

function handleNewYouTubeIframes() {
  const iframes = document.querySelectorAll('iframe:not(.youtube-embed-done)');
  for (const iframe of iframes) {
    if (iframe.src && iframe.src.includes('youtube.com')) {
      adjustYoutubeIframeSize(iframe);
    }
  }
}

setInterval(handleNewYouTubeIframes, 1000);
