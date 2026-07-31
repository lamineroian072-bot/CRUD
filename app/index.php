<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Core Records System</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>

  <div class="app-container">
    
    <!-- Printable Document Header (Only visible on paper / PDF printout) -->
    <div class="print-only-header">
      <div class="print-brand">
        <img src="assets/logo.png" alt="Logo" class="print-logo" onerror="this.style.display='none';">
        <div>
          <h2>System Audit & Summary Report</h2>
          <p id="printTimestamp">Generated on: </p>
        </div>
      </div>
    </div>

    <!-- Main Application Header -->
    <header class="app-header">
      <div class="brand">
        <div class="logo-wrapper">
          <img src="assets/logo.png" alt="Logo" class="brand-logo" onerror="this.onerror=null; this.src='https://via.placeholder.com/48?text=LOGO';">
        </div>
        <div>
          <h1>Core Records</h1>
          <p class="subtitle">System Overview & Management</p>
        </div>
      </div>
      <div class="header-actions">
        <!-- Direct Instant Print Function -->
        <button type="button" class="btn btn-secondary" onclick="triggerDirectPrint()">
          <i data-feather="printer"></i> Print Report
        </button>
        <button type="button" class="btn btn-primary" onclick="openModal()">
          <i data-feather="plus"></i> New Record
        </button>
      </div>
    </header>

    <!-- Controls Bar -->
    <section class="controls-bar">
      <div class="search-box">
        <i data-feather="search"></i>
        <input type="text" id="searchInput" placeholder="Search by name, role, or code..." oninput="handleSearch()">
      </div>
      <div class="stats-pills">
        <span class="pill">Total: <strong id="statTotal">0</strong></span>
        <span class="pill pill-active">Active: <strong id="statActive">0</strong></span>
      </div>
    </section>

    <!-- Data Table -->
    <main class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Profile</th>
            <th>Code</th>
            <th>Name</th>
            <th>Role</th>
            <th>Status</th>
            <th>Updated</th>
            <th class="action-col">Actions</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <!-- Populated by app.js -->
        </tbody>
      </table>
      
      <div id="emptyState" class="empty-state hidden">
        <i data-feather="inbox"></i>
        <p>No records found in database.</p>
      </div>
    </main>
  </div>

  <!-- Create/Edit Modal -->
  <div class="modal-overlay hidden" id="modalOverlay">
    <div class="modal-card">
      <div class="modal-header">
        <h3 id="modalTitle">Create Record</h3>
        <button type="button" class="btn-close" onclick="closeModal()"><i data-feather="x"></i></button>
      </div>
      <form id="recordForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
        <input type="hidden" id="recordId" name="id">
        
        <div class="form-group">
          <label for="inputImage">Profile Picture</label>
          <input type="file" id="inputImage" name="image" accept="image/*">
        </div>

        <div class="form-group">
          <label for="inputName">Full Name</label>
          <input type="text" id="inputName" name="name" required placeholder="e.g. Alex Rivera">
        </div>

        <div class="form-group">
          <label for="inputRole">Role / Department</label>
          <input type="text" id="inputRole" name="role" required placeholder="e.g. Data Analyst">
        </div>

        <div class="form-group">
          <label for="inputStatus">Status</label>
          <select id="inputStatus" name="status">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Pending">Pending</option>
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Record</button>
        </div>
      </form>
    </div>
  </div>

  <script src="app.js"></script>
  <script>
    function triggerDirectPrint() {
      const ts = document.getElementById("printTimestamp");
      if (ts) ts.innerText = "Generated on: " + new Date().toLocaleString();
      window.print();
    }
  </script>
</body>
</html>