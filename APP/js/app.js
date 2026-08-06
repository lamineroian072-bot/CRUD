let appState = {
  products: [],
  filteredProducts: [],
  orders: [],
  viewMode: 'grid', // 'grid' or 'table'
  globalCondition: 'All'
};

let productModal = null;
let orderModal = null;
let compareModal = null;

document.addEventListener("DOMContentLoaded", () => {
  productModal = new bootstrap.Modal(document.getElementById('productModal'));
  orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
  compareModal = new bootstrap.Modal(document.getElementById('compareModal'));
  
  // Attach search listener
  document.getElementById('globalSearch').addEventListener('input', applyFilters);
  
  loadDashboardData();
});

async function loadDashboardData() {
  try {
    const res = await fetch('api.php?action=get_dashboard_data');
    const data = await res.json();

    if (data.success) {
      appState.products = data.products;
      appState.filteredProducts = [...data.products];
      appState.orders = data.orders || [];

      renderMetrics(data.metrics);
      updateMaxPriceSlider();
      applyFilters();
      renderOrdersTable();
      populateCompareSelects();
    }
  } catch (err) {
    showToast("Error connecting to server.", "danger");
  }
}

function renderMetrics(m) {
  document.getElementById("kpiTotalInv").innerText = m.totalProducts.toLocaleString();
  document.getElementById("kpiInvBreakdown").innerHTML = `<span class="text-success fw-medium">${m.brandNewCount} Brand New</span> &bull; <span class="text-warning fw-medium">${m.secondhandCount} Pre-Owned</span>`;
  
  document.getElementById("kpiMargin").innerText = `${m.avgDepreciation}%`;
  
  // Update Orders KPI
  document.getElementById("kpiOrders").innerText = m.totalOrders.toLocaleString();
  document.getElementById("kpiRevenue").innerText = "₱" + m.totalRevenue.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + " Revenue";
  
  document.getElementById("kpiValuation").innerText = "₱" + m.totalValuation.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function updateMaxPriceSlider() {
  const maxPrice = appState.products.reduce((max, p) => Math.max(max, parseFloat(p.price) || 0), 0);
  const slider = document.getElementById("priceRange");
  slider.max = Math.ceil(maxPrice / 1000) * 1000 || 150000;
  slider.value = slider.max;
  updatePriceLabel();
}

function updatePriceLabel() {
  const val = document.getElementById("priceRange").value;
  document.getElementById("priceLabel").innerText = `Max: ₱${parseInt(val).toLocaleString()}`;
}

function setGlobalFilter(condition) {
  appState.globalCondition = condition;
  
  // Update toggle UI
  document.querySelectorAll('.saas-toggle button').forEach(b => b.classList.remove('active', 'bg-dark', 'text-white'));
  if (condition === 'All') document.getElementById('toggleAll').classList.add('active', 'bg-dark', 'text-white');
  if (condition === 'Brand New') document.getElementById('toggleNew').classList.add('active', 'bg-dark', 'text-white');
  if (condition === 'Secondhand') document.getElementById('toggleUsed').classList.add('active', 'bg-dark', 'text-white');
  
  applyFilters();
}

function applyFilters() {
  const search = document.getElementById('globalSearch').value.toLowerCase();
  const maxPrice = parseFloat(document.getElementById('priceRange').value);
  const filterNew = document.getElementById('filterNew').checked;
  const filterUsed = document.getElementById('filterUsed').checked;
  const filterWarranty = document.getElementById('filterWarranty').value;

  appState.filteredProducts = appState.products.filter(p => {
    // 1. Global Search
    const matchesSearch = p.model.toLowerCase().includes(search) || (p.condition_grade || "").toLowerCase().includes(search);
    
    // 2. Global Condition Toggle
    if (appState.globalCondition !== 'All' && p.condition_type !== appState.globalCondition) return false;
    
    // 3. Sidebar Condition checkboxes
    const isNew = p.condition_type === 'Brand New';
    if (isNew && !filterNew) return false;
    if (!isNew && !filterUsed) return false;
    
    // 4. Price
    if (parseFloat(p.price) > maxPrice) return false;
    
    // 5. Warranty
    if (filterWarranty && p.warranty_status !== filterWarranty) return false;

    return matchesSearch;
  });

  renderInventory();
}

function setViewMode(mode) {
  appState.viewMode = mode;
  document.getElementById('btnViewGrid').classList.toggle('active', mode === 'grid');
  document.getElementById('btnViewTable').classList.toggle('active', mode === 'table');
  renderInventory();
}

function renderInventory() {
  const container = document.getElementById("inventoryContainer");
  if (appState.filteredProducts.length === 0) {
    container.innerHTML = `<div class="text-center text-secondary py-5"><i class="bi bi-inbox fs-1"></i><p class="mt-2">No inventory matches your filters.</p></div>`;
    return;
  }

  if (appState.viewMode === 'grid') {
    renderGrid(container);
  } else {
    renderTable(container);
  }
}

function renderGrid(container) {
  let html = `<div class="row g-4">`;
  appState.filteredProducts.forEach(p => {
    const isNew = p.condition_type === 'Brand New';
    const badgeHtml = isNew 
      ? `<span class="badge badge-new px-3 py-2 rounded-pill"><i class="bi bi-stars"></i> BRAND NEW</span>`
      : `<span class="badge badge-used px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill"></i> PRE-OWNED ${p.condition_grade ? '| ' + escapeHtml(p.condition_grade) : ''}</span>`;
      
    const thumb = p.thumbnail_url || `https://placehold.co/600x400/1E293B/F8FAFC?text=${encodeURIComponent(p.model)}`;
    
    let priceHtml = '';
    const priceFormatted = "₱" + parseFloat(p.price).toLocaleString('en-US', { minimumFractionDigits: 0 });
    const msrpFormatted = "₱" + parseFloat(p.msrp || 0).toLocaleString('en-US', { minimumFractionDigits: 0 });
    
    if (isNew) {
      priceHtml = `
        <div class="fs-8 text-secondary text-uppercase mb-1">Dealer Price</div>
        <div class="d-flex align-items-end gap-2">
          <h3 class="fw-bold m-0 text-white">${priceFormatted}</h3>
          ${p.msrp > p.price ? `<span class="text-decoration-line-through text-secondary fs-7">MSRP ${msrpFormatted}</span>` : ''}
        </div>
      `;
    } else {
      const savings = p.msrp > 0 ? Math.round(((p.msrp - p.price) / p.msrp) * 100) : 0;
      priceHtml = `
        <div class="fs-8 text-secondary text-uppercase mb-1">Market Value</div>
        <div class="d-flex align-items-end gap-2">
          <h3 class="fw-bold m-0 text-white">${priceFormatted}</h3>
          ${savings > 0 ? `<span class="badge bg-success bg-opacity-25 text-success rounded-1">Save ${savings}%</span>` : ''}
        </div>
        ${p.msrp > 0 ? `<div class="fs-8 text-secondary mt-1">Originally ${msrpFormatted}</div>` : ''}
      `;
    }

    const inspectionHtml = !isNew && p.inspection_status ? `<div class="fs-8 mt-2 text-info"><i class="bi bi-shield-check"></i> ${escapeHtml(p.inspection_status)}</div>` : '';
    const mileageHtml = !isNew && p.mileage ? `<div class="fs-8 text-secondary mt-1"><i class="bi bi-speedometer2"></i> ${escapeHtml(p.mileage)}</div>` : '';

    html += `
      <div class="col-12 col-md-6 col-xxl-4">
        <div class="product-card" onclick="editProduct(${p.id})">
          <div class="card-badge-top">${badgeHtml}</div>
          <div class="product-img-wrapper">
            <img src="${escapeHtml(thumb)}" alt="${escapeHtml(p.model)}" loading="lazy">
          </div>
          <div class="p-4 d-flex flex-column flex-grow-1">
            <h5 class="fw-bold mb-1">${escapeHtml(p.model)}</h5>
            <div class="text-secondary fs-7 mb-3">${escapeHtml(p.storage)} &bull; ${escapeHtml(p.warranty_status || 'Standard Warranty')}</div>
            
            <div class="mt-auto pt-3 border-top border-secondary border-opacity-50">
              ${priceHtml}
              ${inspectionHtml}
              ${mileageHtml}
              <div class="d-flex justify-content-between align-items-center mt-3 pt-2">
                 <span class="fs-8 fw-medium ${p.stock > 0 ? 'text-success' : 'text-danger'}"><i class="bi bi-box"></i> ${p.stock} in stock</span>
                 <button class="btn btn-sm btn-primary rounded-pill px-3">View Details</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  html += `</div>`;
  container.innerHTML = html;
}

function renderTable(container) {
  let html = `
    <div class="table-responsive rounded-4 border border-secondary border-opacity-50">
      <table class="table table-dark table-hover align-middle mb-0">
        <thead class="bg-body-secondary text-uppercase fs-8 text-secondary border-bottom border-secondary border-opacity-50">
          <tr>
            <th class="ps-4">Model</th>
            <th>Condition</th>
            <th>Price</th>
            <th>MSRP</th>
            <th>Stock</th>
            <th>Warranty</th>
            <th class="text-end pe-4">Actions</th>
          </tr>
        </thead>
        <tbody>
  `;
  
  appState.filteredProducts.forEach(p => {
    html += `
      <tr>
        <td class="ps-4 fw-bold text-white d-flex align-items-center gap-3">
          <img src="${escapeHtml(p.thumbnail_url || `https://placehold.co/100x100?text=No+Img`)}" class="rounded" width="40" height="40" style="object-fit:cover;">
          <div>
            ${escapeHtml(p.model)}<br>
            <span class="fs-8 text-secondary fw-normal">${escapeHtml(p.storage)}</span>
          </div>
        </td>
        <td>${p.condition_type === 'Brand New' ? '<span class="text-success fw-semibold">Brand New</span>' : `<span class="text-warning fw-semibold">Pre-Owned (${escapeHtml(p.condition_grade)})</span>`}</td>
        <td class="fw-bold">₱${parseFloat(p.price).toLocaleString()}</td>
        <td class="text-secondary">₱${parseFloat(p.msrp || 0).toLocaleString()}</td>
        <td>${p.stock}</td>
        <td>${escapeHtml(p.warranty_status || 'N/A')}</td>
        <td class="text-end pe-4">
          <button class="btn btn-sm btn-outline-secondary rounded-3 me-1" onclick="editProduct(${p.id})"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger rounded-3" onclick="deleteProduct(${p.id})"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `;
  });
  
  html += `</tbody></table></div>`;
  container.innerHTML = html;
}

/* Orders Rendering */
function renderOrdersTable() {
  const tbody = document.getElementById("ordersTableBody");
  tbody.innerHTML = "";

  if (appState.orders.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-secondary py-4">No orders found.</td></tr>`;
    return;
  }

  appState.orders.forEach(o => {
    let statusClass = 'text-warning bg-warning';
    if (o.status === 'Completed') statusClass = 'text-success bg-success';
    if (o.status === 'Cancelled') statusClass = 'text-danger bg-danger';
    
    const statusBadge = `<span class="badge ${statusClass} bg-opacity-10 border border-opacity-25 px-2 py-1 fs-8">${o.status}</span>`;

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td class="ps-4 font-monospace text-primary fw-medium">${escapeHtml(o.order_code)}</td>
      <td class="fw-semibold text-white">${escapeHtml(o.customer)}</td>
      <td class="text-secondary fs-7">${escapeHtml(o.contact)}</td>
      <td>${escapeHtml(o.model)} (${escapeHtml(o.storage)}) <span class="text-secondary">x${o.quantity}</span></td>
      <td class="fw-bold text-success">₱${parseFloat(o.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
      <td>${statusBadge}</td>
      <td class="text-end pe-4">
        <button class="btn btn-sm btn-outline-danger rounded-3" onclick="deleteOrder(${o.id})"><i class="bi bi-trash"></i></button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function openOrderModal() {
  const select = document.getElementById("ordProduct");
  select.innerHTML = '<option value="">Select a product...</option>';

  appState.products.forEach(p => {
    if (p.stock > 0) {
      const opt = document.createElement("option");
      opt.value = p.id;
      opt.innerText = `${p.model} (${p.storage}) - ₱${parseFloat(p.price).toLocaleString()} [Stock: ${p.stock}]`;
      select.appendChild(opt);
    }
  });

  document.getElementById("orderForm").reset();
  orderModal.show();
}

async function saveOrder(e) {
  e.preventDefault();
  const formData = new FormData(document.getElementById("orderForm"));

  try {
    const res = await fetch('api.php?action=create_order', { method: 'POST', body: formData });
    const result = await res.json();
    if (result.success) {
      orderModal.hide();
      loadDashboardData();
      showToast(result.message, "success");
    } else {
      showToast(result.message, "danger");
    }
  } catch (err) {
    showToast("Failed to create order.", "danger");
  }
}

async function deleteOrder(id) {
  if (confirm("Delete this order record?")) {
    try {
      const res = await fetch(`api.php?action=delete_order&id=${id}`);
      const result = await res.json();
      if (result.success) {
        loadDashboardData();
        showToast(result.message, "success");
      }
    } catch (err) {
      showToast("Delete failed.", "danger");
    }
  }
}

/* Product CRUD */
function openProductModal() {
  document.getElementById("productModalTitle").innerHTML = "<i class='bi bi-plus-circle text-primary me-2'></i> Add Product";
  document.getElementById("productForm").reset();
  document.getElementById("prodId").value = "";
  productModal.show();
}

function editProduct(id) {
  const p = appState.products.find(item => item.id == id);
  if (!p) return;

  document.getElementById("productModalTitle").innerHTML = "<i class='bi bi-pencil-square text-primary me-2'></i> Edit Product";
  document.getElementById("prodId").value = p.id;
  document.getElementById("prodModel").value = p.model;
  document.getElementById("prodCondition").value = p.condition_type;
  document.getElementById("prodStorage").value = p.storage;
  document.getElementById("prodPrice").value = parseFloat(p.price) || '';
  document.getElementById("prodStock").value = p.stock;
  document.getElementById("prodMsrp").value = parseFloat(p.msrp) || '';
  document.getElementById("prodGrade").value = p.condition_grade;
  document.getElementById("prodMileage").value = p.mileage;
  document.getElementById("prodWarranty").value = p.warranty_status;
  document.getElementById("prodInspect").value = p.inspection_status;
  document.getElementById("prodThumb").value = p.thumbnail_url;

  productModal.show();
}

async function saveProduct(e) {
  e.preventDefault();
  const form = document.getElementById("productForm");
  const formData = new FormData(form);
  const id = document.getElementById("prodId").value;
  const action = id ? 'update_product' : 'create_product';

  try {
    const res = await fetch(`api.php?action=${action}`, { method: 'POST', body: formData });
    const result = await res.json();
    if (result.success) {
      productModal.hide();
      loadDashboardData();
      showToast(result.message, "success");
    } else {
      showToast(result.message, "danger");
    }
  } catch (err) {
    showToast("Failed to save product.", "danger");
  }
}

async function deleteProduct(id) {
  if (confirm("Are you sure you want to delete this product?")) {
    try {
      const res = await fetch(`api.php?action=delete_product&id=${id}`);
      const result = await res.json();
      if (result.success) {
        loadDashboardData();
        showToast(result.message, "success");
      }
    } catch (err) {
      showToast("Delete failed.", "danger");
    }
  }
}

/* Compare Modal Logic */
function populateCompareSelects() {
  const sNew = document.getElementById("compareNewSelect");
  const sUsed = document.getElementById("compareUsedSelect");
  sNew.innerHTML = '<option value="">Select...</option>';
  sUsed.innerHTML = '<option value="">Select...</option>';
  
  appState.products.forEach(p => {
    const opt = `<option value="${p.id}">${escapeHtml(p.model)} - ₱${parseFloat(p.price).toLocaleString()}</option>`;
    if (p.condition_type === 'Brand New') {
      sNew.innerHTML += opt;
    } else {
      sUsed.innerHTML += opt;
    }
  });
}

function openCompareModal() {
  document.getElementById('paneNew').innerHTML = '<p class="text-secondary py-5">Select a brand new vehicle.</p>';
  document.getElementById('paneUsed').innerHTML = '<p class="text-secondary py-5">Select a pre-owned equivalent.</p>';
  document.getElementById('savingsDelta').style.display = 'none';
  compareModal.show();
}

function renderCompare() {
  const idNew = document.getElementById("compareNewSelect").value;
  const idUsed = document.getElementById("compareUsedSelect").value;
  
  const pNew = appState.products.find(p => p.id == idNew);
  const pUsed = appState.products.find(p => p.id == idUsed);
  
  const paneNew = document.getElementById('paneNew');
  const paneUsed = document.getElementById('paneUsed');
  
  if (pNew) {
    paneNew.innerHTML = buildCompareCardHTML(pNew, true);
  }
  
  if (pUsed) {
    paneUsed.innerHTML = buildCompareCardHTML(pUsed, false);
  }
  
  if (pNew && pUsed) {
    const delta = pNew.price - pUsed.price;
    const percent = Math.round((delta / pNew.price) * 100);
    const savingsBox = document.getElementById('savingsDelta');
    if (delta > 0) {
      savingsBox.style.display = 'block';
      document.getElementById('savingsText').innerText = `You Save: ₱${delta.toLocaleString()} (${percent}% less than new)`;
    } else {
      savingsBox.style.display = 'none';
    }
  }
}

function buildCompareCardHTML(p, isNew) {
  const priceFormatted = "₱" + parseFloat(p.price).toLocaleString();
  return `
    <img src="${escapeHtml(p.thumbnail_url || 'https://placehold.co/600x400/1E293B/F8FAFC?text=No+Img')}" class="img-fluid rounded mb-3" style="height:200px; object-fit:cover; width:100%;">
    <h4 class="fw-bold">${escapeHtml(p.model)}</h4>
    <div class="mb-3">
      ${isNew ? '<span class="badge badge-new"><i class="bi bi-stars"></i> Brand New</span>' : '<span class="badge badge-used"><i class="bi bi-arrow-repeat"></i> Pre-Owned</span>'}
    </div>
    <h2 class="fw-bold ${isNew ? 'text-white' : 'text-warning'} mb-4">${priceFormatted}</h2>
    
    <ul class="list-unstyled text-start mb-0">
      <li class="py-2 border-bottom border-secondary border-opacity-50"><i class="bi bi-cpu text-primary me-2"></i> ${escapeHtml(p.storage || 'Standard Specs')}</li>
      <li class="py-2 border-bottom border-secondary border-opacity-50"><i class="bi bi-shield-check text-primary me-2"></i> ${escapeHtml(p.warranty_status || 'Standard Warranty')}</li>
      ${!isNew ? `<li class="py-2 border-bottom border-secondary border-opacity-50"><i class="bi bi-clipboard2-check text-primary me-2"></i> Grade: ${escapeHtml(p.condition_grade || 'N/A')}</li>` : ''}
      ${!isNew ? `<li class="py-2"><i class="bi bi-speedometer2 text-primary me-2"></i> ${escapeHtml(p.mileage || 'N/A')}</li>` : `<li class="py-2"><i class="bi bi-car-front text-primary me-2"></i> 0 Miles (Factory)</li>`}
    </ul>
  `;
}

function resetFilters() {
  document.getElementById("globalSearch").value = "";
  document.getElementById("filterNew").checked = true;
  document.getElementById("filterUsed").checked = true;
  document.getElementById("filterWarranty").value = "";
  setGlobalFilter('All');
  updateMaxPriceSlider();
  applyFilters();
}

function showToast(msg, type = "success") {
  const container = document.getElementById('toastContainer');
  const toastEl = document.createElement('div');
  toastEl.className = `toast align-items-center text-bg-${type} border-0 show shadow mb-2 rounded-3`;
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center gap-2">
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'info-circle-fill'}"></i>
        <span>${msg}</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  container.appendChild(toastEl);
  setTimeout(() => toastEl.remove(), 3500);
}

function escapeHtml(str) {
  return String(str || '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  })[m]);
}