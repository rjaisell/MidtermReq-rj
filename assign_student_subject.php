<?php
require_once 'db.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: incorrect.php');
    exit();
}

// Fetch available students with enrollment year and year level
$students_sql = "
    SELECT students.student_id, students.name, enrollments.enrollment_year, enrollments.year_level
    FROM students
    JOIN enrollments ON students.student_id = enrollments.student_id";
$students_result = $conn->query($students_sql);

// Fetch available sections and subjects for filtering
$sections = $conn->query("SELECT section_id, section_name FROM sections");
$subjects = $conn->query("SELECT subject_id, name FROM subjects");

// Fetch available section-subject mappings
$section_subjects = $conn->query("
    SELECT ss.section_subject_id, s.section_name, sub.name AS subject_name
    FROM section_subject ss
    JOIN sections s ON ss.section_id = s.section_id
    JOIN subjects sub ON ss.subject_id = sub.subject_id
");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_students = $_POST['student_ids'] ?? []; // Array of selected student IDs
    $selected_section_subjects = $_POST['section_subject_ids'] ?? []; // Array of selected section-subject IDs

    $success_message = '';
    $error_message = '';

    foreach ($selected_students as $student_id) {
        foreach ($selected_section_subjects as $section_subject_id) {
            $student_id = intval($student_id);
            $section_subject_id = intval($section_subject_id);

            // Insert student-section-subject mapping
            $sql = "INSERT INTO student_section_subject (student_id, section_subject_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $student_id, $section_subject_id);

            if (!$stmt->execute()) {
                $error_message = "Error assigning section-subject ID $section_subject_id to student $student_id: " . $conn->error;
            } else {
                $success_message = "Assigned Successfully!";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Students to Section-Subjects</title>
    <link rel="stylesheet" href="page.css"> 
    <script>
        function filterSectionSubjects() {
            const sectionFilter = document.getElementById('section_filter').value;
            const subjectFilter = document.getElementById('subject_filter').value;

            const checkboxes = document.querySelectorAll('.section-subject-checkbox');
            checkboxes.forEach(checkbox => {
                const sectionName = checkbox.getAttribute('data-section');
                const subjectName = checkbox.getAttribute('data-subject');

                if ((sectionFilter === "" || sectionName === sectionFilter) &&
                    (subjectFilter === "" || subjectName === subjectFilter)) {
                    checkbox.parentElement.style.display = '';
                } else {
                    checkbox.parentElement.style.display = 'none';
                }
            });
        }

        function filterStudents() {
            const yearFilter = document.getElementById('year_filter').value;
            const levelFilter = document.getElementById('level_filter').value;

            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                const studentYear = checkbox.getAttribute('data-year');
                const studentLevel = checkbox.getAttribute('data-level');

                if ((yearFilter === "" || studentYear === yearFilter) &&
                    (levelFilter === "" || studentLevel === levelFilter)) {
                    checkbox.style.display = ''; // Show matching student
                } else {
                    checkbox.style.display = 'none'; // Hide non-matching student
                }
            });
        }
        
        // JavaScript for fade-out of the popup message
        document.addEventListener('DOMContentLoaded', function() {
            const popupMessage = document.getElementById('popup-message');
            if (popupMessage) {
                setTimeout(function() {
                    popupMessage.style.animation = 'fadeOut 1s ease-in-out forwards';
                }, 3000); // Wait 3 seconds before fading out
            }
        });
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <header><h1>Assign Students to Section-Subjects</h1></header>

        <!-- Display Success/Error Popup Message -->
        <?php if (isset($success_message)): ?>
            <div class="popup-message" id="popup-message">
                <?= $success_message ?>
            </div>
        <?php elseif (isset($error_message)): ?>
            <div class="popup-message error" id="popup-message">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <!-- Filter Students -->
            <section>
                <label for="year_filter">Filter by Enrollment Year:</label><br>
                <select id="year_filter" onchange="filterStudents()">
                <option value="">All</option>
        <?php
            // Predefined range of years from 2020 to 2030
            for ($year = 2020; $year <= 2030; $year++) {
                echo "<option value='$year'>$year</option>";
            }
        ?>
    </select><br><br>

                <label for="level_filter">Filter by Grade Level:</label><br>
                <select id="level_filter" onchange="filterStudents()">
                    <option value="">All</option>
                    <option value="1">Grade 1</option>
                    <option value="2">Grade 2</option>
                    <option value="3">Grade 3</option>
                    <option value="4">Grade 4</option>
                    <option value="5">Grade 5</option>
                    <option value="6">Grade 6</option>
                </select><br><br>

                <header><h1>Select Students:</h1></header>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                    <div class="student-checkbox" 
                         data-year="<?= htmlspecialchars($student['enrollment_year']) ?>" 
                         data-level="<?= htmlspecialchars($student['year_level']) ?>">
                        <input type="checkbox" name="student_ids[]" value="<?= $student['student_id'] ?>">
                        <?= htmlspecialchars($student['name']) ?> (Year: <?= htmlspecialchars($student['enrollment_year']) ?>, Grade: <?= htmlspecialchars($student['year_level']) ?>)
                    </div>
                <?php endwhile; ?><br>
            </section>

            <!-- Filter Section-Subjects -->
            <section>
                <label for="section_filter">Filter by Section:</label><br>
                <select id="section_filter" onchange="filterSectionSubjects()">
                    <option value="">All</option>
                    <?php
                    while ($section = $sections->fetch_assoc()) {
                        echo "<option value='" . $section['section_name'] . "'>" . $section['section_name'] . "</option>";
                    }
                    ?>
                </select><br><br>

                <label for="subject_filter">Filter by Subject:</label><br>
                <select id="subject_filter" onchange="filterSectionSubjects()">
                    <option value="">All</option>
                    <?php
                    while ($subject = $subjects->fetch_assoc()) {
                        echo "<option value='" . $subject['name'] . "'>" . $subject['name'] . "</option>";
                    }
                    ?>
                </select><br><br>

                <header><h1>Select Section-Subjects:</h1></header>
                <?php while ($ss = $section_subjects->fetch_assoc()): ?>
                    <div>
                        <input 
                            type="checkbox" 
                            class="section-subject-checkbox" 
                            name="section_subject_ids[]" 
                            value="<?= $ss['section_subject_id'] ?>"
                            data-section="<?= $ss['section_name'] ?>"
                            data-subject="<?= $ss['subject_name'] ?>"
                        >
                        <?= htmlspecialchars($ss['section_name']) ?> - <?= htmlspecialchars($ss['subject_name']) ?>
                    </div>
                <?php endwhile; ?><br>
            </section>

            <button type="submit">Assign Students</button>
        </form>
    </div>
</body>
</html>
