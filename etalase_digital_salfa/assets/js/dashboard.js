// Fungsi untuk menampilkan modal detail karya
function showDetailModal(img, title, author, date, desc, github, demo) {
    const modal = document.getElementById('detailModal');
    if (!modal) return;

    // Set gambar
    const imgContainer = document.getElementById('modalImgContainer');
    const imgElement = document.getElementById('modalImg');
    if (img && img !== '') {
        imgElement.src = img;
        imgContainer.style.display = 'block';
    } else {
        imgContainer.style.display = 'none';
    }

    // Set teks detail
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalInfo').innerHTML = 'Oleh: <b>' + author + '</b> &bull; ' + date;
    document.getElementById('modalDesc').innerText = desc;

    // Set link GitHub & Demo
    const ghBtn = document.getElementById('modalGithubBtn');
    const demoBtn = document.getElementById('modalDemoBtn');

    if (github && github !== '#' && github !== '') {
        ghBtn.href = github;
        ghBtn.style.display = 'block';
    } else {
        ghBtn.style.display = 'none';
    }

    if (demo && demo !== '#' && demo !== '') {
        demoBtn.href = demo;
        demoBtn.style.display = 'block';
    } else {
        demoBtn.style.display = 'none';
    }

    modal.style.display = 'flex';
}

// Fungsi untuk menutup modal detail
function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    if (modal) modal.style.display = 'none';
}

// Fungsi untuk modal hapus
function showDeleteModal(deleteUrl) {
    const deleteModal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) confirmBtn.href = deleteUrl;
    if (deleteModal) deleteModal.style.display = 'flex';
}

function closeDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) deleteModal.style.display = 'none';
}

// Tutup modal jika klik di luar box
window.onclick = function(event) {
    const detailModal = document.getElementById('detailModal');
    const deleteModal = document.getElementById('deleteModal');
    if (event.target === detailModal) detailModal.style.display = 'none';
    if (event.target === deleteModal) deleteModal.style.display = 'none';
};