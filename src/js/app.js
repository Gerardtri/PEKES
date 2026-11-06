import { PRODUCTS } from '../data/products.js';

// Application State
const state = {
  products: PRODUCTS,
  activeFilter: 'all', // 'all', 'Gimnasios de piso', 'Gimnasios de pared', 'Rascadores'
  searchQuery: '',
  isPrecioPendienteSimulated: false,
  whatsappNumber: '573001234567', // Configurable
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
  showToast(`📊 Evento "click_whatsapp" emitido hacia GA4 & Meta Pixel`, payload);

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
}

// App Launch
document.addEventListener('DOMContentLoaded', () => {
  renderProducts();
  initEvents();
  console.log('[PEKES Storefront] Ready with products loaded.');
});
