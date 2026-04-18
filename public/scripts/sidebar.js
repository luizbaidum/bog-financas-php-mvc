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

// Ensure only one accordion section is open at a time
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sidebar .accordion-header').forEach(function(header) {
        header.addEventListener('click', function(event) {
            let targetSelector = header.getAttribute('data-bs-target');
            if (!targetSelector) return;
            let target = document.querySelector(targetSelector);

            // Close any other open accordion-content within the sidebar
            document.querySelectorAll('.sidebar .accordion-content.collapse.show').forEach(function(openEl) {
                if (openEl == target) return;
                let inst = bootstrap.Collapse.getInstance(openEl);
                if (inst) {
                    inst.hide();
                } else {
                    new bootstrap.Collapse(openEl, { toggle: false }).hide();
                }
            });
        });
    });
});