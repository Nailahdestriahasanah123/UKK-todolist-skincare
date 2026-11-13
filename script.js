/**
 * ==============================================================
 * FILE: assets/js/script.js
 * FUNGSI: Logika interaksi antarmuka (DOM Manipulation & AJAX)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. Event Listener: Menangkap interaksi user seperti 'click', 'input', 'submit'.
 * 2. Fetch API (AJAX Asynchronous): Mengirim dan menerima data ke backend PHP
 *    di balik layar tanpa perlu me-refresh (reload) seluruh halaman web.
 * 3. DOM Manipulation: Mengubah tampilan HTML secara langsung (class, text, style)
 *    setelah backend selesai memproses request.
 */

document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // 1. AUTO CLOSE ALERT NOTIFIKASI
    // -------------------------------------------------------------
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        // Otomatis hilangkan alert setelah 4.5 detik
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alertBox.style.opacity = '0';
            alertBox.style.transform = 'translateY(-10px)';
            setTimeout(() => alertBox.remove(), 400);
        }, 4500);

        // Tombol silang untuk menutup manual
        const closeBtn = alertBox.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => alertBox.remove());
        }
    }

    // -------------------------------------------------------------
    // 2. CHECKLIST INTERAKTIF (TOGGLE STATUS DENGAN FETCH API)
    // -------------------------------------------------------------
    const taskCheckboxes = document.querySelectorAll('.custom-checkbox');

    taskCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', async function() {
            const taskId = this.dataset.id;
            const taskItem = document.getElementById(`task-${taskId}`);
            const isChecked = this.checked;

            try {
                // Kirim request ke backend menggunakan Fetch API (metode POST)
                const formData = new FormData();
                formData.append('id', taskId);

                const response = await fetch('actions/toggle_status.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Update tampilan baris tugas
                    if (result.is_completed === 1) {
                        taskItem.classList.add('completed');
                    } else {
                        taskItem.classList.remove('completed');
                    }

                    // Update hitungan statistik & progress bar secara live
                    updateLiveProgress();
                } else {
                    alert('Gagal memperbarui status: ' + (result.message || 'Error'));
                    this.checked = !isChecked; // Kembalikan ke posisi semula jika gagal
                }
            } catch (err) {
                console.error('Fetch error:', err);
                // Fallback: Jika Fetch bermasalah, submit via URL biasa
                window.location.href = `actions/toggle_status.php?id=${taskId}`;
            }
        });
    });

    // -------------------------------------------------------------
    // 3. FUNGSI UPDATE PROGRESS BAR LIVE DI CLIENT
    // -------------------------------------------------------------
    function updateLiveProgress() {
        const allItems = document.querySelectorAll('.task-item');
        const completedItems = document.querySelectorAll('.task-item.completed');
        
        const total = allItems.length;
        const completed = completedItems.length;
        const percent = total > 0 ? Math.round((completed / total) * 100) : 0;

        // Update persentase teks
        const percentText = document.querySelector('.progress-percent');
        if (percentText) percentText.textContent = `${percent}%`;

        // Update lebar progress bar fill
        const barFill = document.querySelector('.progress-bar-fill');
        if (barFill) barFill.style.width = `${percent}%`;

        // Update teks statistik (misal: "2 dari 7 Selesai")
        const statsText = document.querySelector('.progress-stats span:first-child');
        if (statsText) statsText.textContent = `${completed} dari ${total} Selesai ✨`;
    }

    // -------------------------------------------------------------
    // 4. LIVE SEARCH FILTER (PENCARIAN INSTAN SISI CLIENT)
    // -------------------------------------------------------------
    const searchInput = document.getElementById('searchTaskInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const taskItems = document.querySelectorAll('.task-item');
            let visibleCount = 0;

            taskItems.forEach(item => {
                const name = item.dataset.name ? item.dataset.name.toLowerCase() : '';
                const notes = item.dataset.notes ? item.dataset.notes.toLowerCase() : '';
                const category = item.dataset.category ? item.dataset.category.toLowerCase() : '';

                if (name.includes(query) || notes.includes(query) || category.includes(query)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Tampilkan empty state jika hasil pencarian nihil
            const emptySearch = document.getElementById('emptySearchState');
            if (emptySearch) {
                emptySearch.style.display = (visibleCount === 0 && query !== '') ? 'block' : 'none';
            }
        });
    }

    // -------------------------------------------------------------
    // 5. MODAL EDIT RUTINITAS
    // -------------------------------------------------------------
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const editBtns = document.querySelectorAll('.edit-btn');
    const closeEditModalBtn = document.getElementById('closeEditModal');
    const cancelEditBtn = document.getElementById('cancelEditBtn');

    if (editModal) {
        editBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                // Ambil data dari attribute elemen
                const id = btn.dataset.id;
                const name = btn.dataset.name;
                const categoryId = btn.dataset.category;
                const time = btn.dataset.time;
                const notes = btn.dataset.notes;
                const priority = btn.dataset.priority;

                // Masukkan nilai ke dalam form modal
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_task_name').value = name;
                document.getElementById('edit_category_id').value = categoryId;
                document.getElementById('edit_target_time').value = time;
                document.getElementById('edit_notes').value = notes;
                document.getElementById('edit_priority').value = priority;

                // Tampilkan modal
                editModal.classList.add('active');
            });
        });

        // Tutup modal edit
        const closeModal = () => editModal.classList.remove('active');
        if (closeEditModalBtn) closeEditModalBtn.addEventListener('click', closeModal);
        if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeModal);

        // Tutup jika klik area latar belakang hitam (overlay)
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) closeModal();
        });
    }

    // -------------------------------------------------------------
    // 6. MODAL KONFIRMASI HAPUS (DELETE MODAL CUTE)
    // -------------------------------------------------------------
    const deleteModal = document.getElementById('deleteModal');
    const deleteBtns = document.querySelectorAll('.delete-btn');
    const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteLink = document.getElementById('confirmDeleteLink');
    const deleteItemNameSpan = document.getElementById('deleteItemName');

    if (deleteModal) {
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const id = btn.dataset.id;
                const name = btn.dataset.name;

                // Set teks nama yang akan dihapus & target URL
                if (deleteItemNameSpan) deleteItemNameSpan.textContent = `"${name}"`;
                if (confirmDeleteLink) confirmDeleteLink.href = `actions/hapus_task.php?id=${id}`;

                // Tampilkan modal konfirmasi
                deleteModal.classList.add('active');
            });
        });

        const closeDelModal = () => deleteModal.classList.remove('active');
        if (closeDeleteModalBtn) closeDeleteModalBtn.addEventListener('click', closeDelModal);
        if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', closeDelModal);

        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) closeDelModal();
        });
    }

    // -------------------------------------------------------------
    // 7. DRAG AND DROP & ARROW REORDERING (HTML5 & ARROW BUTTONS)
    // -------------------------------------------------------------
    const taskContainer = document.getElementById('taskListContainer');
    const dragToast = document.getElementById('dragToast');
    const dragToastMsg = document.getElementById('dragToastMsg');
    let toastTimeout = null;

    function showDragToast(msg) {
        if (!dragToast) return;
        if (dragToastMsg) dragToastMsg.textContent = msg;
        dragToast.classList.add('show');
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            dragToast.classList.remove('show');
        }, 2800);
    }

    // Fungsi terpusat untuk menyimpan urutan baru ke database via AJAX
    async function saveTaskOrder() {
        if (!taskContainer) return;
        const allItems = taskContainer.querySelectorAll('.task-item');
        const newOrderIds = [];
        allItems.forEach(item => {
            if (item.dataset.id) {
                newOrderIds.push(item.dataset.id);
            }
        });

        if (newOrderIds.length > 0) {
            try {
                const response = await fetch('actions/reorder_tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ task_ids: newOrderIds })
                });
                const resData = await response.json();
                if (resData.success) {
                    showDragToast(resData.message || 'Urutan langkah skincare tersimpan! ✨');
                }
            } catch (err) {
                console.error('Gagal menyimpan urutan:', err);
            }
        }
    }

    if (taskContainer) {
        // --- A. DUKUNGAN TOMBOL PANAH CEPAT (▲ Geser Naik / ▼ Geser Turun) ---
        taskContainer.addEventListener('click', (e) => {
            const arrowUp = e.target.closest('.btn-arrow-up');
            const arrowDown = e.target.closest('.btn-arrow-down');

            if (arrowUp) {
                e.preventDefault();
                const currentItem = arrowUp.closest('.task-item');
                const prevItem = currentItem.previousElementSibling;
                if (prevItem && prevItem.classList.contains('task-item')) {
                    taskContainer.insertBefore(currentItem, prevItem);
                    currentItem.style.transform = 'scale(1.02)';
                    setTimeout(() => currentItem.style.transform = '', 200);
                    saveTaskOrder();
                }
            } else if (arrowDown) {
                e.preventDefault();
                const currentItem = arrowDown.closest('.task-item');
                const nextItem = currentItem.nextElementSibling;
                if (nextItem && nextItem.classList.contains('task-item')) {
                    taskContainer.insertBefore(nextItem, currentItem);
                    currentItem.style.transform = 'scale(1.02)';
                    setTimeout(() => currentItem.style.transform = '', 200);
                    saveTaskOrder();
                }
            }
        });

        // --- B. DUKUNGAN HTML5 NATIVE DRAG AND DROP ---
        const draggables = taskContainer.querySelectorAll('.task-item[draggable="true"]');

        draggables.forEach(draggable => {
            draggable.addEventListener('dragstart', (e) => {
                // Cegah drag jika user mengklik input checkbox atau tombol aksi
                if (e.target.closest('input, button, a')) {
                    e.preventDefault();
                    return;
                }

                draggable.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                try {
                    e.dataTransfer.setData('text/plain', draggable.dataset.id);
                } catch (err) {}
            });

            draggable.addEventListener('dragend', () => {
                draggable.classList.remove('dragging');
                saveTaskOrder();
            });
        });

        // Event saat elemen ditarik di atas container (dragover)
        taskContainer.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const draggingItem = taskContainer.querySelector('.dragging');
            if (!draggingItem) return;

            const afterElement = getDragAfterElement(taskContainer, e.clientY);
            if (afterElement == null) {
                taskContainer.appendChild(draggingItem);
            } else {
                taskContainer.insertBefore(draggingItem, afterElement);
            }
        });

        // Fungsi Helper: Mendeteksi posisi elemen terdekat saat mouse bergerak
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.task-item:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
    }
});
