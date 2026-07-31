<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Core Records System - APP</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>

  <div class="app-container">
    
    <!-- Printable Header (Triggers only when printing / saving to PDF) -->
    <div class="print-only-header">
      <div class="print-brand">
        <img src="assets/logo.png?v=<?php echo time(); ?>" 
             alt="Logo" 
             class="print-logo" 
             onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23000\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z\'></path></svg>';">
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
          <img src="data:image/jpeg;base64,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" 
               alt="Logo" 
               class="brand-logo" 
               onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'32\' height=\'32\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%236366f1\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z\'></path></svg>';">
        </div>
        <div>
          <h1>Core Records</h1>
          <p class="subtitle">System Overview & Management</p>
        </div>
      </div>
      <div class="header-actions">
        <button type="button" class="btn btn-secondary" onclick="triggerDirectPrint()">
          <i data-feather="printer"></i> Print Report
        </button>
        <button type="button" class="btn btn-primary" onclick="openModal()">
          <i data-feather="plus"></i> New Record
        </button>
      </div>
    </header>

    <!-- Controls & Search -->
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
          <!-- Dynamically populated via JavaScript -->
        </tbody>
      </table>
      
      <div id="emptyState" class="empty-state hidden">
        <i data-feather="inbox"></i>
        <p>No records found in database.</p>
      </div>
    </main>
  </div>

  <!-- Create / Edit Modal -->
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
    feather.replace();

    function triggerDirectPrint() {
      const ts = document.getElementById("printTimestamp");
      if (ts) ts.innerText = "Generated on: " + new Date().toLocaleString();
      window.print();
    }
  </script>
</body>
</html>