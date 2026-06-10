// Initialize Swup
let swup;
if (typeof Swup !== 'undefined') {
  swup = new Swup();
  
  // Update active nav link on page transition (navbar is outside #swup container)
  swup.hooks.on('page:view', () => {
    updateActiveNavLinks();
  });
}

function updateActiveNavLinks() {
  const currentUrl = window.location.href.split('#')[0].replace(/\/$/, '');
  
  document.querySelectorAll('.navbar-nav a').forEach(link => {
    const linkUrl = link.href.split('#')[0].replace(/\/$/, '');
    
    // Remove active classes
    link.classList.remove('active');
    if (link.parentElement) {
        link.parentElement.classList.remove('current-menu-item', 'current_page_item');
    }

    // Add active classes if href matches current URL
    if (linkUrl === currentUrl) {
        link.classList.add('active');
        if (link.parentElement) {
            link.parentElement.classList.add('current-menu-item');
        }
    }
  });
}

// Ensure clicking immediately sets it to active (for instant visual feedback)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.navbar-nav a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.navbar-nav a').forEach(l => {
                l.classList.remove('active');
                if (l.parentElement) l.parentElement.classList.remove('current-menu-item', 'current_page_item');
            });
            this.classList.add('active');
            if (this.parentElement) this.parentElement.classList.add('current-menu-item');
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
  // Theme Toggle Logic (Persistent)
  const themeToggleBtn = document.getElementById('theme-toggle');
  const themeIcon = document.getElementById('theme-icon');

  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeIcon(savedTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      updateThemeIcon(newTheme);
    });
  }

  function updateThemeIcon(theme) {
    if (!themeIcon) return;
    if (theme === 'dark') {
      themeIcon.classList.remove('bi-moon-fill');
      themeIcon.classList.add('bi-sun-fill');
    } else {
      themeIcon.classList.remove('bi-sun-fill');
      themeIcon.classList.add('bi-moon-fill');
    }
  }

  // Audio Player Logic (Persistent)
  const stickyPlayer = document.getElementById('sticky-player');
  const globalPlayBtn = document.getElementById('global-play-btn');
  const globalPlayIcon = document.getElementById('global-play-icon');
  const equalizer = document.getElementById('player-equalizer');
  const audioPlayer = document.getElementById('global-audio-player');
  const volumeSlider = document.getElementById('player-volume');

  let isPlaying = false;

  // Initialize player with single page stream if available (Initial Load)
  if (stickyPlayer) {
    const initStream = stickyPlayer.getAttribute('data-init-stream');
    if (initStream && audioPlayer) {
      audioPlayer.src = initStream;
    }
  }

  if (audioPlayer) {
    audioPlayer.addEventListener('waiting', () => {
      const liveDot = document.querySelector('#sticky-player .live-dot');
      const details = document.getElementById('player-station-details');
      if (liveDot) liveDot.style.backgroundColor = '#f59e0b'; // buffering color
      if (details) details.innerText = 'Buffering...';
    });
    audioPlayer.addEventListener('playing', () => {
      const liveDot = document.querySelector('#sticky-player .live-dot');
      const details = document.getElementById('player-station-details');
      if (liveDot) liveDot.style.backgroundColor = '#10b981'; // playing color
      if (details) details.innerText = 'Live Radio';
    });
    audioPlayer.addEventListener('error', () => {
      const liveDot = document.querySelector('#sticky-player .live-dot');
      const details = document.getElementById('player-station-details');
      if (liveDot) liveDot.style.backgroundColor = '#ef4444'; // error color
      if (details) details.innerText = 'Stream Error';
      togglePlayState(false, true);
    });
  }

  // Play button from card (Delegated, so it survives Swup transitions)
  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-play-trigger');
    if (btn) {
      e.preventDefault();

      const streamUrl = btn.getAttribute('data-stream-url');
      if (!streamUrl) return;

      const card = btn.closest('.station-card') || btn.closest('.station-hero');
      if (card) {
        const stationName = card.querySelector('.station-name')?.innerText || 'Unknown Station';
        document.getElementById('player-station-name').innerText = stationName;

        const imgSrc = card.querySelector('.station-img')?.src || card.querySelector('.station-large-logo')?.src;
        if (imgSrc) {
          document.getElementById('player-thumbnail').src = imgSrc;
        }
      }

      if (stickyPlayer) stickyPlayer.classList.add('show');

      // Set audio source and play
      if (audioPlayer.src !== streamUrl) {
        audioPlayer.src = streamUrl;
      }

      audioPlayer.play().then(() => {
        togglePlayState(true);
      }).catch(err => {
        console.error("Error playing audio:", err);
        const details = document.getElementById('player-station-details');
        if (details) details.innerText = 'Autoplay Blocked';
        togglePlayState(false, true);
      });
    }
  });

  if (globalPlayBtn) {
    globalPlayBtn.addEventListener('click', () => {
      if (isPlaying) {
        audioPlayer.pause();
        togglePlayState(false);
      } else {
        audioPlayer.play().then(() => {
          togglePlayState(true);
        }).catch(err => {
          console.error(err);
          togglePlayState(false, true);
        });
      }
    });
  }

  if (volumeSlider && audioPlayer) {
    audioPlayer.volume = volumeSlider.value / 100;
    volumeSlider.addEventListener('input', (e) => {
      audioPlayer.volume = e.target.value / 100;
    });
  }

  function togglePlayState(play, isError = false) {
    isPlaying = play;

    const liveDot = document.querySelector('#sticky-player .live-dot');
    const details = document.getElementById('player-station-details');
    if (liveDot) {
      if (play) {
        liveDot.style.backgroundColor = '#10b981';
        if (details) details.innerText = 'Live Radio';
      } else if (isError) {
        liveDot.style.backgroundColor = '#ef4444';
      } else {
        liveDot.style.backgroundColor = '';
        if (details) details.innerText = 'Paused';
      }
    }

    if (globalPlayIcon && equalizer) {
      if (isPlaying) {
        globalPlayIcon.classList.remove('bi-play-fill');
        globalPlayIcon.classList.add('bi-pause-fill');
        equalizer.classList.remove('paused');
      } else {
        globalPlayIcon.classList.remove('bi-pause-fill');
        globalPlayIcon.classList.add('bi-play-fill');
        equalizer.classList.add('paused');
      }
    }
  }

  // Back to Top Button (Persistent)
  const backToTopBtn = document.getElementById('btn-back-to-top');
  window.addEventListener('scroll', () => {
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
      backToTopBtn.style.display = 'block';
    } else {
      backToTopBtn.style.display = 'none';
    }
  });

  if (backToTopBtn) {
    backToTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Dynamic Initialization (Runs on load and after Swup transitions)
  function initApp() {
    // Initialize Choices.js for rich multi-select (if available)
    const genreSelect = document.getElementById('genreSelect');
    if (genreSelect && typeof Choices !== 'undefined') {
      new Choices(genreSelect, {
        removeItemButton: true,
        searchEnabled: true,
        placeholderValue: 'Select genres...',
        searchPlaceholderValue: 'Search genres...',
        shouldSort: false,
        itemSelectText: '',
        noChoicesText: 'No more genres'
      });
    }

    // Grid/List View Toggle
    const btnGridView = document.getElementById('btn-grid-view');
    const btnListView = document.getElementById('btn-list-view');
    const gridViewSection = document.getElementById('grid-view-section');
    const listViewSection = document.getElementById('list-view-section');

    if (btnGridView && btnListView && gridViewSection && listViewSection) {
      btnGridView.addEventListener('click', () => {
        btnGridView.classList.add('active');
        btnListView.classList.remove('active');
        gridViewSection.classList.remove('d-none');
        listViewSection.classList.add('d-none');
      });

      btnListView.addEventListener('click', () => {
        btnListView.classList.add('active');
        btnGridView.classList.remove('active');
        gridViewSection.classList.add('d-none');
        listViewSection.classList.remove('d-none');
      });
    }

    // Newsletter Subscription
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = document.getElementById('newsletter-email');
        const submitBtn = document.getElementById('newsletter-submit');
        const msgDiv = document.getElementById('newsletter-msg');
        
        if (!emailInput.value) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        msgDiv.className = 'mt-2 small d-none';
        
        const formData = new FormData();
        formData.append('action', 'liveradio_subscribe');
        formData.append('email', emailInput.value);
        formData.append('nonce', liveradio_ajax.nonce);
        
        fetch(liveradio_ajax.ajax_url, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bi bi-send-fill"></i>';
          msgDiv.classList.remove('d-none', 'text-success', 'text-danger');
          if (data.success) {
            msgDiv.classList.add('text-success');
            msgDiv.textContent = data.data;
            newsletterForm.reset();
          } else {
            msgDiv.classList.add('text-danger');
            msgDiv.textContent = data.data || 'Subscription failed.';
          }
        })
        .catch(err => {
          console.error(err);
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bi bi-send-fill"></i>';
          msgDiv.classList.remove('d-none');
          msgDiv.classList.add('text-danger');
          msgDiv.textContent = 'Server error.';
        });
      });
    }

    // AJAX Form Submission (Submit Station)
    const submitForm = document.getElementById('submitStationForm');
    const msgDiv = document.getElementById('submit-message');
    if (submitForm) {
      submitForm.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!submitForm.checkValidity()) {
          submitForm.classList.add('was-validated');
          return;
        }

        const formData = new FormData(submitForm);
        formData.append('nonce', liveradio_ajax.nonce);

        fetch(liveradio_ajax.ajax_url, {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            msgDiv.classList.remove('d-none', 'alert-danger', 'alert-success');
            if (data.success) {
              msgDiv.classList.add('alert-success');
              msgDiv.textContent = data.data;
              submitForm.reset();
              submitForm.classList.remove('was-validated');
            } else {
              msgDiv.classList.add('alert-danger');
              msgDiv.textContent = data.data || 'Error occurred';
            }
          })
          .catch(error => {
            console.error(error);
            msgDiv.classList.remove('d-none');
            msgDiv.classList.add('alert-danger');
            msgDiv.textContent = 'Server error.';
          });
      });
    }

    // AJAX Station Filtering
    const filterForm = document.getElementById('taxonomy-filter-form');
    const stationContainer = document.getElementById('station-list-container');
    if (filterForm && stationContainer) {
      const inputs = filterForm.querySelectorAll('input[name="search"], select[name="country"], select[name="sort"]');
      let timeout = null;

      inputs.forEach(input => {
        input.addEventListener('input', () => {
          // Update hero section if country dropdown changes
          if (input.name === 'country') {
            const selectedOption = input.options[input.selectedIndex];
            const newName = selectedOption.getAttribute('data-name');
            const newCode = selectedOption.getAttribute('data-code');
            const newCount = selectedOption.getAttribute('data-count');
            
            const heroTermName = document.getElementById('hero-term-name');
            const heroDesc = document.querySelector('.term-name-desc');
            const heroIconContainer = document.getElementById('hero-icon-container');
            const heroTermCount = document.getElementById('hero-term-count');
            
            if (heroTermName && newName) {
                heroTermName.textContent = newName;
            }
            if (heroDesc && newName) {
                heroDesc.textContent = newName.toLowerCase();
            }
            if (heroTermCount && newCount) {
                heroTermCount.textContent = parseInt(newCount).toLocaleString();
            }
            
            if (heroIconContainer && newCode) {
                heroIconContainer.innerHTML = `<img id="hero-country-flag" src="https://flagcdn.com/w320/${newCode}.png" alt="${newName} Flag" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else if (heroIconContainer && !newCode) {
                // If default is selected, reset to a globe icon
                heroIconContainer.innerHTML = `<i class="bi bi-globe2"></i>`;
            }
          }

          clearTimeout(timeout);
          timeout = setTimeout(() => {
            const formData = new FormData(filterForm);

            fetch(liveradio_ajax.ajax_url, {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success && data.data.grid && data.data.list) {
                  const gridSection = document.querySelector('#grid-view-section .row');
                  const listSection = document.querySelector('#list-view-section .d-flex');
                  if (gridSection) gridSection.innerHTML = data.data.grid;
                  if (listSection) listSection.innerHTML = data.data.list;

                  const pagination = document.querySelector('.pagination-wrapper');
                  if (pagination) pagination.style.display = 'none';
                }
              });
          }, 500); // debounce 500ms
        });
      });
    }

    // Auto-play Logic for Single Station Page
    const heroPlayBtn = document.querySelector('.station-hero .btn-play-trigger');
    if (heroPlayBtn) {
      const streamUrl = heroPlayBtn.getAttribute('data-stream-url');
      if (audioPlayer.src !== streamUrl || !isPlaying) {
        // Short delay to ensure DOM is ready and UI can update
        setTimeout(() => heroPlayBtn.click(), 300);
      }
    }
  }

  // Run initApp on first load
  initApp();

  // Re-run initApp on Swup page transition
  if (swup) {
    swup.hooks.on('page:view', () => {
      initApp();
    });
  }
});
