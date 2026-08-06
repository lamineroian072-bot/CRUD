<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dealership Dashboard</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg saas-nav sticky-top py-3 shadow-sm">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white fs-4" href="#">
        <i class="bi bi-hexagon-fill text-primary"></i>
        NEXUS AUTO
      </a>

      <button class="navbar-toggler border-0 shadow-none text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <i class="bi bi-list fs-2"></i>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <!-- Global Search -->
        <div class="mx-auto my-3 my-lg-0 w-100" style="max-width: 400px;">
          <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-secondary"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control border-start-0 ps-0 shadow-none" id="globalSearch" placeholder="Search model, condition, SKU...">
          </div>
        </div>

        <ul class="navbar-nav ms-auto d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3">
          
          <!-- Global Condition Toggle Switch -->
          <li class="nav-item">
            <div class="saas-toggle w-100 text-center">
              <button class="btn btn-sm active px-3" id="toggleAll" onclick="setGlobalFilter('All')">All</button>
              <button class="btn btn-sm text-success px-3" id="toggleNew" onclick="setGlobalFilter('Brand New')"><i class="bi bi-stars"></i> New</button>
              <button class="btn btn-sm text-warning px-3" id="toggleUsed" onclick="setGlobalFilter('Secondhand')"><i class="bi bi-arrow-repeat"></i> Used</button>
            </div>
          </li>

          <li class="nav-item dropdown w-100 w-lg-auto">
            <button class="btn btn-dark border-secondary dropdown-toggle rounded-pill w-100 text-start px-3" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-geo-alt-fill text-danger me-1"></i> Main Branch
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
              <li><a class="dropdown-item" href="#">Main Branch (NY)</a></li>
              <li><a class="dropdown-item" href="#">West Coast (CA)</a></li>
            </ul>
          </li>

          <li class="nav-item d-none d-lg-block cursor-pointer position-relative dropdown">
            <a href="#" class="text-secondary text-decoration-none" data-bs-toggle="dropdown">
              <i class="bi bi-bell fs-5"></i>
              <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-sm p-3 text-center" style="min-width: 200px;">
              <li class="text-secondary fs-7">No new notifications.</li>
            </ul>
          </li>

          <li class="nav-item dropdown w-100 w-lg-auto border-top border-secondary pt-3 mt-1 pt-lg-0 mt-lg-0 border-lg-0">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
              <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['full_name']) ?>&background=0D8ABC&color=fff" alt="" width="32" height="32" class="rounded-circle me-2">
              <span class="fw-medium fs-7 d-inline-block"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-sm">
              <li><a class="dropdown-item" href="#">Profile</a></li>
              <li><a class="dropdown-item" href="#">Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Sign out</a></li>
            </ul>
          </li>

        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content Container -->
  <main class="container-fluid px-4 py-4 flex-grow-1">

    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index: 1090;"></div>

    <!-- Section 1: Dashboard Overview (KPIs) -->
    <div class="row g-4 mb-5">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="saas-card p-4 h-100">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-slate fw-semibold mb-1 fs-7 text-uppercase">Total Active Inventory</p>
              <h2 class="fw-bold mb-2" id="kpiTotalInv">0</h2>
              <div class="fs-8 text-secondary" id="kpiInvBreakdown">0 Brand New &bull; 0 Pre-Owned</div>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-lg">
              <i class="bi bi-box-seam fs-4"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="saas-card p-4 h-100">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-slate fw-semibold mb-1 fs-7 text-uppercase">Avg Margin / Depreciation</p>
              <h2 class="fw-bold mb-2 text-warning" id="kpiMargin">0%</h2>
              <div class="fs-8 text-secondary">vs. New MSRP valuation</div>
            </div>
            <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-lg">
              <i class="bi bi-graph-down-arrow fs-4"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="saas-card p-4 h-100">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-slate fw-semibold mb-1 fs-7 text-uppercase">Total Orders</p>
              <h2 class="fw-bold mb-2 text-success" id="kpiOrders">0</h2>
              <div class="fs-8 text-secondary" id="kpiRevenue">₱0 Revenue</div>
            </div>
            <div class="bg-success bg-opacity-10 text-success p-2 rounded-lg">
              <i class="bi bi-cart-check fs-4"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="saas-card p-4 h-100">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-slate fw-semibold mb-1 fs-7 text-uppercase">Total Valuation</p>
              <h2 class="fw-bold mb-2 text-info" id="kpiValuation">₱0</h2>
              <div class="fs-8 text-secondary">Combined portfolio value</div>
            </div>
            <div class="bg-info bg-opacity-10 text-info p-2 rounded-lg">
              <i class="bi bi-wallet2 fs-4"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: Layout structure -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom border-secondary pb-3" id="dashboardTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active px-4 rounded-pill fw-medium" id="inventory-tab" data-bs-toggle="pill" data-bs-target="#inventory-pane" type="button" role="tab">
          <i class="bi bi-shop me-2"></i> Inventory Showcase
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link px-4 rounded-pill fw-medium" id="orders-tab" data-bs-toggle="pill" data-bs-target="#orders-pane" type="button" role="tab">
          <i class="bi bi-receipt me-2"></i> Customer Orders
        </button>
      </li>
    </ul>

    <div class="tab-content" id="dashboardTabsContent">
      
      <!-- INVENTORY PANE -->
      <div class="tab-pane fade show active" id="inventory-pane" role="tabpanel">
        <div class="row g-4">
          
          <!-- Filtering Sidebar -->
          <div class="col-12 col-xl-3">
            <div class="sidebar-filter sticky-top" style="top: 100px;">
              <h5 class="fw-bold mb-4">Filters</h5>
              
              <div class="mb-4">
                <label class="form-label fs-7 fw-medium text-slate">Condition</label>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" value="Brand New" id="filterNew" checked onchange="applyFilters()">
                  <label class="form-check-label fs-7" for="filterNew">Brand New</label>
                </div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" value="Secondhand" id="filterUsed" checked onchange="applyFilters()">
                  <label class="form-check-label fs-7" for="filterUsed">Pre-Owned (Grade A+/A/B)</label>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label fs-7 fw-medium text-slate">Price Range</label>
                <input type="range" class="form-range" id="priceRange" min="0" max="150000" step="1000" value="150000" onchange="updatePriceLabel(); applyFilters()">
                <div class="d-flex justify-content-between fs-8 text-secondary mt-1">
                  <span>₱0</span>
                  <span id="priceLabel">Max: ₱150k</span>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label fs-7 fw-medium text-slate">Warranty / Guarantee</label>
                <select class="form-select fs-7" id="filterWarranty" onchange="applyFilters()">
                  <option value="">Any</option>
                  <option value="Factory">Factory Warranty</option>
                  <option value="Dealer">Dealer Guaranteed</option>
                  <option value="As-Is">As-Is</option>
                </select>
              </div>

              <button class="btn btn-outline-secondary w-100 rounded-3 fs-7" onclick="resetFilters()">Reset Filters</button>
            </div>
          </div>

          <!-- Inventory Showcase -->
          <div class="col-12 col-xl-9">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
              <h4 class="fw-bold m-0">Inventory Catalog</h4>
              
              <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="saas-toggle me-md-2">
                  <button class="btn btn-sm active px-3" id="btnViewGrid" onclick="setViewMode('grid')"><i class="bi bi-grid-fill"></i></button>
                  <button class="btn btn-sm px-3" id="btnViewTable" onclick="setViewMode('table')"><i class="bi bi-list-task"></i></button>
                </div>
                <button class="btn btn-primary px-3 fw-medium flex-grow-1 flex-md-grow-0" onclick="openProductModal()">
                  <i class="bi bi-plus-lg me-1"></i> Add Product
                </button>
                <button class="btn btn-outline-info px-3 fw-medium flex-grow-1 flex-md-grow-0" onclick="openCompareModal()">
                  <i class="bi bi-layout-split me-1"></i> Compare
                </button>
              </div>
            </div>

            <div id="inventoryContainer">
              <div class="row g-4" id="skeletonContainer">
                <div class="col-md-4"><div class="skeleton" style="height: 350px;"></div></div>
                <div class="col-md-4"><div class="skeleton" style="height: 350px;"></div></div>
                <div class="col-md-4"><div class="skeleton" style="height: 350px;"></div></div>
              </div>
            </div>

          </div>
        </div>
      </div>
      
      <!-- ORDERS PANE -->
      <div class="tab-pane fade" id="orders-pane" role="tabpanel">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
          <h4 class="fw-bold m-0">Customer Orders</h4>
          <button class="btn btn-success px-4 fw-medium shadow-sm" onclick="openOrderModal()">
            <i class="bi bi-cart-plus me-1"></i> New Order
          </button>
        </div>
        
        <div class="saas-card overflow-hidden">
          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
              <thead class="bg-body-secondary text-uppercase fs-8 text-secondary border-bottom border-secondary border-opacity-50">
                <tr>
                  <th class="ps-4">Order ID</th>
                  <th>Customer</th>
                  <th>Contact</th>
                  <th>Item Purchased</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody id="ordersTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

  </main>

  <!-- Product Modal (Add/Edit) -->
  <div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content saas-card text-light border-0 shadow-lg">
        <div class="modal-header border-bottom border-secondary px-4 py-3">
          <h5 class="modal-title fw-bold" id="productModalTitle">Add Product</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form id="productForm" onsubmit="saveProduct(event)" enctype="multipart/form-data">
          <div class="modal-body p-4 row g-3">
            <input type="hidden" id="prodId" name="id">
            
            <div class="col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="prodModel" name="model" placeholder="Model" required>
                <label for="prodModel" class="text-secondary">Model Name</label>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-floating">
                <select class="form-select" id="prodCondition" name="condition_type" required>
                  <option value="Brand New">Brand New</option>
                  <option value="Secondhand">Secondhand</option>
                </select>
                <label for="prodCondition" class="text-secondary">Condition Type</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="prodStorage" name="storage" placeholder="Storage" required>
                <label for="prodStorage" class="text-secondary">Storage (e.g. 256GB)</label>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="prodStock" name="stock" placeholder="Stock" required>
                <label for="prodStock" class="text-secondary">Stock Quantity</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" step="0.01" class="form-control" id="prodMsrp" name="msrp" placeholder="MSRP">
                <label for="prodMsrp" class="text-secondary">MSRP (₱)</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" step="0.01" class="form-control" id="prodPrice" name="price" placeholder="Current Price" required>
                <label for="prodPrice" class="text-secondary">Current Price (₱)</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating mb-2">
                <input type="text" class="form-control" id="prodThumb" name="thumbnail_url" placeholder="Thumbnail URL">
                <label for="prodThumb" class="text-secondary">Thumbnail Image URL</label>
              </div>
              <div>
                <label class="form-label fs-7 text-secondary mb-1">Or Upload Image</label>
                <input type="file" class="form-control bg-dark text-light border-secondary" id="prodImage" name="image" accept="image/*">
              </div>
            </div>

            <!-- Secondhand Specific -->
            <div class="col-12 mt-4"><h6 class="text-primary border-bottom border-secondary pb-2">Pre-Owned Specs (Optional)</h6></div>
            
            <div class="col-md-3">
              <div class="form-floating">
                <select class="form-select" id="prodGrade" name="condition_grade">
                  <option value="">N/A</option>
                  <option value="Grade A+">Grade A+</option>
                  <option value="Grade A">Grade A</option>
                  <option value="Grade B">Grade B</option>
                  <option value="Fair">Fair</option>
                </select>
                <label for="prodGrade" class="text-secondary">Grade</label>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="prodMileage" name="mileage" placeholder="Mileage">
                <label for="prodMileage" class="text-secondary">Mileage / Usage</label>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-floating">
                <select class="form-select" id="prodWarranty" name="warranty_status">
                  <option value="Factory">Factory</option>
                  <option value="Dealer">Dealer</option>
                  <option value="As-Is">As-Is</option>
                </select>
                <label for="prodWarranty" class="text-secondary">Warranty</label>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="prodInspect" name="inspection_status" placeholder="Inspection">
                <label for="prodInspect" class="text-secondary">Inspection Status</label>
              </div>
            </div>

          </div>
          <div class="modal-footer border-top border-secondary px-4 py-3">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-3 px-4 fw-medium">Save Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Order Modal -->
  <div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content saas-card text-light border-0 shadow-lg">
        <div class="modal-header border-bottom border-secondary px-4 py-3">
          <h5 class="modal-title fw-bold"><i class="bi bi-cart-plus text-success me-2"></i> Create New Order</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form id="orderForm" onsubmit="saveOrder(event)">
          <div class="modal-body p-4 row g-3">
            
            <div class="col-12">
              <div class="form-floating">
                <input type="text" class="form-control" id="ordCustomer" name="customer" placeholder="Customer Name" required>
                <label for="ordCustomer" class="text-secondary">Customer Name</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating">
                <input type="text" class="form-control" id="ordContact" name="contact" placeholder="Contact Number" required>
                <label for="ordContact" class="text-secondary">Contact Number</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating">
                <select class="form-select" id="ordProduct" name="product_id" required></select>
                <label for="ordProduct" class="text-secondary">Select Product</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="ordQty" name="quantity" value="1" min="1" required>
                <label for="ordQty" class="text-secondary">Quantity</label>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-floating">
                <select class="form-select" id="ordStatus" name="status">
                  <option value="Completed">Completed</option>
                  <option value="Pending">Pending</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
                <label for="ordStatus" class="text-secondary">Order Status</label>
              </div>
            </div>

          </div>
          <div class="modal-footer border-top border-secondary px-4 py-3">
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success rounded-3 px-4 fw-medium">Confirm Order</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Dual-View Comparison Modal -->
  <div class="modal fade" id="compareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content saas-card text-light border-0 shadow-lg">
        <div class="modal-header border-bottom border-secondary px-4 py-3">
          <h5 class="modal-title fw-bold"><i class="bi bi-layout-split text-info me-2"></i> Compare New vs. Pre-Owned</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="form-floating">
                <select class="form-select" id="compareNewSelect" onchange="renderCompare()"></select>
                <label for="compareNewSelect" class="text-secondary">Select Brand New</label>
              </div>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
              <div class="form-floating">
                <select class="form-select" id="compareUsedSelect" onchange="renderCompare()"></select>
                <label for="compareUsedSelect" class="text-secondary">Select Pre-Owned</label>
              </div>
            </div>
          </div>

          <div class="d-flex flex-column flex-lg-row gap-4 align-items-center align-items-lg-stretch" id="compareContainer">
            <!-- New Pane -->
            <div class="compare-pane text-center w-100" id="paneNew">
               <p class="text-secondary py-5">Select a vehicle to compare</p>
            </div>
            
            <!-- VS Divider -->
            <div class="d-flex flex-column justify-content-center align-items-center py-3 py-lg-0">
              <div class="bg-secondary bg-opacity-25 rounded-circle p-3 fw-bold">VS</div>
            </div>

            <!-- Used Pane -->
            <div class="compare-pane text-center w-100" id="paneUsed">
               <p class="text-secondary py-5">Select a vehicle to compare</p>
            </div>
          </div>
          
          <div class="mt-4 p-3 bg-success bg-opacity-10 border border-success rounded-3 text-center" id="savingsDelta" style="display:none;">
            <h4 class="text-success m-0 fw-bold" id="savingsText">You Save: $0 (0%)</h4>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/app.js"></script>
</body>
</html>