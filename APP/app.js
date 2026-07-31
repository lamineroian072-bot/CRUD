let localRecords = [];

document.addEventListener("DOMContentLoaded", () => {
  fetchRecords();
  feather.replace();
});

async function fetchRecords() {
  try {
    const res = await fetch('api.php?action=read');
    localRecords = await res.json();
    renderTable(localRecords);
    updateStats();
  } catch (err) {
    console.error("Error loading records:", err);
  }
}

function renderTable(data) {
  const tbody = document.getElementById("tableBody");
  const emptyState = document.getElementById("emptyState");
  tbody.innerHTML = "";

  if (!data || data.length === 0) {
    emptyState.classList.remove("hidden");
    return;
  } else {
    emptyState.classList.add("hidden");
  }

  data.forEach((item) => {
    const tr = document.createElement("tr");
    const imgPath = (item.image && item.image !== 'default.png') ? `uploads/${item.image}` : 'https://via.placeholder.com/38?text=User';

    tr.innerHTML = `
      <td>
        <img src="${imgPath}" class="avatar-img" alt="Profile" onerror="this.onerror=null; this.src='https://via.placeholder.com/38?text=User';">
      </td>
      <td><strong>${item.record_code}</strong></td>
      <td>${escapeHtml(item.name)}</td>
      <td>${escapeHtml(item.role)}</td>
      <td><span class="badge badge-${item.status.toLowerCase()}">${item.status}</span></td>
      <td>${item.updated_at ? item.updated_at.split(' ')[0] : 'N/A'}</td>
      <td>
        <button class="btn-icon" onclick="editRecord(${item.id})" title="Edit"><i data-feather="edit-2"></i></button>
        <button class="btn-icon" onclick="deleteRecord(${item.id})" title="Delete"><i data-feather="trash-2"></i></button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  feather.replace();
}

function updateStats() {
  document.getElementById("statTotal").innerText = localRecords.length;
  document.getElementById("statActive").innerText = localRecords.filter(r => r.status === "Active").length;
}

function handleSearch() {
  const query = document.getElementById("searchInput").value.toLowerCase();
  const filtered = localRecords.filter(
    r => r.name.toLowerCase().includes(query) ||
         r.role.toLowerCase().includes(query) ||
         r.record_code.toLowerCase().includes(query)
  );
  renderTable(filtered);
}

function openModal(isEdit = false) {
  document.getElementById("modalTitle").innerText = isEdit ? "Edit Record" : "Create Record";
  document.getElementById("modalOverlay").classList.remove("hidden");
}

function closeModal() {
  document.getElementById("modalOverlay").classList.add("hidden");
  document.getElementById("recordForm").reset();
  document.getElementById("recordId").value = "";
}

async function handleFormSubmit(e) {
  e.preventDefault();
  const form = document.getElementById("recordForm");
  const formData = new FormData(form);
  const id = document.getElementById("recordId").value;
  const action = id ? 'update' : 'create';

  try {
    const res = await fetch(`api.php?action=${action}`, {
      method: 'POST',
      body: formData
    });

    const result = await res.json();
    if (result.success) {
      closeModal();
      fetchRecords();
    } else {
      alert("Error: " + result.message);
    }
  } catch (err) {
    console.error("Submit error:", err);
  }
}

function editRecord(id) {
  const record = localRecords.find(r => r.id == id);
  if (!record) return;

  document.getElementById("recordId").value = record.id;
  document.getElementById("inputName").value = record.name;
  document.getElementById("inputRole").value = record.role;
  document.getElementById("inputStatus").value = record.status;

  openModal(true);
}

async function deleteRecord(id) {
  if (confirm(`Are you sure you want to remove record ID ${id}?`)) {
    try {
      const res = await fetch(`api.php?action=delete&id=${id}`);
      const result = await res.json();
      if (result.success) {
        fetchRecords();
      }
    } catch (err) {
      console.error("Delete error:", err);
    }
  }
}

function escapeHtml(str) {
  return str.replace(/[&<>"']/g, (m) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  })[m]);
}