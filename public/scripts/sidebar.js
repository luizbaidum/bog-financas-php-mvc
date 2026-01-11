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

// Simple hover effect for cash flow items using Bootstrap classes
document.querySelectorAll('.cashflow-item').forEach(item => {
    item.addEventListener('mouseenter', function() {
        this.classList.add('bg-light');
    });
    item.addEventListener('mouseleave', function() {
        this.classList.remove('bg-light');
    });
});

// Add click effect using Bootstrap classes
document.querySelectorAll('.cashflow-item').forEach(item => {
    item.addEventListener('click', function() {
        // Remove active class from all items
        document.querySelectorAll('.cashflow-item').forEach(i => {
            i.classList.remove('active');
        });

        // Add active class to clicked item
        this.classList.add('active');

        // Remove active class after a delay
        setTimeout(() => {
            this.classList.remove('active');
        }, 300);
    });
});

document.getElementById('sidebarClose')?.addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('show');
    document.getElementById('sidebarBackdrop').classList.remove('show');
});