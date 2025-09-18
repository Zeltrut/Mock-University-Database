<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Distribution - CPSC 332 Term Project</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { text-align: center; margin-bottom: 20px; }
        table { width: 50%; margin: 20px auto; border-collapse: collapse; } /* Centered table */
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; text-align: center;}
        td:last-child {text-align: center;} /* Center the count */
        .error { color: red; font-weight: bold; text-align: center; }
        p {text-align: center;}
        .link-container {text-align: center; margin-top: 20px;}
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Grade Distribution</h1>

        <?php
        //database configuration file
        require_once __DIR__ . '/../db_config.php';

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p class='error'>Connection failed: " . $conn->connect_error . "</p></div></body></html>");
        }

        // Get course_num and section_num from the GET request
        if (isset($_GET['course_num']) && !empty($_GET['course_num']) &&
            isset($_GET['section_num']) && !empty($_GET['section_num'])) {

            $course_num = $_GET['course_num'];
            $section_num = $_GET['section_num'];

            echo "<h2>For Course: " . htmlspecialchars($course_num) . ", Section: " . htmlspecialchars($section_num) . "</h2>";

            // SQL Query:
            // Requirement: "count how many students get each distinct grade"
            //
            // ASSUMPTIONS for table/column names:
            // - Enrollment table: `Enrollment` (or similar)
            // - Columns in Enrollment: `CourseNumber`, `SectionNumber`, `Grade` (and a student identifier)

            $sql = "SELECT Grade, COUNT(*) as GradeCount
                    FROM Enrollment  -- Or your actual enrollment table name
                    WHERE CourseNumber = ? AND SectionNumber = ?
                    GROUP BY Grade
                    ORDER BY Grade";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("ss", $course_num, $section_num); // Assuming course_num and section_num are strings or numbers treated as strings
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Grade</th><th>Number of Students</th></tr>";
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["Grade"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["GradeCount"]) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No grade information found for this course section, or the course/section number is incorrect.</p>";
                }
                $stmt->close();
            } else {
                echo "<p class='error'>Error preparing statement: " . htmlspecialchars($conn->error) . "</p>";
            }

        } else {
            echo "<p class='error'>Course number and/or section number not provided.</p>";
        }

        $conn->close();
        ?>
        <div class="link-container">
            <a href="index.html">Back to Homepage</a>
        </div>
    </div>
</body>
</html>
