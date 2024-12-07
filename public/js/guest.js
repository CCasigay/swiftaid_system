// Variables for User Location and Severity
let userLatitude, userLongitude, emergencySeverity;
let startTime = localStorage.getItem('startTime'); // Get the start time from localStorage
let lastLocationRequestTime = localStorage.getItem('lastLocationRequestTime'); // Store the last location request time

// Check if 30 minutes have passed
function checkTimeLimit() {
    const timeLimit = 30 * 60 * 1000; // 30 minutes in milliseconds
    const coolDownPeriod = 60 * 60 * 1000; // 1 hour in milliseconds
    const currentTime = new Date().getTime();
    
    const startTime = localStorage.getItem('startTime'); // Get start time from localStorage
    const redirectedTime = localStorage.getItem('redirectedTime'); // Get last redirected time
    
    if (redirectedTime) {
        const timeSinceRedirect = currentTime - parseInt(redirectedTime, 10);

        // If within the cooldown period, block access to the guest page
        if (timeSinceRedirect < coolDownPeriod) {
            const remainingTime = Math.ceil((coolDownPeriod - timeSinceRedirect) / (60 * 1000)); // Minutes left
            alert(`You can access this page again in ${remainingTime} minutes.`);
            window.location.href = "../mains/signup.php"; // Redirect to update_user.php
            return;
        } else {
            // Clear the cooldown period after 1 hour
            localStorage.removeItem('redirectedTime');
        }
    }

    if (startTime) {
        const elapsedTime = currentTime - parseInt(startTime, 10);

        // If 30 minutes have passed, redirect to `update_user.php`
        if (elapsedTime >= timeLimit) {
            localStorage.setItem('redirectedTime', currentTime); // Store the redirect time
            localStorage.removeItem('startTime'); // Clear the start time
            alert("Session expired! Redirecting...");
            window.location.href = "../mains/signup.php"; // Redirect to update_user.php
        }
    } else {
        // If no start time exists, set it as a new session
        localStorage.setItem('startTime', currentTime);
    }
}

// Call the function to check the time limit only once when the page loads
window.onload = function() {
    checkTimeLimit();
};

// Function: Show Notification Bar
function showNotification(message, success = true) {
    const notificationBar = document.getElementById("notificationBar");

    if (!notificationBar) {
        console.error("Notification bar element not found!");
        return;
    }

    notificationBar.style.backgroundColor = success ? "#28a745" : "#dc3545";
    notificationBar.style.color = "white";
    notificationBar.textContent = message;
    notificationBar.style.display = "block";
    notificationBar.style.position = "fixed";
    notificationBar.style.top = "0";
    notificationBar.style.width = "100%";
    notificationBar.style.zIndex = "1050";

    setTimeout(() => {
        notificationBar.style.display = "none";
    }, 3000);
}

// Function: Get User Location with rate limiting
function getLocation() {
    const currentTime = new Date().getTime();
    const minTimeBetweenRequests = 10 * 1000; // Minimum time of 10 seconds between requests

    // Check if the location API is being called too soon
    if (lastLocationRequestTime && (currentTime - lastLocationRequestTime < minTimeBetweenRequests)) {
        showNotification("Location request is too frequent. Please wait a moment.", false);
        return; // Prevent multiple requests in a short time
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userLatitude = position.coords.latitude;
                userLongitude = position.coords.longitude;
                const severityModal = new bootstrap.Modal(document.getElementById("severityModal"));
                severityModal.show();

                // Update the last location request time
                localStorage.setItem('lastLocationRequestTime', currentTime);
            },
            (error) => showNotification("Error getting location: " + error.message, false)
        );
    } else {
        showNotification("Geolocation is not supported by this browser.", false);
    }
}

// Function: Submit Emergency Severity
function submitSeverity(severity) {
    emergencySeverity = severity;
    const severityModal = bootstrap.Modal.getInstance(document.getElementById("severityModal"));
    severityModal.hide();

    const phoneModal = new bootstrap.Modal(document.getElementById("phoneModal"));
    phoneModal.show();
}

// Handle Phone Form Submission
document.querySelector("#phoneForm").addEventListener("submit", (event) => {
    event.preventDefault();
    const phoneInput = document.querySelector("#phoneNumber");
    const phoneValue = phoneInput.value.trim();
    const phonePattern = /^\d{10,15}$/;

    // Validate phone number
    if (!phonePattern.test(phoneValue)) {
        phoneInput.classList.add("is-invalid");
        showNotification("Invalid phone number. Please enter 10-15 digits.", false);
        return;
    }

    phoneInput.classList.remove("is-invalid");

    const requestData = {
        phone: phoneValue,
        latitude: userLatitude,
        longitude: userLongitude,
        severity: emergencySeverity,
    };

    fetch("../includes/send_help.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(requestData),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const confirmationModal = new bootstrap.Modal(document.getElementById("confirmationModal"));
            confirmationModal.show();
            showNotification("Your emergency request has been sent successfully.", true);
            
            // After successful submission, redirect the user to the guest home page
            setTimeout(() => {
                window.location.href = "../mains/guest_home.php";
            }, 3000);
        } else {
            showNotification("Error sending your emergency request.", false);
        }
    })
    .catch(() => {
        showNotification("Network error. Please try again later.", false);
    });
});
