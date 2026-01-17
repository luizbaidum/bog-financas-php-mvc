// Sidebar toggle for mobile
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('show');
    document.getElementById('sidebarBackdrop').classList.toggle('show');
});

// Close sidebar when clicking on backdrop
document.getElementById('sidebarBackdrop').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('show');
    this.classList.remove('show');
});

// Set current date
const now = new Date();
const options = { day: 'numeric', month: 'long', year: 'numeric' };
const formattedDate = now.toLocaleDateString('pt-BR', options);

document.getElementById('sidebarClose')?.addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('show');
    document.getElementById('sidebarBackdrop').classList.remove('show');
});

document.addEventListener('DOMContentLoaded', function() {
    atualizarMenuAberto();
});