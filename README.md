# Core Records System 🚀

A lightweight, responsive PHP & MySQL CRUD management dashboard with custom profile picture uploads, real-time client-side search, stat metrics, and instant PDF/Print reporting capabilities.

---

## 🌟 Key Features

* **Full CRUD Operations**: Create, read, update, and delete system records smoothly using asynchronous background fetches (`api.php`).
* **Profile Image Uploads**: Upload custom profile avatars with automatic fallback support for default placeholders.
* **Instant Print & PDF Export**: Integrated `window.print()` functionality with CSS print media overrides that strip UI controls for clean physical or PDF reports.
* **Live Dynamic Search**: Filter records instantly by name, role, or unique record code.
* **Modern Dark UI**: Designed with CSS custom properties (`var()`), responsive flex layouts, and [Feather Icons](https://feathericons.com/).

---

## 📁 Project Structure

```text
crud_system/
│
├── assets/
│   └── logo.png          # Main header brand logo
├── uploads/              # Directory for uploaded profile pictures
│   └── default.png       # Default placeholder avatar
├── api.php               # RESTful PHP backend handling database actions
├── config.php            # MySQL PDO database connection file
├── index.php             # Main dashboard UI
├── style.css             # System styling & @media print overrides
├── app.js                # Frontend logic (Fetch API, DOM rendering, Modals)
└── README.md             # Project documentation
