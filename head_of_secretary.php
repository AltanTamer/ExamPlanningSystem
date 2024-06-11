<?php
session_start();
include('db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Head of Secretary') {
    header("location: login.php");
    exit;
}

$user = $_SESSION['user'];
$facultyID = $user['FacultyID'];


$departmentsQuery = "SELECT DepartmentID, DepartmentName FROM Department WHERE FacultyID = $facultyID";
$departmentsResult = $conn->query($departmentsQuery);

if (!$departmentsResult) {
    die("Departments Query Failed: " . $conn->error);
}

// fakültedeki her dersi döndürür
$availableCoursesQuery = "SELECT CourseID, CourseName FROM Course WHERE FacultyID = $facultyID";
$availableCoursesResult = $conn->query($availableCoursesQuery);

if (!$availableCoursesResult) {
    die("Available Courses Query Failed: " . $conn->error);
}

// course ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insertCourse'])) {
    $departmentID = $_POST['departmentID'];
    $courseName = $_POST['courseName'];
    $day1 = $_POST['day1'];
    $timeSlot1 = $_POST['timeSlot1'];
    $day2 = $_POST['day2'];
    $timeSlot2 = $_POST['timeSlot2'];

    $insertCourseQuery = "INSERT INTO Course (CourseName, DepartmentID, FacultyID, Day1, TimeSlot1, Day2, TimeSlot2) 
                          VALUES ('$courseName', $departmentID, $facultyID, '$day1', '$timeSlot1', '$day2', '$timeSlot2')";
    if (!$conn->query($insertCourseQuery)) {
        die("Insert Course Failed: " . $conn->error);
    }
}

// sınav ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insertExam'])) {
    $courseID = $_POST['courseID'];
    $examName = $_POST['examName'];
    $examDate = $_POST['examDate'];
    $examTime = $_POST['examTime'];
    $numOfAssistants = $_POST['numOfAssistants'];

    $insertExamQuery = "INSERT INTO Exam (ExamName, CourseID, ExamDate, ExamTime, NumOfClasses, FacultyID) 
                        VALUES ('$examName', $courseID, '$examDate', '$examTime', $numOfAssistants, $facultyID)";
    if (!$conn->query($insertExamQuery)) {
        die("Insert Exam Failed: " . $conn->error);
    }

    $examID = $conn->insert_id;

    //fairly asistan dağıtma
    $retrieveAssistantsQuery = "SELECT EmployeeID, FirstName, LastName FROM Employee 
                                WHERE FacultyID = $facultyID AND Role = 'Assistant' 
                                ORDER BY Score ASC LIMIT $numOfAssistants";
    $assistantsResult = $conn->query($retrieveAssistantsQuery);

    if (!$assistantsResult) {
        die("Retrieve Assistants Failed: " . $conn->error);
    }

    $assistantIDs = array();

    echo "<h2>Selected Assistants</h2>";
    echo "<ul>";
    while ($assistant = $assistantsResult->fetch_assoc()) {
        $assistantID = $assistant['EmployeeID'];
        $assistantName = $assistant['FirstName'] . " " . $assistant['LastName'];

        // seçilen asistanın scoreunu güncelle
        $updateScoreQuery = "UPDATE Employee SET Score = Score + 1 WHERE EmployeeID = $assistantID";
        if (!$conn->query($updateScoreQuery)) {
            die("Update Score Failed: " . $conn->error);
        }

        // AssistantExamAssignment güncelle
        $insertAssignmentQuery = "INSERT INTO AssistantExamAssignment (AssistantID, ExamID) VALUES ($assistantID, $examID)";
        if (!$conn->query($insertAssignmentQuery)) {
            $conn->query("ROLLBACK");
            die("Insert Assistant Exam Assignment Failed: " . $conn->error);
        }

        $assistantIDs[] = $assistantID;

        echo "<li>$assistantName</li>";
    }
    echo "</ul>";

    $conn->query("COMMIT");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Head of Secretary Page - <?php echo $user['FirstName'] . ' ' . $user['LastName']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2 {
            margin-bottom: 20px;
        }
        a {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: inline-block;
            margin-bottom: 10px;
        }
        input[type="text"],
        input[type="date"],
        input[type="time"],
        input[type="number"],
        select {
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        table {
            border-collapse: collapse;
            width: 50%;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo $user['FirstName']; ?></h1>
    <a href="logout.php">Logout</a>
    
    <h2>Insert Course Information</h2>
    <form method="post" action="" autocomplete="off">
        <label for="departmentID">Select a Department:</label>
        <select name="departmentID" id="departmentID" required>
            <?php
            mysqli_data_seek($departmentsResult, 0);
            while ($row = $departmentsResult->fetch_assoc()) {
                echo "<option value='" . $row['DepartmentID'] . "'>" . $row['DepartmentName'] . "</option>";
            }
            ?>
        </select><br><br>
        <label for="courseName">Course Name:</label>
        <input type="text" name="courseName" id="courseName" required><br><br>
        <label for="day1">Day 1:</label>
        <select name="day1" id="day1" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
        </select><br><br>
        <label for="timeSlot1">Time Slot 1:</label>
        <input type="time" name="timeSlot1" id="timeSlot1" required><br><br>
        <label for="day2">Day 2:</label>
        <select name="day2" id="day2" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
        </select><br><br>
        <label for="timeSlot2">Time Slot 2:</label>
        <input type="time" name="timeSlot2" id="timeSlot2" required><br><br>
        <button type="submit" name="insertCourse">Insert Course</button>
    </form>

    <h2>Insert Exam Information</h2>
    <form method="post"action="" autocomplete="off">
        <label for="courseID">Select a Course:</label>
        <select name="courseID" id="courseID" required>
            <?php
            mysqli_data_seek($availableCoursesResult, 0);
            while ($row = $availableCoursesResult->fetch_assoc()) {
                echo "<option value='" . $row['CourseID'] . "'>" . $row['CourseName'] . "</option>";
            }
            ?>
        </select><br><br>
        <label for="examName">Exam Name:</label>
        <input type="text" name="examName" id="examName" required><br><br>
        <label for="examDate">Exam Date:</label>
        <input type="date" name="examDate" id="examDate" required><br><br>
        <label for="examTime">Exam Time:</label>
        <input type="time" name="examTime" id="examTime" required><br><br>
        <label for="numOfAssistants">Number of Assistants Needed:</label>
        <input type="number" name="numOfAssistants" id="numOfAssistants" min="1" required><br><br>
        <button type="submit" name="insertExam">Insert Exam</button>
    </form>

    <h2>Assistant Scores</h2>
    <?php
    // Fetch and display assistant scores
    $assistantScoresQuery = "SELECT FirstName, LastName, Score FROM Employee 
                             WHERE FacultyID = $facultyID AND Role = 'Assistant'";
    $assistantScoresResult = $conn->query($assistantScoresQuery);

    if (!$assistantScoresResult) {
        die("Fetch Assistant Scores Failed: " . $conn->error);
    }

    echo "<table>";
    echo "<thead>
          <tr>
          <th>Assistant Name</th>
          <th>Score</th>
          </tr>
          </thead>";
    echo "<tbody>";
    while ($row = $assistantScoresResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['FirstName'] . " " . $row['LastName'] . "</td>";
        echo "<td>" . $row['Score'] . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    ?>

</body>
</html>