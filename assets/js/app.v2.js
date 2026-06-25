// Initialize Swup
let swup;
if (typeof Swup !== 'undefined') {
  swup = new Swup({
    plugins: [new SwupHeadPlugin()]
  });
  
  // Update active nav link on page transition (navbar is outside #swup container)
  swup.hooks.on('page:view', () => {
    updateActiveNavLinks();
    
    // Re-initialize AdSense if available
    if (typeof window.adsbygoogle !== 'undefined') {
        document.querySelectorAll('.adsbygoogle').forEach(ad => {
            if (!ad.hasAttribute('data-adsbygoogle-status')) {
                window.adsbygoogle.push({});
            }
        });
    }
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
  // Fetch user country for localized stations if cookie not set
  if (document.cookie.indexOf('lr_user_country=') === -1) {
    fetch('http://ip-api.com/json/?fields=country,countryCode')
      .then(response => response.json())
      .then(data => {
        if (data && data.country && data.countryCode) {
          const expires = new Date(Date.now() + 12 * 60 * 60 * 1000).toUTCString();
          document.cookie = `lr_user_country=${encodeURIComponent(data.country)}; expires=${expires}; path=/`;
          document.cookie = `lr_user_country_code=${encodeURIComponent(data.countryCode)}; expires=${expires}; path=/`;
        }
      })
      .catch(err => console.log('Error fetching user country:', err));
  }

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

  // The player stream is fetched dynamically via AJAX on click or auto-play

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

      const stationId = btn.getAttribute('data-station-id');
      if (!stationId) return;

      const card = btn.closest('.station-card') || btn.closest('.station-hero');
      if (card) {
        const stationName = card.querySelector('.station-name')?.innerText || 'Unknown Station';
        document.getElementById('player-station-name').innerText = stationName;
        
        const fpName = document.getElementById('front-playing-name');
        if (fpName) fpName.innerText = stationName;

        const imgSrc = card.querySelector('.station-img')?.src || card.querySelector('.station-large-logo')?.src;
        if (imgSrc) {
          document.getElementById('player-thumbnail').src = imgSrc;
          
          const fpImg = document.getElementById('front-playing-img');
          if (fpImg) fpImg.src = imgSrc;
        }
      }

      if (stickyPlayer) stickyPlayer.classList.add('show');
      const details = document.getElementById('player-station-details');
      if (details) details.innerText = 'Connecting...';

      const formData = new FormData();
      formData.append('action', 'liveradio_get_stream_url');
      formData.append('station_id', stationId);
      formData.append('nonce', liveradio_ajax.nonce);

      fetch(liveradio_ajax.ajax_url, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data.stream_url) {
          const streamUrl = data.data.stream_url;
          if (audioPlayer.src !== streamUrl) {
            audioPlayer.src = streamUrl;
            audioPlayer.dataset.stationId = stationId;
          }

          audioPlayer.play().then(() => {
            togglePlayState(true);
          }).catch(err => {
            console.error("Error playing audio:", err);
            if (err.name === 'NotAllowedError') {
              togglePlayState(false);
            } else {
              if (details) details.innerText = 'Stream Error';
              togglePlayState(false, true);
            }
          });
        } else {
          if (details) details.innerText = 'Stream Unavailable';
          togglePlayState(false, true);
        }
      })
      .catch(err => {
        console.error("Error fetching stream:", err);
        if (details) details.innerText = 'Connection Error';
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
    const fpStatus = document.getElementById('front-playing-status');

    if (play) {
      if (liveDot) liveDot.style.backgroundColor = '#10b981';
      if (details) details.innerText = 'Live Radio';
      if (fpStatus) fpStatus.innerText = 'Now playing \u00b7 Live';
    } else if (isError) {
      if (liveDot) liveDot.style.backgroundColor = '#ef4444';
      if (fpStatus) fpStatus.innerText = 'Stream Error';
    } else {
      if (liveDot) liveDot.style.backgroundColor = '';
      if (details) details.innerText = 'Paused';
      if (fpStatus) fpStatus.innerText = 'Paused';
    }

    if (globalPlayIcon && equalizer) {
      const fpEq = document.getElementById('front-playing-eq');
      if (isPlaying) {
        globalPlayIcon.classList.remove('bi-play-fill');
        globalPlayIcon.classList.add('bi-pause-fill');
        equalizer.classList.remove('paused');
        if (fpEq) fpEq.classList.remove('paused');
      } else {
        globalPlayIcon.classList.remove('bi-pause-fill');
        globalPlayIcon.classList.add('bi-play-fill');
        equalizer.classList.add('paused');
        if (fpEq) fpEq.classList.add('paused');
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

    // AJAX Form Submission (Contact Us)
    const contactForm = document.getElementById('contactForm');
    const contactMsgDiv = document.getElementById('contact-message');
    if (contactForm) {
      contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!contactForm.checkValidity()) {
          contactForm.classList.add('was-validated');
          return;
        }

        const submitBtn = document.getElementById('contactSubmitBtn');
        const originalBtnText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = 'Sending...';

        const formData = new FormData(contactForm);
        formData.append('nonce', liveradio_ajax.nonce); // General nonce fallback if needed, but we check specific nonce in backend

        fetch(liveradio_ajax.ajax_url, {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerText = originalBtnText;
            contactMsgDiv.classList.remove('d-none', 'alert-danger', 'alert-success');
            if (data.success) {
              contactMsgDiv.classList.add('alert-success');
              contactMsgDiv.textContent = data.data;
              contactForm.reset();
              contactForm.classList.remove('was-validated');
            } else {
              contactMsgDiv.classList.add('alert-danger');
              contactMsgDiv.textContent = data.data || 'Error occurred';
            }
          })
          .catch(error => {
            console.error(error);
            submitBtn.disabled = false;
            submitBtn.innerText = originalBtnText;
            contactMsgDiv.classList.remove('d-none');
            contactMsgDiv.classList.add('alert-danger');
            contactMsgDiv.textContent = 'Server error.';
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

    // Sync persistent player state with front page "Now Playing" widget
    const fpName = document.getElementById('front-playing-name');
    const fpImg = document.getElementById('front-playing-img');
    const fpEq = document.getElementById('front-playing-eq');
    const fpStatus = document.getElementById('front-playing-status');
    
    if (fpName || fpImg || fpStatus) {
      if (audioPlayer && audioPlayer.src && audioPlayer.dataset.stationId) {
        // We have an active station in the persistent player
        const persistentName = document.getElementById('player-station-name')?.innerText;
        const persistentImgSrc = document.getElementById('player-thumbnail')?.src;
        const persistentDetails = document.getElementById('player-station-details')?.innerText;
        
        if (fpName && persistentName && persistentName !== 'Unknown Station') {
          fpName.innerText = persistentName;
        }
        if (fpImg && persistentImgSrc) {
          fpImg.src = persistentImgSrc;
        }
        
        if (isPlaying) {
          if (fpStatus) fpStatus.innerText = 'Now playing \u00b7 Live';
          if (fpEq) fpEq.classList.remove('paused');
        } else {
          if (fpStatus) fpStatus.innerText = persistentDetails || 'Paused';
          if (fpEq) fpEq.classList.add('paused');
        }
      }
    }

    // Auto-play Logic for Single Station Page and Front Page
    const heroPlayBtn = document.querySelector('.station-hero .btn-play-trigger');
    if (heroPlayBtn) {
      const stationId = heroPlayBtn.getAttribute('data-station-id');
      const isSpotlight = heroPlayBtn.closest('.spotlight-card') !== null;

      if (isSpotlight) {
        // On front page: only auto-play if no station has been played yet
        if (!audioPlayer.dataset.stationId) {
          setTimeout(() => heroPlayBtn.click(), 300);
        }
      } else {
        // On single station page: auto-play if it's a new station or not playing
        if (audioPlayer.dataset.stationId !== stationId || !isPlaying) {
          // Short delay to ensure DOM is ready and UI can update
          setTimeout(() => heroPlayBtn.click(), 300);
        }
      }
    }

    // Action Buttons (Favorites, Share, Copy Link)
    const btnFavorite = document.getElementById('btn-favorite');
    const btnShare = document.getElementById('btn-share');
    const btnCopyLink = document.getElementById('btn-copy-link');

    if (btnFavorite) {
      const stationId = btnFavorite.getAttribute('data-station-id');
      const favorites = JSON.parse(localStorage.getItem('liveradio_favorites') || '[]');
      const icon = btnFavorite.querySelector('i');
      
      // Initialize state
      if (favorites.includes(stationId)) {
        icon.classList.remove('bi-heart');
        icon.classList.add('bi-heart-fill');
        icon.style.color = '#ef4444';
      }

      btnFavorite.addEventListener('click', () => {
        const currentFavorites = JSON.parse(localStorage.getItem('liveradio_favorites') || '[]');
        const idx = currentFavorites.indexOf(stationId);
        
        if (idx === -1) {
          // Add to favorites
          currentFavorites.push(stationId);
          icon.classList.remove('bi-heart');
          icon.classList.add('bi-heart-fill');
          icon.style.color = '#ef4444';
        } else {
          // Remove from favorites
          currentFavorites.splice(idx, 1);
          icon.classList.remove('bi-heart-fill');
          icon.classList.add('bi-heart');
          icon.style.color = '';
        }
        localStorage.setItem('liveradio_favorites', JSON.stringify(currentFavorites));
      });
    }

    if (btnShare) {
      btnShare.addEventListener('click', () => {
        const title = btnShare.getAttribute('data-title');
        const url = btnShare.getAttribute('data-url');
        
        if (navigator.share) {
          navigator.share({
            title: title,
            url: url
          }).catch(console.error);
        } else {
          // Fallback to Twitter share if native share is not supported
          window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + encodeURIComponent(url), '_blank');
        }
      });
    }

    if (btnCopyLink) {
      btnCopyLink.addEventListener('click', () => {
        const url = btnCopyLink.getAttribute('data-url');
        navigator.clipboard.writeText(url).then(() => {
          const icon = btnCopyLink.querySelector('i');
          const originalClass = icon.className;
          icon.className = 'bi bi-check2 text-success';
          setTimeout(() => {
            icon.className = originalClass;
          }, 2000);
        }).catch(err => {
          console.error('Failed to copy', err);
        });
      });
    }

    // Report Broken Stream
    const btnReport = document.getElementById('btn-report');
    if (btnReport) {
      btnReport.addEventListener('click', () => {
        const stationId = btnReport.getAttribute('data-station-id');
        if (btnReport.classList.contains('reported')) return;
        
        btnReport.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        btnReport.disabled = true;
        
        // Ensure liveradio_ajax is defined
        if (typeof liveradio_ajax === 'undefined') {
          console.error('liveradio_ajax not defined');
          return;
        }

        const formData = new FormData();
        formData.append('action', 'liveradio_report_station');
        formData.append('station_id', stationId);
        formData.append('nonce', liveradio_ajax.nonce);

        fetch(liveradio_ajax.ajax_url, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            btnReport.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
            btnReport.classList.add('reported');
            alert('Thanks! We will review this station.');
          } else {
            btnReport.innerHTML = '<i class="bi bi-exclamation-triangle"></i>';
            btnReport.disabled = false;
            alert('Error reporting station.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          btnReport.innerHTML = '<i class="bi bi-exclamation-triangle"></i>';
          btnReport.disabled = false;
        });
      });
    }
    // Load Favorites on Favorites Page
    const favoritesContainer = document.getElementById('favorites-container');
    if (favoritesContainer) {
      const favoritesGrid = document.getElementById('favorites-grid');
      const favoritesLoading = document.getElementById('favorites-loading');
      const favoritesEmpty = document.getElementById('favorites-empty');
      const currentFavorites = JSON.parse(localStorage.getItem('liveradio_favorites') || '[]');

      if (currentFavorites.length === 0) {
        if (favoritesLoading) favoritesLoading.classList.add('d-none');
        if (favoritesEmpty) favoritesEmpty.classList.remove('d-none');
      } else {
        const formData = new FormData();
        formData.append('action', 'liveradio_get_favorites');
        formData.append('nonce', liveradio_ajax.nonce);
        currentFavorites.forEach(id => formData.append('station_ids[]', id));

        fetch(liveradio_ajax.ajax_url, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (favoritesLoading) favoritesLoading.classList.add('d-none');
          if (data.success && data.data.html) {
            if (favoritesGrid) {
              favoritesGrid.innerHTML = data.data.html;
              favoritesGrid.classList.remove('d-none');
            }
          } else {
            if (favoritesEmpty) favoritesEmpty.classList.remove('d-none');
          }
        })
        .catch(err => {
          console.error('Error fetching favorites', err);
          if (favoritesLoading) favoritesLoading.classList.add('d-none');
          if (favoritesEmpty) {
            favoritesEmpty.classList.remove('d-none');
            favoritesEmpty.querySelector('h3').innerText = 'Error loading favorites';
            favoritesEmpty.querySelector('p').innerText = 'Please try again later.';
          }
        });
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

  // ============================================================
  // RECENTLY PLAYED: Track played stations in localStorage
  // ============================================================
  const RECENTLY_PLAYED_KEY = 'liveradio_recently_played';
  const MAX_RECENTLY_PLAYED = 5;

  function addToRecentlyPlayed(stationId) {
    if (!stationId) return;
    let history = JSON.parse(localStorage.getItem(RECENTLY_PLAYED_KEY) || '[]');
    // Remove if already exists to re-insert at front
    history = history.filter(id => id !== stationId);
    history.unshift(stationId);
    if (history.length > MAX_RECENTLY_PLAYED) history = history.slice(0, MAX_RECENTLY_PLAYED);
    localStorage.setItem(RECENTLY_PLAYED_KEY, JSON.stringify(history));
  }

  function incrementPlayCount(stationId) {
    if (!stationId || typeof liveradio_ajax === 'undefined') return;
    const formData = new FormData();
    formData.append('action', 'liveradio_increment_play');
    formData.append('station_id', stationId);
    formData.append('nonce', liveradio_ajax.nonce);
    fetch(liveradio_ajax.ajax_url, { method: 'POST', body: formData }).catch(() => {});
  }

  // Hook into the existing play button click to track history + increment count
  const originalBodyClick = document.body.onclick;
  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-play-trigger');
    if (btn) {
      const stationId = btn.getAttribute('data-station-id');
      if (stationId) {
        addToRecentlyPlayed(stationId);
        incrementPlayCount(stationId);
      }
    }
  });

  // ============================================================
  // CONTINUE LISTENING: Load recently played on homepage
  // ============================================================
  function loadRecentlyPlayed() {
    const section = document.getElementById('continue-listening-section');
    const container = document.getElementById('recently-played-container');
    if (!section || !container) return;

    const history = JSON.parse(localStorage.getItem(RECENTLY_PLAYED_KEY) || '[]');
    if (history.length === 0) {
      section.classList.add('d-none');
      return;
    }

    const formData = new FormData();
    formData.append('action', 'liveradio_get_recently_played');
    formData.append('nonce', liveradio_ajax.nonce);
    history.forEach(id => formData.append('station_ids[]', id));

    fetch(liveradio_ajax.ajax_url, { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success && data.data.html) {
          container.innerHTML = data.data.html;
          section.classList.remove('d-none');
          // Animate section in
          section.style.opacity = '0';
          section.style.transform = 'translateY(20px)';
          requestAnimationFrame(() => {
            section.style.transition = 'opacity .4s ease, transform .4s ease';
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
          });
        }
      }).catch(() => {});
  }

  // Clear recently played history
  const clearHistoryBtn = document.getElementById('btn-clear-history');
  if (clearHistoryBtn) {
    clearHistoryBtn.addEventListener('click', () => {
      localStorage.removeItem(RECENTLY_PLAYED_KEY);
      const section = document.getElementById('continue-listening-section');
      if (section) {
        section.style.transition = 'opacity .3s ease';
        section.style.opacity = '0';
        setTimeout(() => section.classList.add('d-none'), 300);
      }
    });
  }

  loadRecentlyPlayed();

  if (swup) {
    swup.hooks.on('page:view', () => { loadRecentlyPlayed(); });
  }

  // ============================================================
  // SURPRISE ME: Random station button
  // ============================================================
  const surpriseBtn = document.getElementById('btn-surprise-me');
  if (surpriseBtn) {
    surpriseBtn.addEventListener('click', () => {
      const content = document.getElementById('surprise-btn-content');
      const loading = document.getElementById('surprise-btn-loading');

      content.classList.add('d-none');
      loading.classList.remove('d-none');
      surpriseBtn.disabled = true;

      const formData = new FormData();
      formData.append('action', 'liveradio_random_station');
      formData.append('nonce', liveradio_ajax.nonce);

      fetch(liveradio_ajax.ajax_url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          content.classList.remove('d-none');
          loading.classList.add('d-none');
          surpriseBtn.disabled = false;

          if (data.success && data.data.station_id) {
            const { station_id, station_name, img } = data.data;

            // Update player UI
            const playerName = document.getElementById('player-station-name');
            const playerThumb = document.getElementById('player-thumbnail');
            const stickyPlayer = document.getElementById('sticky-player');
            const playerDetails = document.getElementById('player-station-details');

            if (playerName) playerName.innerText = station_name;
            if (playerThumb && img) playerThumb.src = img;
            if (stickyPlayer) stickyPlayer.classList.add('show');
            if (playerDetails) playerDetails.innerText = 'Connecting...';

            // Fetch stream URL and play
            const streamForm = new FormData();
            streamForm.append('action', 'liveradio_get_stream_url');
            streamForm.append('station_id', station_id);
            streamForm.append('nonce', liveradio_ajax.nonce);

            fetch(liveradio_ajax.ajax_url, { method: 'POST', body: streamForm })
              .then(r => r.json())
              .then(streamData => {
                if (streamData.success && streamData.data.stream_url) {
                  const audio = document.getElementById('global-audio-player');
                  if (audio) {
                    audio.src = streamData.data.stream_url;
                    audio.dataset.stationId = station_id;
                    audio.play().catch(() => {});
                    addToRecentlyPlayed(station_id);
                    incrementPlayCount(station_id);

                    // Flash the button green briefly
                    surpriseBtn.style.borderColor = '#10b981';
                    surpriseBtn.style.color = '#10b981';
                    setTimeout(() => {
                      surpriseBtn.style.borderColor = '';
                      surpriseBtn.style.color = '';
                    }, 1500);
                  }
                }
              });
          }
        })
        .catch(() => {
          content.classList.remove('d-none');
          loading.classList.add('d-none');
          surpriseBtn.disabled = false;
        });
    });
  }
});
