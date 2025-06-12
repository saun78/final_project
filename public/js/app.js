// Toggle sidebar
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('collapsed');
    document.querySelector('.main-content').classList.toggle('expanded');
});

// Handle mobile view
function handleResize() {
    if (window.innerWidth <= 768) {
        document.querySelector('.sidebar').classList.add('collapsed');
        document.querySelector('.main-content').classList.add('expanded');
    }
}

// Initial check
handleResize();

// Listen for window resize
window.addEventListener('resize', handleResize); 