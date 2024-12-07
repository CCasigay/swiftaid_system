function toggleMenu() {
    const hamburger = document.querySelector('.hamburger');
    const mobileNav = document.querySelector('.mobile-nav');
    hamburger.classList.toggle('active');
    mobileNav.classList.toggle('active');
}

// Fetch data from the PHP endpoint
fetch('../api/graph.php')
    .then(response => response.json())
    .then(data => {
        // Prepare the data for the chart
        const labels = data.map(item => item.date);
        const reportCounts = data.map(item => item.count);

        // Data for the line chart
        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Accidents Reported',
                data: reportCounts,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        };

        // Configuration for the chart
        const config = {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
        };

        // Rendering the chart
        var lineChart = new Chart(
            document.getElementById('lineChart'),
            config
        );
    })
    .catch(error => {
        console.error('Error fetching data:', error);
    });