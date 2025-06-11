// Theme switcher
document.addEventListener('DOMContentLoaded', function() {
    // Apply saved theme
    const savedTheme = getCookie('theme') || 'default';
    document.body.className = savedTheme;
    
    // Theme selector change event
    const themeSelect = document.getElementById('theme');
    if (themeSelect) {
        themeSelect.value = savedTheme;
        themeSelect.addEventListener('change', function() {
            document.body.className = this.value;
        });
    }
    
    // Add click effects to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(1px)';
        });
        
        button.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(0)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

// Helper function to get cookie
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}