import { PRODUCTS } from '../data/products.js';

// Application State
const state = {
  products: PRODUCTS,
  activeFilter: 'all', // 'all', 'Gimnasios de piso', 'Gimnasios de pared', 'Rascadores'
  searchQuery: '',
  isPrecioPendienteSimulated: false,
  whatsappNumber: '573215427745', // WhatsApp oficial provisto por PEKES
  selectedProduct: null
};

// Utilities
function formatCOP(val) {
  if (val === null || val === undefined || isNaN(val)) return 'N/A';
  return '$' + val.toLocaleString('es-CO');
}

function showToast(message, payload) {
  const toast = document.getElementById('analytics-toast');
  const toastMsg = document.getElementById('toast-message');
  const toastCode = document.getElementById('toast-payload');
  
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

  const productUrl = `${window.location.origin}/#producto-${product.id}`;
  
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

// Analytics Trigger (Simulating Shopify.analytics.publish and GA4/Meta event)
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
  showToast(`Evento "click_whatsapp" emitido hacia GA4 & Meta Pixel`, payload);

  // Dispatch simulated custom event for theme listeners
  window.dispatchEvent(new CustomEvent('pekes:click_whatsapp', { detail: payload }));
}

// Render Products Grid
function renderProducts() {
  const grid = document.getElementById('products-grid');
  const countBadge = document.getElementById('catalog-count');
  
  let filtered = state.products.filter(p => {
    // Category match
    if (state.activeFilter !== 'all' && p.category !== state.activeFilter) {
      return false;
    }
    // Search match
    if (state.searchQuery.trim()) {
      const q = state.searchQuery.toLowerCase();
      const matchName = p.name.toLowerCase().includes(q);
      const matchDims = p.dimensions.toLowerCase().includes(q);
      const matchSku = p.sku.toLowerCase().includes(q);
      return matchName || matchDims || matchSku;
    }
    return true;
  });

  if (countBadge) {
    countBadge.textContent = `${filtered.length} productos`;
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
    const priceDisplay = isPending
      ? `<span class="card-price pending">Precio por confirmar por WhatsApp</span>`
      : `<span class="card-price">${formatCOP(p.price)}</span>`;

    const categoryBadge = p.category === 'Gimnasios de pared' ? 'Pared' : (p.category === 'Rascadores' ? 'Rascador' : 'Piso');

    return `
      <article class="product-card" data-id="${p.id}" tabindex="0" role="button" aria-label="Ver detalles de ${p.name}">
        <div class="card-img-container">
          <span class="card-badge">Por encargo</span>
          <span class="card-badge stage-badge">${categoryBadge}</span>
          <img src="${p.image}" alt="Gimnasio para gatos ${p.name}" loading="lazy" />
        </div>
        <div class="card-body">
          <span class="card-sku">${p.sku}</span>
          <h3 class="card-title">${p.name}</h3>
          <p class="card-dims">${p.dimensions}</p>
          <div class="card-price-row">
            ${priceDisplay}
            <span class="card-btn-view">Ver más →</span>
          </div>
        </div>
      </article>
    `;
  }).join('');

  // Attach card click handlers
  grid.querySelectorAll('.product-card').forEach(card => {
    const id = card.getAttribute('data-id');
    const product = state.products.find(p => p.id === id);
    card.addEventListener('click', () => openProductModal(product));
    card.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openProductModal(product);
      }
    });
  });
}

// Modal Logic
function openProductModal(product) {
  state.selectedProduct = product;
  const isPending = state.isPrecioPendienteSimulated;
  const modal = document.getElementById('product-modal');
  
  const { rawMessage, url } = buildWhatsAppUrl(product, isPending);

  document.getElementById('modal-img').src = product.image;
  document.getElementById('modal-img').alt = product.name;
  document.getElementById('modal-title').textContent = product.name;
  document.getElementById('modal-sku').textContent = `SKU: ${product.sku}`;
  document.getElementById('modal-cat').textContent = product.category;
  document.getElementById('modal-desc').textContent = product.description;
  document.getElementById('modal-dims').textContent = product.dimensions;

  const priceValEl = document.getElementById('modal-price-val');
  if (isPending) {
    priceValEl.textContent = 'Precio por confirmar por WhatsApp';
    priceValEl.className = 'modal-price-val pending';
  } else {
    priceValEl.textContent = formatCOP(product.price);
    priceValEl.className = 'modal-price-val';
  }

  // Render includes checklist
  const includesList = document.getElementById('modal-includes-list');
  if (product.includes && product.includes.length > 0) {
    document.getElementById('modal-includes-box').style.display = 'block';
    includesList.innerHTML = product.includes.map(item => `<li>${item}</li>`).join('');
  } else {
    document.getElementById('modal-includes-box').style.display = 'none';
  }

  // Preview Message
  document.getElementById('modal-wa-preview').textContent = rawMessage;

  // Button link
  const btnWa = document.getElementById('modal-btn-wa');
  btnWa.href = url;
  btnWa.onclick = (e) => {
    trackWhatsAppClick(product, 'product_modal');
  };

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeProductModal() {
  const modal = document.getElementById('product-modal');
  if (!modal) return;
  modal.classList.remove('active');
  document.body.style.overflow = '';
}

// Legal Modal Controllers
function openLegalModal(tabKey = 'privacidad') {
  const modal = document.getElementById('legal-modal');
  if (!modal) return;

  const targetPaneId = `pane-${tabKey}`;
  
  // Switch tab buttons
  document.querySelectorAll('.legal-tab-btn').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-target') === targetPaneId);
  });

  // Switch panes
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

// Init Event Listeners
function initEvents() {
  // Category tabs
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
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

  // Modal close handlers (Product)
  document.getElementById('modal-close-btn')?.addEventListener('click', closeProductModal);
  document.getElementById('product-modal')?.addEventListener('click', (e) => {
    if (e.target.id === 'product-modal') closeProductModal();
  });

  // Legal Modal Triggers & Controls
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
    if (window.scrollY > 20) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

  // Hero Slider & Cat Peeker Controllers
  initHeroSlider();
  initCatPeeker();
  initMichiQuiz();
  initPurrMode();
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

  let quizState = {
    p1: null,
    p2: null,
    p3: null
  };

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

  // Options click
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

  // Back buttons
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
    const descEl = document.getElementById('result-profile-desc');
    const imgEl = document.getElementById('result-img');
    const catEl = document.getElementById('result-category-pill');
    const nameEl = document.getElementById('result-product-name');
    const dimsEl = document.getElementById('result-product-dims');
    const priceEl = document.getElementById('result-product-price');

    if (titleEl) titleEl.textContent = profileTitle;
    if (descEl) descEl.textContent = profileDesc;
    if (imgEl) {
      imgEl.src = product.image;
      imgEl.alt = `Gimnasio recomendado ${product.name}`;
    }
    if (catEl) catEl.textContent = product.category;
    if (nameEl) nameEl.textContent = product.name;
    if (dimsEl) dimsEl.textContent = product.dimensions;
    if (priceEl) priceEl.textContent = formatCOP(product.price);

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

// ==============================================================================
// Modo Ronroneo (Web Audio Synthesis + Huellitas Interactivas)
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

// Hero Banner Carousel Controller
function initHeroSlider() {
  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.slider-dot');
  const prevBtn = document.getElementById('hero-prev-btn');
  const nextBtn = document.getElementById('hero-next-btn');
  const slider = document.getElementById('hero-slider');
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

  // Clicking a slide opens the corresponding product modal
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

// Interactive Cat Mascot Peeker Controller
function initCatPeeker() {
  const peeker = document.getElementById('cat-peeker');
  const bubble = document.getElementById('cat-bubble');
  const closeBtn = document.getElementById('cat-bubble-close');
  const catChar = document.getElementById('cat-character-wrap');
  if (!peeker || !bubble) return;

  // Pop up speech bubble gracefully after 3.5 seconds
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

// App Launch
document.addEventListener('DOMContentLoaded', () => {
  renderProducts();
  initEvents();
  initScrollReveal();
  console.log('[PEKES Storefront] Ready with products loaded.');
});

// Scroll Reveal — microinteraction de aparición progresiva
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
