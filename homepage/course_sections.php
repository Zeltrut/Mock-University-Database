<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Sections - CPSC 332 Term Project</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
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
        <h1>Course Sections</h1>

        <?php
        require_once __DIR__ . '/../db_config.php';

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p class='error'>Connection failed: " . $conn->connect_error . "</p></div></body></html>");
        }

        if (isset($_GET['course_num']) && !empty($_GET['course_num'])) {
            $course_num_input = $_GET['course_num'];

            echo "<h2>Sections for Course: " . htmlspecialchars($course_num_input) . "</h2>";

            // SQL Query:
            // Requirement: "Given a course number list the sections of the course, including the classrooms,
            // the meeting days and time, and the number of students enrolled in each section."
            // Tables: Sections, Courses (for title), Enrollment (for student count)
            // Columns from DDL:
            // Sections: CourseNumber, SectionNumber, Classroom, MeetingDays, StartTime, EndTime
            // Courses: CourseNumber, CourseTitle
            // Enrollment: StudentID, CourseNumber, SectionNumber

            $sql = "SELECT S.SectionNumber, C.CourseTitle, S.Classroom, S.MeetingDays, 
                           TIME_FORMAT(S.StartTime, '%h:%i %p') AS FormattedStartTime, 
                           TIME_FORMAT(S.EndTime, '%h:%i %p') AS FormattedEndTime,
                           COUNT(E.StudentID) AS EnrolledStudents
                    FROM Sections S
                    JOIN Courses C ON S.CourseNumber = C.CourseNumber
                    LEFT JOIN Enrollment E ON S.CourseNumber = E.CourseNumber AND S.SectionNumber = E.SectionNumber
                    WHERE S.CourseNumber = ?
                    GROUP BY S.CourseNumber, S.SectionNumber, C.CourseTitle, S.Classroom, S.MeetingDays, S.StartTime, S.EndTime
                    ORDER BY S.SectionNumber";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $course_num_input);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Section</th><th>Course Title</th><th>Classroom</th><th>Meeting Days</th><th>Start Time</th><th>End Time</th><th>Enrolled Students</th></tr>";
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["SectionNumber"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["CourseTitle"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Classroom"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["MeetingDays"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["FormattedStartTime"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["FormattedEndTime"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["EnrolledStudents"]) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No sections found for this course number, or the course number is incorrect.</p>";
                }
                $stmt->close();
            } else {
                echo "<p class='error'>Error preparing statement: " . htmlspecialchars($conn->error) . "</p>";
            }

        } else {
            echo "<p class='error'>Course number not provided.</p>";
        }

        $conn->close();
        ?>
        <div class="link-container">
            <a href="index.html">Back to Homepage</a>
        </div>
    </div>
</body>
</html>
