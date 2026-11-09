/**
 * PEKES Pet Shop — Frontend Application Logic (PHP + REST API Compatible)
 * Totalmente autónomo sin dependencias de Node.js / Vite para Hostinger
 */

// Application State
const state = {
  products: window.PEKES_PRODUCTS || [],
  activeFilter: 'all', // 'all', 'Gimnasios de piso', 'Gimnasios de pared', 'Rascadores'
  searchQuery: '',
  isPrecioPendienteSimulated: false,
  whatsappNumber: window.PEKES_WHATSAPP || '573215427745',
  selectedProduct: null
};

// Utilities
function formatCOP(val) {
  if (val === null || val === undefined || isNaN(val)) return 'N/A';
  return '$' + Number(val).toLocaleString('es-CO');
}

function showToast(message, payload) {
  const toast = document.getElementById('analytics-toast');
  const toastMsg = document.getElementById('toast-message');
  const toastCode = document.getElementById('toast-payload');
  if (!toast || !toastMsg) return;
  
  toastMsg.textContent = message;
  if (payload) {
    toastCode.textContent = JSON.stringify(payload, null, 2);
    toastCode.style.display = 'block';
  } else {
    toastCode.style.display = 'none';
  }
  
  toast.classList.add('active');
  setTimeout(() => {
    toast.classList.remove('active');
  }, 4000);
}

// Generate WhatsApp message according to Section 10 specs
function buildWhatsAppUrl(product, isPending) {
  const priceText = isPending ? 'por confirmar' : formatCOP(product.price);
  const questionText = isPending 
    ? '¿Me pueden confirmar su precio, disponibilidad y opciones de entrega?' 
    : '¿Me pueden confirmar su disponibilidad y las opciones de entrega?';

  const baseDomain = window.PEKES_SITE_URL || 'https://www.pekes.shop';
  const slug = getProductSlug(product);
  const productUrl = `${baseDomain}/producto/${slug}`;
  
  const rawMessage = `Hola, PEKES. Estoy interesado en este producto:

Producto: ${product.name}
Referencia: ${product.sku}
Precio publicado: ${priceText}
Enlace: ${productUrl}

${questionText}`;

  const encodedMessage = encodeURIComponent(rawMessage);
  return {
    rawMessage,
    url: `https://wa.me/${state.whatsappNumber}?text=${encodedMessage}`
  };
}

// Analytics Trigger
function trackWhatsAppClick(product, location = 'product_modal') {
  const isPending = state.isPrecioPendienteSimulated;
  const payload = {
    event: 'click_whatsapp',
    location: location,
    product_name: product ? product.name : 'Consulta General',
    sku: product ? product.sku : 'N/A',
    price: (!isPending && product) ? product.price : '',
    category: product ? product.category : 'General',
    page: window.location.pathname
  };

  console.log('[PEKES Analytics] Triggered click_whatsapp:', payload);
  showToast('Evento "click_whatsapp" emitido hacia GA4 & Meta Pixel', payload);

  window.dispatchEvent(new CustomEvent('pekes:click_whatsapp', { detail: payload }));
}

function getProductSlug(p) {
  if (!p) return '';
  if (p.slug) return p.slug;
  const name = (p.name || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  if ((p.category || '').includes('piso')) return 'gimnasio-' + name;
  if ((p.category || '').includes('pared')) {
    const num = (p.name || '').replace(/[^0-9]/g, '');
    return 'gimnasio-pared-combo-' + (num || name);
  }
  if ((p.category || '').includes('Rascador')) return 'rascador-' + name;
  return name;
}

// Render Products Grid
function renderProducts() {
  const grid = document.getElementById('products-grid');
  const countBadge = document.getElementById('catalog-count');
  if (!grid) return;
  
  let filtered = state.products.filter(p => {
    if (state.activeFilter !== 'all' && p.category !== state.activeFilter) {
      return false;
    }
    if (state.searchQuery.trim()) {
      const q = state.searchQuery.toLowerCase();
      const matchName = (p.name || '').toLowerCase().includes(q);
      const matchDims = (p.dimensions || '').toLowerCase().includes(q);
      const matchSku  = (p.sku || '').toLowerCase().includes(q);
      return matchName || matchDims || matchSku;
    }
    return true;
  });

  if (countBadge) {
    countBadge.textContent = `${filtered.length} productos disponibles`;
  }

  if (filtered.length === 0) {
    grid.innerHTML = `
      <div class="empty-catalog">
        <h3>No se encontraron productos</h3>
        <p>Intenta con otro término de búsqueda o selecciona otra categoría.</p>
      </div>
    `;
    return;
  }

  const isPending = state.isPrecioPendienteSimulated;

  grid.innerHTML = filtered.map(p => {
    const isPendingCard = isPending || p.is_pending;
    const priceDisplay = isPendingCard
      ? `<span class="card-price pending">Precio por confirmar por WhatsApp</span>`
      : `<span class="card-price">${formatCOP(p.price)}</span>`;

    const categoryBadge = p.category === 'Gimnasios de pared' ? 'Pared' : (p.category === 'Rascadores' ? 'Rascador' : 'Piso');
    const slug = getProductSlug(p);
    const { url: waUrl } = buildWhatsAppUrl(p, isPendingCard);

    return `
      <article class="product-card" data-id="${p.id}" data-slug="${slug}">
        <div class="card-img-container" role="button" tabindex="0" aria-label="Ver ficha completa de ${p.name}">
          <span class="card-badge">Por encargo</span>
          <span class="card-badge stage-badge">${categoryBadge}</span>
          <img src="${p.image}" alt="Gimnasio para gatos ${p.name}" loading="lazy" onerror="this.src='/images/logos/mascota.png'" />
        </div>
        <div class="card-body">
          <span class="card-sku">${p.sku}</span>
          <h3 class="card-title">${p.name}</h3>
          <p class="card-dims">${p.dimensions || ''}</p>
          <div class="card-price-row">
            ${priceDisplay}
          </div>
          <div class="card-actions-row">
            <button type="button" class="card-btn-view" data-action="view" aria-label="Ver ficha técnica de ${p.name}">
              <span>Ver Ficha</span>
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </button>
            <a href="${waUrl}" target="_blank" rel="noopener" class="card-btn-wa" title="Consultar por WhatsApp" aria-label="Consultar ${p.name} por WhatsApp">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z" />
              </svg>
            </a>
          </div>
        </div>
      </article>
    `;
  }).join('');
}

// Modal Logic
function openProductModal(product) {
  if (!product) return;
  state.selectedProduct = product;
  const isPending = state.isPrecioPendienteSimulated || product.is_pending;
  const modal = document.getElementById('product-modal');
  if (!modal) return;
  
  const { rawMessage, url } = buildWhatsAppUrl(product, isPending);
  const slug = getProductSlug(product);

  const modalImg = document.getElementById('modal-img');
  if (modalImg) {
    modalImg.src = product.image;
    modalImg.alt = product.name;
  }
  
  const titleEl = document.getElementById('modal-title');
  if (titleEl) titleEl.textContent = product.name;

  const skuEl = document.getElementById('modal-sku');
  if (skuEl) skuEl.textContent = `SKU: ${product.sku}`;

  const catEl = document.getElementById('modal-cat');
  if (catEl) catEl.textContent = product.category;

  const descEl = document.getElementById('modal-desc');
  if (descEl) descEl.textContent = product.description || '';

  const dimsEl = document.getElementById('modal-dims');
  if (dimsEl) dimsEl.textContent = product.dimensions || 'Dimensiones a medida. Consultar con asesor por WhatsApp.';

  const priceValEl = document.getElementById('modal-price-val');
  if (priceValEl) {
    if (isPending) {
      priceValEl.textContent = 'Precio por confirmar por WhatsApp';
      priceValEl.className = 'modal-price-val pending';
    } else {
      priceValEl.textContent = formatCOP(product.price);
      priceValEl.className = 'modal-price-val';
    }
  }

  // Includes list
  const includesList = document.getElementById('modal-includes-list');
  const includesBox = document.getElementById('modal-includes-box');
  if (includesBox && includesList) {
    if (product.includes && product.includes.length > 0) {
      includesBox.style.display = 'block';
      includesList.innerHTML = product.includes.map(item => `<li>${item}</li>`).join('');
    } else {
      includesBox.style.display = 'none';
    }
  }

  // Direct permalink to individual SEO page
  const permalinkEl = document.getElementById('modal-permalink');
  if (permalinkEl) {
    permalinkEl.href = '/producto/' + slug;
  }

  // Preview Message (opcional)
  const previewBox = document.getElementById('modal-wa-preview');
  if (previewBox) previewBox.textContent = rawMessage;

  // WhatsApp Button link
  const btnWa = document.getElementById('modal-btn-wa');
  if (btnWa) {
    btnWa.href = url;
    btnWa.onclick = () => {
      trackWhatsAppClick(product, 'product_modal');
    };
  }

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeProductModal() {
  const modal = document.getElementById('product-modal');
  if (!modal) return;
  modal.classList.remove('active');
  document.body.style.overflow = '';
}

// Legal Modal
function openLegalModal(tabKey = 'privacidad') {
  const modal = document.getElementById('legal-modal');
  if (!modal) return;

  const targetPaneId = `pane-${tabKey}`;
  
  document.querySelectorAll('.legal-tab-btn').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-target') === targetPaneId);
  });

  document.querySelectorAll('.legal-pane').forEach(pane => {
    pane.classList.toggle('active', pane.id === targetPaneId);
  });

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeLegalModal() {
  const modal = document.getElementById('legal-modal');
  if (!modal) return;
  modal.classList.remove('active');
  document.body.style.overflow = '';
}

// ==============================================================================
// El Test Michi — Recomendador de Personalidad Felina
// ==============================================================================
function initMichiQuiz() {
  const step1 = document.getElementById('quiz-step-1');
  const step2 = document.getElementById('quiz-step-2');
  const step3 = document.getElementById('quiz-step-3');
  const resultView = document.getElementById('quiz-result-view');
  const progressFill = document.getElementById('quiz-progress-fill');
  const stepIndicator = document.getElementById('quiz-step-indicator');
  if (!step1 || !resultView) return;

  let quizState = { p1: null, p2: null, p3: null };

  function setStep(stepNum) {
    step1.classList.toggle('active', stepNum === 1);
    step2.classList.toggle('active', stepNum === 2);
    step3.classList.toggle('active', stepNum === 3);
    resultView.classList.toggle('active', stepNum === 4);

    if (stepNum <= 3) {
      if (progressFill) progressFill.style.width = `${(stepNum / 3) * 100}%`;
      if (stepIndicator) stepIndicator.textContent = `Paso ${stepNum} de 3`;
    } else {
      if (progressFill) progressFill.style.width = '100%';
      if (stepIndicator) stepIndicator.textContent = '¡Resultado!';
    }
  }

  document.querySelectorAll('.quiz-option-card').forEach(btn => {
    btn.addEventListener('click', () => {
      const step = parseInt(btn.getAttribute('data-step') || '1', 10);
      const ans = btn.getAttribute('data-answer');

      if (step === 1) {
        quizState.p1 = ans;
        setStep(2);
      } else if (step === 2) {
        quizState.p2 = ans;
        setStep(3);
      } else if (step === 3) {
        quizState.p3 = ans;
        calculateResult();
        setStep(4);
      }
    });
  });

  document.getElementById('quiz-back-to-1')?.addEventListener('click', () => setStep(1));
  document.getElementById('quiz-back-to-2')?.addEventListener('click', () => setStep(2));
  document.getElementById('quiz-restart-btn')?.addEventListener('click', () => {
    quizState = { p1: null, p2: null, p3: null };
    setStep(1);
  });

  function calculateResult() {
    let matchedSku = 'PEK-PIS-011'; // Default: SKY
    let profileTitle = 'El Observador Real';
    let profileDesc = 'Tu gato necesita una atalaya elevada para sentirse seguro mientras domina visualmente toda la estancia.';

    const { p1, p2, p3 } = quizState;

    if (p1 === 'atleta') {
      profileTitle = 'El Atleta Intrépido';
      profileDesc = 'Tu gato tiene un instinto enérgico y dinámico. Requiere recorridos verticales, postes largos de rascado y desafíos para liberar su vitalidad.';
      if (p2 === 'pared') {
        matchedSku = p3 === 'manada' ? 'PEK-PAR-021' : 'PEK-PAR-001';
      } else {
        matchedSku = p3 === 'manada' ? 'PEK-PIS-030' : 'PEK-PIS-025';
      }
    } else if (p1 === 'zen') {
      profileTitle = 'El Zen Dormilón';
      profileDesc = 'Tu compañero prioriza la calma, el abrigo y las texturas acogedoras. Adora acurrucarse en refugios cerrados y plataformas acolchadas.';
      if (p2 === 'pared') {
        matchedSku = 'PEK-PAR-004';
      } else {
        matchedSku = 'PEK-PIS-005';
      }
    } else if (p1 === 'social') {
      profileTitle = 'El Curioso Guardián';
      profileDesc = 'Tu gato vive cada momento contigo y quiere estar cerca de tus rutinas sin perder su propio territorio de descanso y juego.';
      matchedSku = p2 === 'pared' ? 'PEK-PAR-002' : 'PEK-PIS-006';
    } else {
      profileTitle = 'El Observador Real';
      profileDesc = 'Tu gato es un estratega nato. Le apasiona vigilar las ventanas y contemplar el movimiento del hogar desde la altura.';
      matchedSku = p2 === 'pared' ? 'PEK-PAR-001' : 'PEK-PIS-011';
    }

    let product = state.products.find(p => p.sku === matchedSku) || state.products[0];

    const titleEl = document.getElementById('result-profile-title');
    const descEl  = document.getElementById('result-profile-desc');
    const imgEl   = document.getElementById('result-img');
    const catEl   = document.getElementById('result-category-pill');
    const nameEl  = document.getElementById('result-product-name');
    const dimsEl  = document.getElementById('result-product-dims');
    const priceEl = document.getElementById('result-product-price');

    if (titleEl) titleEl.textContent = profileTitle;
    if (descEl) descEl.textContent = profileDesc;
    if (imgEl && product) {
      imgEl.src = product.image;
      imgEl.alt = `Gimnasio recomendado ${product.name}`;
    }
    if (catEl && product) catEl.textContent = product.category;
    if (nameEl && product) nameEl.textContent = product.name;
    if (dimsEl && product) dimsEl.textContent = product.dimensions || '';
    if (priceEl && product) priceEl.textContent = formatCOP(product.price);

    if (product) {
      const msg = `Hola, PEKES. Hice el Test Michi en su web y el perfil de mi gato fue "${profileTitle}". El gimnasio recomendado es:

Producto: ${product.name}
Referencia: ${product.sku}
Precio publicado: ${formatCOP(product.price)}

¿Me pueden confirmar disponibilidad, colores y opciones de entrega en Cali?`;

      const waBtn = document.getElementById('result-wa-btn');
      if (waBtn) {
        waBtn.href = `https://wa.me/${state.whatsappNumber}?text=${encodeURIComponent(msg)}`;
        waBtn.onclick = () => {
          trackWhatsAppClick(product, 'michi_quiz_result');
        };
      }
    }
  }
}

// ==============================================================================
// Modo Ronroneo (Web Audio Synthesis)
// ==============================================================================
function initPurrMode() {
  const toggleBtn = document.getElementById('btn-purr-toggle');
  let audioCtx = null;
  let oscillator = null;
  let gainNode = null;
  let lfo = null;
  let isPurring = false;

  function startPurrSound() {
    try {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (!AudioContext) return;
      if (!audioCtx) audioCtx = new AudioContext();
      if (audioCtx.state === 'suspended') audioCtx.resume();

      gainNode = audioCtx.createGain();
      gainNode.gain.setValueAtTime(0.06, audioCtx.currentTime);

      const filter = audioCtx.createBiquadFilter();
      filter.type = 'lowpass';
      filter.frequency.setValueAtTime(95, audioCtx.currentTime);

      oscillator = audioCtx.createOscillator();
      oscillator.type = 'triangle';
      oscillator.frequency.setValueAtTime(28, audioCtx.currentTime);

      lfo = audioCtx.createOscillator();
      const lfoGain = audioCtx.createGain();
      lfo.frequency.setValueAtTime(22, audioCtx.currentTime);
      lfoGain.gain.setValueAtTime(14, audioCtx.currentTime);

      lfo.connect(lfoGain);
      lfoGain.connect(oscillator.frequency);

      oscillator.connect(filter);
      filter.connect(gainNode);
      gainNode.connect(audioCtx.destination);

      oscillator.start();
      lfo.start();
      isPurring = true;
    } catch (e) {
      console.log('[Purr Audio] Note:', e);
    }
  }

  function stopPurrSound() {
    if (oscillator) {
      try { oscillator.stop(); lfo.stop(); } catch(e) {}
      oscillator = null;
      lfo = null;
    }
    isPurring = false;
  }

  toggleBtn?.addEventListener('click', () => {
    const textSpan = toggleBtn.querySelector('.purr-text');
    if (!isPurring) {
      startPurrSound();
      toggleBtn.classList.add('active');
      if (textSpan) textSpan.textContent = 'Ronroneando...';
    } else {
      stopPurrSound();
      toggleBtn.classList.remove('active');
      if (textSpan) textSpan.textContent = 'Modo Michi';
    }
  });

  document.addEventListener('click', (e) => {
    if (e.target.closest('a, button, input, textarea')) return;
    spawnPawStamp(e.clientX, e.clientY);
  });

  function spawnPawStamp(x, y) {
    const paw = document.createElement('div');
    paw.className = 'paw-stamp';
    paw.textContent = '·';
    paw.style.left = `${x}px`;
    paw.style.top = `${y}px`;
    paw.style.color = isPurring ? '#8269B2' : 'rgba(130, 105, 178, 0.45)';
    document.body.appendChild(paw);
    setTimeout(() => {
      paw.remove();
    }, 1200);
  }
}

// Hero Carousel Slider
function initHeroSlider() {
  const slides = document.querySelectorAll('.hero-slide');
  const dots   = document.querySelectorAll('.slider-dot');
  const prevBtn = document.getElementById('hero-prev-btn');
  const nextBtn = document.getElementById('hero-next-btn');
  const slider  = document.getElementById('hero-slider');
  if (!slides.length) return;

  let currentSlide = 0;
  let autoplayTimer = null;

  function showSlide(index) {
    if (index >= slides.length) index = 0;
    if (index < 0) index = slides.length - 1;
    currentSlide = index;

    slides.forEach((s, i) => s.classList.toggle('active', i === currentSlide));
    dots.forEach((d, i) => d.classList.toggle('active', i === currentSlide));
  }

  function startAutoplay() {
    stopAutoplay();
    autoplayTimer = setInterval(() => {
      showSlide(currentSlide + 1);
    }, 4500);
  }

  function stopAutoplay() {
    if (autoplayTimer) clearInterval(autoplayTimer);
  }

  nextBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    showSlide(currentSlide + 1);
    startAutoplay();
  });

  prevBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    showSlide(currentSlide - 1);
    startAutoplay();
  });

  dots.forEach(dot => {
    dot.addEventListener('click', (e) => {
      e.stopPropagation();
      const idx = parseInt(dot.getAttribute('data-index') || '0', 10);
      showSlide(idx);
      startAutoplay();
    });
  });

  slides.forEach(slide => {
    slide.addEventListener('click', () => {
      const pId = slide.getAttribute('data-product-id');
      const prod = state.products.find(p => p.id === pId);
      if (prod) openProductModal(prod);
    });
  });

  slider?.addEventListener('mouseenter', stopAutoplay);
  slider?.addEventListener('mouseleave', startAutoplay);

  startAutoplay();
}

// Interactive Cat Mascot Peeker
function initCatPeeker() {
  const peeker = document.getElementById('cat-peeker');
  const bubble = document.getElementById('cat-bubble');
  const closeBtn = document.getElementById('cat-bubble-close');
  const catChar  = document.getElementById('cat-character-wrap');
  if (!peeker || !bubble) return;

  setTimeout(() => {
    bubble.classList.add('active');
  }, 3500);

  closeBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    bubble.classList.remove('active');
  });

  catChar?.addEventListener('click', () => {
    bubble.classList.toggle('active');
  });
}

// Scroll Reveal
function initScrollReveal() {
  const targets = document.querySelectorAll(
    '.pillar-card, .flow-step, .lifestyle-card, .faq-item, .section-head, .hero-content, .hero-visual'
  );
  targets.forEach(el => el.classList.add('reveal'));

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, i * 60);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  targets.forEach(el => observer.observe(el));
}

// Init Event Listeners
function initEvents() {
  // Category tabs
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      state.activeFilter = btn.getAttribute('data-category');
      renderProducts();
    });
  });

  // Search input
  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      state.searchQuery = e.target.value;
      renderProducts();
    });
  }

  // Delegación de clics en la cuadrícula de productos (Abre ficha emergente al instante)
  const grid = document.getElementById('products-grid');
  if (grid) {
    grid.addEventListener('click', (e) => {
      // Si el clic fue directamente en el botón de WhatsApp rápido
      const waBtn = e.target.closest('.card-btn-wa');
      if (waBtn) {
        const card = waBtn.closest('.product-card');
        const id = card?.getAttribute('data-id');
        const prod = state.products.find(p => p.id === id);
        trackWhatsAppClick(prod, 'catalog_card');
        return;
      }

      // Si el clic fue en la tarjeta, botón "Ver Ficha", imagen o título
      const card = e.target.closest('.product-card');
      if (card) {
        e.preventDefault();
        const id = card.getAttribute('data-id');
        const slug = card.getAttribute('data-slug');
        const prod = state.products.find(p => p.id === id || (slug && (p.slug === slug || getProductSlug(p) === slug)));
        if (prod) {
          openProductModal(prod);
        }
      }
    });

    grid.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        const card = e.target.closest('.product-card');
        if (card && !e.target.closest('.card-btn-wa')) {
          e.preventDefault();
          const id = card.getAttribute('data-id');
          const slug = card.getAttribute('data-slug');
          const prod = state.products.find(p => p.id === id || (slug && (p.slug === slug || getProductSlug(p) === slug)));
          if (prod) openProductModal(prod);
        }
      }
    });
  }

  // Modal close handlers
  document.getElementById('modal-close-btn')?.addEventListener('click', closeProductModal);
  document.getElementById('product-modal')?.addEventListener('click', (e) => {
    if (e.target.id === 'product-modal') closeProductModal();
  });

  // Legal Modal Triggers
  document.querySelectorAll('.legal-trigger').forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      const tab = trigger.getAttribute('data-legal') || 'privacidad';
      openLegalModal(tab);
    });
  });

  document.querySelectorAll('.legal-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetPane = btn.getAttribute('data-target');
      document.querySelectorAll('.legal-tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      document.querySelectorAll('.legal-pane').forEach(pane => {
        pane.classList.toggle('active', pane.id === targetPane);
      });
    });
  });

  document.getElementById('legal-close-btn')?.addEventListener('click', closeLegalModal);
  document.getElementById('legal-modal')?.addEventListener('click', (e) => {
    if (e.target.id === 'legal-modal') closeLegalModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeProductModal();
      closeLegalModal();
    }
  });

  // Header quick WhatsApp action
  document.getElementById('btn-header-wa')?.addEventListener('click', () => {
    trackWhatsAppClick(null, 'header_button');
  });

  // Floating WhatsApp action
  document.getElementById('floating-wa-btn')?.addEventListener('click', () => {
    trackWhatsAppClick(null, 'floating_button');
  });

  // Sticky header shadow
  window.addEventListener('scroll', () => {
    const header = document.querySelector('.site-header');
    if (header) {
      header.classList.toggle('scrolled', window.scrollY > 20);
    }
  });

  // Initialize interactive features
  initHeroSlider();
  initCatPeeker();
  initMichiQuiz();
  initPurrMode();
  initScrollReveal();
}

// App Launch
document.addEventListener('DOMContentLoaded', async () => {
  // Asegurar carga de los 59 productos en cualquier entorno
  if (!state.products || state.products.length === 0) {
    if (window.PEKES_PRODUCTS && Array.isArray(window.PEKES_PRODUCTS) && window.PEKES_PRODUCTS.length > 0) {
      state.products = window.PEKES_PRODUCTS;
      renderProducts();
    } else {
      const endpoints = [
        '/data/products.json',
        '/public/data/products.json',
        'data/products.json',
        'public/data/products.json',
        '/api/products.php'
      ];
      let loaded = false;
      for (const ep of endpoints) {
        try {
          const r = await fetch(ep);
          if (r.ok) {
            const data = await r.json();
            if (Array.isArray(data) && data.length > 0) {
              state.products = data;
              window.PEKES_PRODUCTS = data;
              renderProducts();
              loaded = true;
              break;
            }
          }
        } catch (_) {}
      }
      if (!loaded) {
        console.warn('[PEKES Storefront] No se pudieron cargar productos vía fetch');
      }
    }
  } else {
    renderProducts();
  }

  initEvents();

  // Si hay un hash (#producto-xxx), abrir directamente el modal correspondiente sin romper la pantalla
  const hash = window.location.hash;
  if (hash && hash.startsWith('#producto-')) {
    const legacyId = hash.replace('#producto-', '');
    const found = state.products.find(p => p.id === legacyId || getProductSlug(p) === legacyId);
    if (found) {
      setTimeout(() => openProductModal(found), 150);
    }
  }

  console.log('[PEKES Storefront] Ready with MiaCara Editorial design. Products count:', state.products.length);
});

