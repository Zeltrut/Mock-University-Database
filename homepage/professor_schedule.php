<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Schedule - CPSC 332 Term Project</title>
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
        <h1>Professor's Class Schedule</h1>

        <?php
        // Include the database configuration file
        require_once __DIR__ . '/../db_config.php';

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p class='error'>Connection failed: " . $conn->connect_error . "</p></div></body></html>");
        }

        if (isset($_GET['prof_ssn']) && !empty($_GET['prof_ssn'])) {
            $prof_ssn_input = $_GET['prof_ssn'];

            // Optional: Fetch professor's name for display
            $prof_name = "";
            $name_stmt = $conn->prepare("SELECT ProfFirstName, ProfLastName FROM Professors WHERE ProfSSN = ?");
            if ($name_stmt) {
                $name_stmt->bind_param("s", $prof_ssn_input);
                $name_stmt->execute();
                $name_result = $name_stmt->get_result();
                if ($name_row = $name_result->fetch_assoc()) {
                    $prof_name = htmlspecialchars($name_row['ProfFirstName']) . " " . htmlspecialchars($name_row['ProfLastName']);
                }
                $name_stmt->close();
            }

            echo "<h2>Schedule for Professor SSN: " . htmlspecialchars($prof_ssn_input) . ($prof_name ? " (" . $prof_name . ")" : "") . "</h2>";

            $sql = "SELECT C.CourseTitle, S.CourseNumber, S.SectionNumber, S.Classroom, S.MeetingDays, 
                           TIME_FORMAT(S.StartTime, '%h:%i %p') AS FormattedStartTime, 
                           TIME_FORMAT(S.EndTime, '%h:%i %p') AS FormattedEndTime
                    FROM Sections S
                    JOIN Courses C ON S.CourseNumber = C.CourseNumber
                    WHERE S.TaughtByProfSSN = ?
                    ORDER BY S.CourseNumber, S.SectionNumber";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $prof_ssn_input);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Course Title</th><th>Course Number</th><th>Section</th><th>Classroom</th><th>Meeting Days</th><th>Start Time</th><th>End Time</th></tr>";
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["CourseTitle"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["CourseNumber"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["SectionNumber"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Classroom"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["MeetingDays"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["FormattedStartTime"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["FormattedEndTime"]) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No classes found for this professor SSN, or the SSN is incorrect.</p>";
                }
                $stmt->close();
            } else {
                echo "<p class='error'>Error preparing statement: " . htmlspecialchars($conn->error) . "</p>";
            }

        } else {
            echo "<p class='error'>Professor SSN not provided.</p>";
        }

        $conn->close();
        ?>
        <div class="link-container">
            <a href="index.html">Back to Homepage</a>
        </div>
    </div>
</body>
</html>
