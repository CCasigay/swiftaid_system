function fetchReports() {
    fetch('../responders/api/fetch_new-reports.php')
        .then(response => response.json())
        .then(data => {
            if (data && data.newReports && Array.isArray(data.newReports) && data.newReports.length > 0) {
                populateTable(data.newReports, 'newReportsTableBody', true);
            } else {
                console.error('No new reports found.');
                const tableBody = document.getElementById('newReportsTableBody');
                if (tableBody) {
                    tableBody.innerHTML = '<p>No new reports available.</p>';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching reports:', error);
            const tableBody = document.getElementById('newReportsTableBody');
            if (tableBody) {
                tableBody.innerHTML = '<p>Error loading reports.</p>';
            }
        });
}

function populateTable(reports, tableBodyId, includeActions) {
    const tableBody = document.getElementById(tableBodyId);
    if (!tableBody) return;  // Exit if table body is not found
    tableBody.innerHTML = ''; // Clear previous content

    reports.forEach(report => {
        // Format the date to "December 01, 2024"
        const formattedDate = formatDate(report['date']);
        
        // Constructing the message as a notification
        const message = `A report (ID: ${report['report_id']}) from <strong>${report['sender_name']}</strong> (User ID: ${report['user_id']}) has been submitted with a severity level of <strong>${report['severity']}</strong>. The location is at Latitude: ${report['latitude']}, Longitude: ${report['longitude']} on <strong>${formattedDate} at ${report['time']}</strong>.`;

        const actionButton = ` 
            <button class="accept-btn" onclick="acceptReport(${report['report_id']}, '${report['latitude']}', '${report['longitude']}')">Accept</button>
        `;

        // Define the notification class based on the severity
        let alertClass = '';
        if (report['severity'].toLowerCase() === 'critical') {
            alertClass = 'alert-danger'; // Red for critical
        } else if (report['severity'].toLowerCase() === 'emergent') {
            alertClass = 'alert-warning'; // Yellow for emergent
        }

        // Creating a div container for the notification message and action button
        const row = document.createElement('div');
        row.classList.add('alert', alertClass, 'alert-dismissible', 'fade', 'show');
        row.innerHTML = ` 
            <strong>${report['severity']} Report:</strong><br>
            ${message}
            <div class="action-container mt-3">
                ${includeActions ? actionButton : ''} 
            </div>
        `;

        tableBody.appendChild(row);
    });
}

// Function to format date to "December 01, 2024"
function formatDate(dateString) {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 
        'October', 'November', 'December'
    ];

    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0'); // Ensures two-digit day
    const month = months[date.getMonth()];
    const year = date.getFullYear();

    return `${month} ${day}, ${year}`;
}

    fetchReports();
    setInterval(fetchReports, 10000);  // 10000 ms = 10 seconds
