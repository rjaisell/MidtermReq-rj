<?php include 'db.php'; ?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: incorrect.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="main.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

</head>
<body>
<div class="sidebar">
    <h2>Welcome, Admin!</h2>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="project.php">Students</a></li>
        <li class="dropdown">
            <a href="#" class="dropbtn" onclick="toggleDropdown(event)">Courses</a>
            <div class="dropdown-content">
                <a href="courses.php">Manage Subjects</a>
                <a href="subjects_dashboard.php">View Subjects</a>
            </div>
        </li>
        <li><a href="members.php">Departments</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<script>
    // Function to toggle the dropdown visibility
    function toggleDropdown(event) {
        const dropdownContent = event.target.nextElementSibling; // Get the dropdown content (div)
        
        // Toggle the 'show' class which controls visibility
        dropdownContent.classList.toggle('show');
        
        // Close the dropdown if clicked anywhere outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                dropdownContent.classList.remove('show');
            }
        });
    }
</script>


    <div class="main-content">
        <header>
            <h1>Dashboard</h1>
        </header>
        <main>
            <div class="stats">
                <div class="stat">
                    <img src="/images/student.png" alt="Students">
                    <h2>Students</h2>
                </div>
                <div class="stat">
                    <img src="/images/course.png" alt="Courses">
                    <h2>Courses</h2>
                </div>
                <div class="stat">
                    <img src="/images/department.png" alt="Departments">
                    <h2>Departments</h2>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="studentChart"></canvas>
            </div>
        </main>
    </div>

    <script>
        var ctx = document.getElementById('studentChart').getContext('2d');
        var studentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
                datasets: [{
                    label: 'Enrolled Students',
                    data: [180, 150, 100, 130,80, 68, 50], 
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
