<?php 
// views/public/index.php

// Includes session_start() and config.php (BASE_URL, SITE_NAME)
include '../templates/header.php'; 
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script> 

<script>
document.addEventListener("DOMContentLoaded", function() {
    var navbar = document.getElementById('mainNav');
    function checkScroll() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
    window.addEventListener('scroll', checkScroll);
    checkScroll();
    
    // --- Dynamic Needs Fetch Logic (Populates URGENTLY NEEDED list and Chart) ---
    const needsContainer = document.getElementById('dynamic-need-list');
    const chartContainer = document.getElementById('regionalSupplyChart');

    function fetchUrgentNeeds() {
        fetch('../../handlers/fetch_public_needs.php') 
        .then(response => response.json())
        .then(data => {
            if (data.success && data.needs.length > 0) {
                
                let chartLabels = [];
                let chartData = [];
                needsContainer.innerHTML = ''; // Clear existing content
                
                data.needs.forEach(need => {
                    let badgeClass = 'badge-info';
                    if (need.urgency === 'CRITICAL') badgeClass = 'badge-danger';
                    if (need.urgency === 'HIGH') badgeClass = 'badge-warning';

                    // 1. Populate URGENTLY NEEDED List
                    const listItem = `
                        <li class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <span class="font-weight-bold display-4" style="color: var(--primary-red);">${need.group}</span>
                            <span class="badge ${badgeClass} badge-pill p-2 font-weight-bold">${need.urgency} (${need.stock} Units)</span>
                        </li>
                    `;
                    needsContainer.innerHTML += listItem;

                    // 2. Prepare Chart Data (Reverse order for better bar chart readability if desired)
                    chartLabels.push(need.group);
                    chartData.push(need.stock);
                });

                // 3. Render Graph
                renderSupplyChart(chartLabels, chartData);

            } else {
                needsContainer.innerHTML = `<li class="text-center text-success font-weight-bold">System stock is healthy!</li>`;
                // Clear chart area if healthy
                if (chartContainer && chartContainer.chart) { chartContainer.chart.destroy(); }
            }
        })
        .catch(error => {
            needsContainer.innerHTML = `<li class="text-center text-danger">Error fetching live data.</li>`;
            console.error('Fetch error:', error);
        });
    }

    // Function to initialize and render the bar chart
    function renderSupplyChart(labels, data) {
        const ctx = chartContainer.getContext('2d');
        
        // Destroy existing chart if it exists to prevent overlap
        if (chartContainer.chart) {
            chartContainer.chart.destroy();
        }

        chartContainer.chart = new Chart(ctx, {
            type: 'bar', // Using Bar Graph for clear comparison of units
            data: {
                labels: labels,
                datasets: [{
                    label: 'Units Available (Below Safety)',
                    data: data,
                    backgroundColor: data.map(units => units <= 10 ? 'rgba(217, 35, 45, 0.8)' : 'rgba(255, 193, 7, 0.8)'), // Red for Critical, Yellow for High
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true, max: 50 }, // Max set to safety threshold
                        scaleLabel: { display: true, labelString: 'Total Units Available' }
                    }],
                    xAxes: [{
                        scaleLabel: { display: true, labelString: 'Blood Group' }
                    }]
                }
            }
        });
    }

    // Run dynamic functions on load
    fetchUrgentNeeds();
    
    // Set intervals for periodic updates
    setInterval(fetchUrgentNeeds, 60000); 
});
</script>

<header id="hero" class="d-flex align-items-center">
    <div id="animated-bg">
        <div class="blood-cell" style="top: 10%; left: 5%; animation-duration: 25s; animation-delay: 1s;"></div>
        <div class="blood-cell" style="top: 50%; left: 30%; animation-duration: 15s; animation-delay: 5s;"></div>
        <div class="blood-cell" style="top: 80%; left: 60%; animation-duration: 30s; animation-delay: 2s;"></div>
    </div>
    
    <div class="container text-center hero-content">
        <p class="text-uppercase mb-2" style="color: var(--accent-gold); font-weight: 600;">A Government-Aiding Unified Platform</p>
        <h1 class="hero-title display-1">
            <span style="color: var(--primary-red);">Empowering</span> Donation, <span style="color: var(--primary-blue);">Saving Lives</span>.
        </h1>
        <p class="lead mb-5 text-light">
            JeevanSetu provides a transparent, intelligent, and real-time link between those who need blood/organs and those who can give.
        </p>
        <a href="register.php" class="btn btn-primary btn-lg font-weight-bold mr-3">
            <i class="fas fa-hand-holding-heart mr-2"></i> Join the JeevanSetu Network
        </a>
        <a href="#needs" class="btn btn-outline-light btn-lg font-weight-bold">
            <i class="fas fa-map-marked-alt mr-2"></i> Find Critical Needs
        </a>
    </div>
</header>

<section id="needs" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-2" style="color: var(--primary-red);">Critical Needs Right Now</h2>
        <p class="text-center lead mb-5 text-muted">A dynamic, real-time snapshot of the most required blood types across the network.</p>
        
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="themed-card p-4 h-100">
                    <h4 class="mb-3" style="color: var(--primary-blue);"><i class="fas fa-chart-bar mr-2"></i> Supply Urgency Visualization</h4>
                    <p class="text-muted">Current inventory levels for groups below the safety threshold (50 units).</p>
                    
                    <div style="height: 350px; display: flex; align-items: center; justify-content: center; margin-top: 10px;">
                        <canvas id="regionalSupplyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <div class="themed-card p-4 h-100 bg-warning text-dark">
                    <h4 class="mb-4 font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> URGENTLY NEEDED</h4>
                    <ul id="dynamic-need-list" class="list-unstyled">
                        <li class="text-center text-muted">Loading critical needs...</li>
                    </ul>
                    <a href="login.php" class="btn btn-dark btn-block mt-4 font-weight-bold">Log in to See Local Needs</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="process" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5" style="color: var(--primary-blue);">How JeevanSetu Works: Simple Steps</h2>
        <div class="row text-center">
            
            <div class="col-md-4 mb-4">
                <div class="themed-card p-4 h-100">
                    <span class="display-3 font-weight-bold" style="color: var(--primary-red);">1</span>
                    <h5 class="card-title mt-3 font-weight-bold">Register & Locate</h5>
                    <p class="card-text text-muted">Sign up as a Donor, Hospital, or Blood Bank. Our system securely stores your details and uses location services to find nearest camps or current emergency needs.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="themed-card p-4 h-100">
                    <span class="display-3 font-weight-bold" style="color: var(--primary-blue);">2</span>
                    <h5 class="card-title mt-3 font-weight-bold">Connect & Coordinate</h5>
                    <p class="card-text text-muted">Hospitals submit authenticated requests. Donors receive targeted SMS/App alerts. Blood Banks use the system to schedule efficient collection drives.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="themed-card p-4 h-100">
                    <span class="display-3 font-weight-bold" style="color: var(--accent-gold);">3</span>
                    <h5 class="card-title mt-3 font-weight-bold">Track & Impact</h5>
                    <p class="card-text text-muted">Donors can digitally track their contribution history and view the estimated impact. Hospitals manage usage and inventory transparently within the platform.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="benefits" class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <h2 style="color: var(--primary-red);">Why Choose JeevanSetu? Our Pledge to Transparency and Efficiency</h2>
                <ul class="list-unstyled mt-4 detailed-list">
                    <li class="mb-3"><i class="fas fa-check-circle mr-3" style="color: var(--primary-blue);"></i> Government Alignment: Built to integrate with national health registries and adheres to governmental donation protocols.</li>
                    <li class="mb-3"><i class="fas fa-check-circle mr-3" style="color: var(--primary-blue);"></i> Smart Predictive Analytics: We use data science to forecast shortages weeks in advance, enabling proactive resource mobilization.</li>
                    <li class="mb-3"><i class="fas fa-check-circle mr-3" style="color: var(--primary-blue);"></i> Data Security & Privacy: Strict encryption, GDPR-compliant standards, and layered access control protect all health and personal data.</li>
                    <li class="mb-3"><i class="fas fa-check-circle mr-3" style="color: var(--primary-blue);"></i> Donor Recognition & History: Provides secure, verified digital records of past donations and personal impact statistics to every donor.</li>
                </ul>
            </div>
            <div class="col-lg-6 mb-4 text-center">
                <div style="
                    height: 350px; 
                    background-color: #e9ecef; 
                    border-radius: 10px; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    border: 1px solid #ccc;
                    overflow: hidden; /* Ensures the image doesn't break the rounded corners */
                ">
                    <img 
                        src="https://img.freepik.com/premium-photo/successful-indian-doctors-team-standing-together_601128-2657.jpg?w=360" 
                        alt="High-fidelity visual mock-up of the JeevanSetu Inventory Dashboard being used by medical staff." 
                        style="
                            width: 100%; 
                            height: 100%; 
                            object-fit: cover; /* Ensures the image covers the 350px container without stretching */
                            border-radius: 10px; /* Applies the radius to the image itself */
                        "
                    >
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 text-center" style="background-color: var(--primary-red); color: white;">
    <div class="container">
        <h2 class="font-weight-bold mb-3">Your Bridge to a Better Tomorrow.</h2>
        <p class="lead mb-4">Join the movement and become a lifeline for someone in need.</p>
        <a href="register.php" class="btn btn-lg btn-light font-weight-bold" style="color: var(--primary-red);">
            <i class="fas fa-heart mr-2"></i> Register Your Intent Today
        </a>
    </div>
</section>

<?php 
// Includes the required JS and closes the HTML document
include '../templates/footer.php'; 
?>