/* --- Project detail: custom video player --- */
(function () {
  const player = document.getElementById('projectVideoPlayer');
  const video = document.getElementById('projectVideo');
  if (!player || !video) return;

  let sources = [];
  try {
    sources = JSON.parse(player.dataset.sources || '[]');
  } catch (e) {
    sources = [];
  }

  const thumbs = Array.from(document.querySelectorAll('.project-video-thumb'));
  if (!sources.length && thumbs.length) {
    sources = thumbs.map(function (thumb) { return thumb.dataset.src; });
  }
  if (!sources.length) return;

  let currentIndex = 0;

  const overlay = document.getElementById('projectVideoOverlay');
  const playBig = document.getElementById('projectVideoPlayBig');
  const playPauseBtn = document.getElementById('projectVideoPlayPause');
  const progress = document.getElementById('projectVideoProgress');
  const progressFill = document.getElementById('projectVideoProgressFill');
  const timeEl = document.getElementById('projectVideoTime');
  const muteBtn = document.getElementById('projectVideoMute');
  const volumeInput = document.getElementById('projectVideoVolume');
  const fullscreenBtn = document.getElementById('projectVideoFullscreen');
  const prevBtn = document.getElementById('projectVideoPrev');
  const nextBtn = document.getElementById('projectVideoNext');
  const counterEl = document.getElementById('projectVideoCounter');

  function formatTime(seconds) {
    if (!isFinite(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return mins + ':' + String(secs).padStart(2, '0');
  }

  function updatePlayState(isPlaying) {
    player.classList.toggle('is-playing', isPlaying);
    if (playPauseBtn) {
      playPauseBtn.setAttribute('aria-label', isPlaying ? 'Pause' : 'Play');
    }
    if (overlay) {
      overlay.classList.toggle('is-hidden', isPlaying);
    }
  }

  function updateProgress() {
    if (!video.duration) return;
    const pct = (video.currentTime / video.duration) * 100;
    if (progress) progress.value = pct;
    if (progressFill) progressFill.style.width = pct + '%';
    if (timeEl) {
      timeEl.textContent = formatTime(video.currentTime) + ' / ' + formatTime(video.duration);
    }
  }

  function updateMuteState() {
    player.classList.toggle('is-muted', video.muted || video.volume === 0);
  }

  function updateCounter() {
    if (counterEl) {
      counterEl.textContent = (currentIndex + 1) + ' / ' + sources.length;
    }
    thumbs.forEach(function (thumb, index) {
      thumb.classList.toggle('is-active', index === currentIndex);
    });
  }

  function loadVideo(index, autoplay) {
    currentIndex = (index + sources.length) % sources.length;
    video.src = sources[currentIndex];
    video.load();
    updateCounter();
    updatePlayState(false);
    if (autoplay) {
      video.play().then(function () {
        updatePlayState(true);
      }).catch(function () {
        updatePlayState(false);
      });
    }
  }

  function togglePlay() {
    if (video.paused) {
      video.play().then(function () {
        updatePlayState(true);
      }).catch(function () {});
    } else {
      video.pause();
      updatePlayState(false);
    }
  }

  function toggleMute() {
    video.muted = !video.muted;
    if (!video.muted && video.volume === 0) {
      video.volume = 0.5;
      if (volumeInput) volumeInput.value = 0.5;
    }
    updateMuteState();
  }

  function toggleFullscreen() {
    const target = player;
    if (!document.fullscreenElement) {
      if (target.requestFullscreen) target.requestFullscreen();
    } else if (document.exitFullscreen) {
      document.exitFullscreen();
    }
  }

  loadVideo(0, false);
  initVideoThumbnails();

  function initVideoThumbnails() {
    document.querySelectorAll('.project-video-thumb-video').forEach(function (thumbVideo) {
      thumbVideo.addEventListener('loadeddata', function () {
        if (thumbVideo.currentTime === 0) {
          thumbVideo.currentTime = 0.1;
        }
        thumbVideo.pause();
      });
    });
  }

  if (playBig) playBig.addEventListener('click', togglePlay);
  if (playPauseBtn) playPauseBtn.addEventListener('click', togglePlay);
  if (overlay) overlay.addEventListener('click', togglePlay);

  video.addEventListener('click', togglePlay);
  video.addEventListener('play', function () { updatePlayState(true); });
  video.addEventListener('pause', function () { updatePlayState(false); });
  video.addEventListener('timeupdate', updateProgress);
  video.addEventListener('loadedmetadata', updateProgress);
  video.addEventListener('ended', function () {
    if (sources.length > 1) {
      loadVideo(currentIndex + 1, true);
    } else {
      updatePlayState(false);
    }
  });

  if (progress) {
    progress.addEventListener('input', function () {
      if (!video.duration) return;
      video.currentTime = (progress.value / 100) * video.duration;
      updateProgress();
    });
  }

  if (muteBtn) muteBtn.addEventListener('click', toggleMute);

  if (volumeInput) {
    volumeInput.addEventListener('input', function () {
      video.volume = parseFloat(volumeInput.value);
      video.muted = video.volume === 0;
      updateMuteState();
    });
  }

  if (fullscreenBtn) fullscreenBtn.addEventListener('click', toggleFullscreen);

  if (prevBtn) {
    prevBtn.addEventListener('click', function () {
      loadVideo(currentIndex - 1, true);
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      loadVideo(currentIndex + 1, true);
    });
  }

  thumbs.forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      const index = parseInt(thumb.dataset.index, 10);
      if (index === currentIndex) {
        togglePlay();
        return;
      }
      loadVideo(index, true);
    });
  });

  document.addEventListener('keydown', function (event) {
    if (!player.matches(':focus-within')) return;
    if (event.key === 'ArrowUp') {
      event.preventDefault();
      loadVideo(currentIndex - 1, true);
    } else if (event.key === 'ArrowDown') {
      event.preventDefault();
      loadVideo(currentIndex + 1, true);
    } else if (event.key === ' ') {
      if (player.contains(event.target)) return;
      event.preventDefault();
      togglePlay();
    }
  });

  player.setAttribute('tabindex', '0');
})();
