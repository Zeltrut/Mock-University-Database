<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transcript - CPSC 332 Term Project</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { text-align: center; margin-bottom: 20px; }
        table { width: 80%; margin: 20px auto; border-collapse: collapse; } /* Centered table */
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .error { color: red; font-weight: bold; text-align: center;}
        p {text-align: center;}
        .link-container {text-align: center; margin-top: 20px;}
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Transcript</h1>

        <?php
        require_once __DIR__ . '/../db_config.php';

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p class='error'>Connection failed: " . $conn->connect_error . "</p></div></body></html>");
        }

        if (isset($_GET['student_id']) && !empty($_GET['student_id'])) {
            $student_id_input = $_GET['student_id'];

            // Fetch student's name for display
            $student_name = "";
            $name_stmt = $conn->prepare("SELECT StudFirstName, StudLastName FROM Students WHERE StudentID = ?");
            if ($name_stmt) {
                $name_stmt->bind_param("s", $student_id_input);
                $name_stmt->execute();
                $name_result = $name_stmt->get_result();
                if ($name_row = $name_result->fetch_assoc()) {
                    $student_name = htmlspecialchars($name_row['StudFirstName']) . " " . htmlspecialchars($name_row['StudLastName']);
                }
                $name_stmt->close();
            }

            echo "<h2>Transcript for Student ID: " . htmlspecialchars($student_id_input) . ($student_name ? " (" . $student_name . ")" : "") . "</h2>";

            // SQL Query:
            // Requirement: "Given the campus wide ID of a student, list all courses the student took and the grades."
            // Tables: Enrollment, Courses
            // Columns from DDL:
            // Enrollment: StudentID, CourseNumber, SectionNumber, Grade
            // Courses: CourseNumber, CourseTitle

            $sql = "SELECT E.CourseNumber, C.CourseTitle, E.SectionNumber, E.Grade
                    FROM Enrollment E
                    JOIN Courses C ON E.CourseNumber = C.CourseNumber
                    WHERE E.StudentID = ?
                    ORDER BY E.CourseNumber, E.SectionNumber"; // Or by term/year if you add that data

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $student_id_input);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Course Number</th><th>Course Title</th><th>Section</th><th>Grade</th></tr>";
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["CourseNumber"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["CourseTitle"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["SectionNumber"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Grade"]) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No courses found for this student ID, or the ID is incorrect.</p>";
                }
                $stmt->close();
            } else {
                echo "<p class='error'>Error preparing statement: " . htmlspecialchars($conn->error) . "</p>";
            }

        } else {
            echo "<p class='error'>Student ID not provided.</p>";
        }

        $conn->close();
        ?>
        <div class="link-container">
            <a href="index.html">Back to Homepage</a>
        </div>
    </div>
</body>
</html>
