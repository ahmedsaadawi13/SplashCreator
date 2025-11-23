// FILE: /public/js/app.js

// Ajax helper function
function ajax(url, method, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                callback(null, response);
            } else {
                callback(xhr.statusText, null);
            }
        }
    };

    xhr.send(JSON.stringify(data));
}

// Show loading indicator
function showLoading(element) {
    element.innerHTML = '<span class="loading"></span> Loading...';
    element.disabled = true;
}

// Hide loading indicator
function hideLoading(element, originalText) {
    element.innerHTML = originalText;
    element.disabled = false;
}

// Show alert
function showAlert(message, type) {
    var alertClass = 'alert-' + (type || 'info');
    var alertDiv = document.createElement('div');
    alertDiv.className = 'alert ' + alertClass;
    alertDiv.textContent = message;

    var container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);

        setTimeout(function() {
            alertDiv.remove();
        }, 5000);
    }
}

// Confirm dialog
function confirm(message, callback) {
    if (window.confirm(message)) {
        callback();
    }
}

// Format date
function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});
