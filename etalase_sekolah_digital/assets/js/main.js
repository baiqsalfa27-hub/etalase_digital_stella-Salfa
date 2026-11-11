/* ==========================================================
   DASHBOARD GLOBAL JAVASCRIPT (Aman & Bebas Error)
   ========================================================== */

// Fungsi Buka Pop-up Detail Karya
function showDetailModal(imgSrc, title, author, date, desc, githubUrl, demoUrl) {
    const imgContainer = document.getElementById('modalImgContainer');
    const imgElement = document.getElementById('modalImg');
    
    if (imgContainer && imgElement) {
        if (imgSrc && imgSrc !== '') {
            imgElement.src = imgSrc;
            imgContainer.style.display = 'flex';
        } else {
            imgContainer.style.display = 'none';
        }
    }

    const setVal = (id, val) => {
        let el = document.getElementById(id);
        if (el) el.innerText = val;
    };

    setVal('modalTitle', title);
    setVal('modalInfo', 'Oleh: ' + author + ' • ' + date);
    setVal('modalDesc', desc);

    let ghBtn = document.getElementById('modalGithubBtn');
    let dmBtn = document.getElementById('modalDemoBtn');
    
    if (ghBtn) ghBtn.setAttribute('href', githubUrl);
    if (dmBtn) dmBtn.setAttribute('href', demoUrl);

    let detailModal = document.getElementById('detailModal');
    if (detailModal) detailModal.style.display = 'flex';
}

function closeDetailModal() {
    let detailModal = document.getElementById('detailModal');
    if (detailModal) detailModal.style.display = 'none';
}

// Fungsi Pop-up Konfirmasi Hapus
function showDeleteModal(url) {
    let confirmBtn = document.getElementById('confirmDeleteBtn');
    let deleteModal = document.getElementById('deleteModal');
    
    if (confirmBtn) confirmBtn.setAttribute('href', url);
    if (deleteModal) deleteModal.style.display = 'flex';
}

function closeDeleteModal() {
    let deleteModal = document.getElementById('deleteModal');
    if (deleteModal) deleteModal.style.display = 'none';
}

// Tutup modal otomatis jika user klik di luar area kotak modal
window.onclick = function(event) {
    let detailModal = document.getElementById('detailModal');
    let deleteModal = document.getElementById('deleteModal');
    if (event.target === detailModal) {
        detailModal.style.display = 'none';
    }
    if (event.target === deleteModal) {
        deleteModal.style.display = 'none';
    }
}