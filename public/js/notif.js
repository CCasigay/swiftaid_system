function fetchNotifications() {
    console.log(document.body.innerHTML); // Log the full DOM (for debugging)

    fetch("../includes/fetchNotifications.php")
        .then((response) => response.json())
        .then((data) => {
            const notificationLink = document.querySelector("a[href='../public/mains/notification.php']");
            console.log(notificationLink); // Check if the link is found

            if (notificationLink) {
                // Check if there are unread notifications
                if (data.unread_count > 0) {
                    let badge = notificationLink.querySelector(".notification-badge");

                    // If no badge, create and append one
                    if (!badge) {
                        badge = document.createElement("span");
                        badge.className = "notification-badge";
                        notificationLink.appendChild(badge);
                    }

                    badge.textContent = data.unread_count; // Set the unread count as badge text
                } else {
                    // If no unread notifications, remove the badge if it exists
                    const badge = notificationLink.querySelector(".notification-badge");
                    if (badge) {
                        badge.remove();
                    }
                }
            } else {
                console.warn("Notification link not found.");
            }

            // Call a function to update notification styles (make sure it's defined)
            updateNotificationStyles();
        })
        .catch((error) => {
            console.error("Error fetching notifications:", error);
        });
}


// Update the styles for notifications based on their read/unread status
function updateNotificationStyles() {
    document.querySelectorAll('.notification').forEach(notification => {
        if (notification.classList.contains('unread')) {
            notification.style.fontWeight = 'bold'; // Keep bold for unread
        } else {
            notification.style.fontWeight = 'normal'; // Normal font for read
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const markAsReadBtn = document.getElementById('markAsReadBtn');
    if (markAsReadBtn) {
        markAsReadBtn.addEventListener('click', function () {
            fetch('../includes/markNotificationsRead.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user_id: "<?php echo $user_id; ?>" })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification').forEach(notification => {
                        notification.classList.remove('unread');
                        notification.classList.add('read');
                        notification.style.fontWeight = 'normal';
                    });
                    alert('All notifications marked as read.');
                } else {
                    alert('Error marking notifications as read.');
                }
            })
            .catch(error => console.error("Error:", error));
        });
    } else {
        console.warn('Mark All as Read button not found.');
    }
});


// Optional: Handle individual notification clicks
document.querySelectorAll('.notification.unread').forEach(notification => {
    notification.addEventListener('click', () => {
        const notificationId = notification.dataset.id; // Add data-id attribute in HTML

        fetch(`../includes/markSingleNotificationRead.php?id=${notificationId}`, {
            method: 'POST',
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notification.classList.remove('unread');
                    notification.classList.add('read');
                    notification.style.fontWeight = 'normal'; // Change to normal font
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
    });
});

// Set an interval to refresh notifications every 30 seconds
setInterval(fetchNotifications, 30000);

// Initial fetch to load notifications and apply correct styles
fetchNotifications();