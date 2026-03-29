// ============================================================
// assets/js/app.js — Shared JavaScript for Nexus
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ----------------------------------------------------------
    // 1. Live Search Filter for Paper Tables
    //    Looks for <input id="search-input"> and table tbody rows
    // ----------------------------------------------------------
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    const statusFilter = document.getElementById('status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }

    function filterTable() {
        const query  = searchInput  ? searchInput.value.toLowerCase().trim()  : '';
        const status = statusFilter ? statusFilter.value.toLowerCase().trim() : '';
        const rows   = document.querySelectorAll('tbody tr[data-title]');

        rows.forEach(function (row) {
            const title      = (row.getAttribute('data-title')  || '').toLowerCase();
            const rowStatus  = (row.getAttribute('data-status') || '').toLowerCase();

            const matchTitle  = title.includes(query);
            const matchStatus = status === '' || rowStatus === status;

            row.style.display = (matchTitle && matchStatus) ? '' : 'none';
        });

        // Show "no results" row if all hidden
        const visibleRows = document.querySelectorAll('tbody tr[data-title]:not([style*="none"])');
        const noResultRow = document.getElementById('no-results-row');
        if (noResultRow) {
            noResultRow.style.display = visibleRows.length === 0 ? '' : 'none';
        }
    }

    // ----------------------------------------------------------
    // 2. Confirm Before Delete (for any form with data-confirm)
    // ----------------------------------------------------------
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ----------------------------------------------------------
    // 3. File Upload Preview (shows filename after selection)
    // ----------------------------------------------------------
    const fileInput  = document.getElementById('paper-file');
    const fileLabel  = document.getElementById('file-label');
    const fileStatus = document.getElementById('file-status');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                const file = this.files[0];
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

                if (fileLabel)  fileLabel.textContent  = file.name;
                if (fileStatus) {
                    fileStatus.innerHTML =
                        '<span class="material-symbols-outlined text-green-600 text-sm">check_circle</span> ' +
                        file.name + ' (' + sizeMB + ' MB)';
                    fileStatus.className = 'flex items-center gap-2 text-sm text-green-700 font-medium mt-3';
                }
            }
        });
    }

    // ----------------------------------------------------------
    // 4. Drag & Drop for Upload Zone
    // ----------------------------------------------------------
    const uploadZone = document.getElementById('upload-zone');
    if (uploadZone && fileInput) {
        ['dragenter', 'dragover'].forEach(function (evt) {
            uploadZone.addEventListener(evt, function (e) {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            uploadZone.addEventListener(evt, function (e) {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
            });
        });
        uploadZone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    }

    // ----------------------------------------------------------
    // 5. Auto-dismiss alert messages after 4 seconds
    // ----------------------------------------------------------
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity    = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 4000);
    });
});
