/* --- Project listing: load more --- */
(function () {
  const loadMoreBtn = document.getElementById('loadMoreProjects');
  if (!loadMoreBtn) return;

  const batchSize = parseInt(loadMoreBtn.dataset.batch, 10) || 20;

  loadMoreBtn.addEventListener('click', function () {
    const hiddenCards = document.querySelectorAll('.project-list-card.is-lazy-hidden');
    const nextBatch = Array.from(hiddenCards).slice(0, batchSize);

    nextBatch.forEach(function (card) {
      card.classList.remove('is-lazy-hidden');
      card.classList.add('visible');
    });

    if (document.querySelectorAll('.project-list-card.is-lazy-hidden').length === 0) {
      loadMoreBtn.closest('.project-list-load-more').remove();
    }
  });
})();
