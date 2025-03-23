// Custom JavaScript

// Tự động ẩn thông báo sau 5 giây
document.addEventListener('DOMContentLoaded', function() {
    // Tìm tất cả các alert
    const alerts = document.querySelectorAll('.alert');
    
    // Thiết lập timeout để ẩn alert sau 5 giây
    alerts.forEach(function(alert) {
        setTimeout(function() {
            // Tạo hiệu ứng fade out
            alert.style.transition = 'opacity 1s';
            alert.style.opacity = '0';
            
            // Xóa alert khỏi DOM sau khi fade out hoàn tất
            setTimeout(function() {
                alert.remove();
            }, 1000);
        }, 5000);
    });
});