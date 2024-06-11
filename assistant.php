<?php
session_start();
include('db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Assistant') {
    header("location: login.php");
    exit;
}

$user = $_SESSION['user'];
$assistantID = $user['EmployeeID'];
$departmentID = $user['DepartmentID'];

// ders register
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['courseID'])) {
    $courseID = $_POST['courseID'];
    $checkQuery = "SELECT * FROM CourseRegistration WHERE EmployeeID = $assistantID AND CourseID = $courseID";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows == 0) {
        $registerQuery = "INSERT INTO CourseRegistration (EmployeeID, CourseID) VALUES ($assistantID, $courseID)";
        if (!$conn->query($registerQuery)) {
            die("Course Registration Failed: " . $conn->error);
        }
    }
}

// kayıtlı course döndürür
$coursesQuery = "SELECT Course.CourseName, Course.Day1 AS Day, Course.TimeSlot1 AS TimeSlot FROM Course
                 INNER JOIN CourseRegistration ON Course.CourseID = CourseRegistration.CourseID
                 WHERE CourseRegistration.EmployeeID = $assistantID
                 UNION
                 SELECT Course.CourseName, Course.Day2 AS Day, Course.TimeSlot2 AS TimeSlot FROM Course
                 INNER JOIN CourseRegistration ON Course.CourseID = CourseRegistration.CourseID
                 WHERE CourseRegistration.EmployeeID = $assistantID";
$coursesResult = $conn->query($coursesQuery);

if (!$coursesResult) {
    die("Courses Query Failed: " . $conn->error);
}

// asistanın görevli olduğu sınavı döndürür
$examsQuery = "SELECT Course.CourseName, Exam.ExamDate AS Day, Exam.ExamTime AS TimeSlot 
               FROM Exam
               INNER JOIN Course ON Exam.CourseID = Course.CourseID
               INNER JOIN AssistantExamAssignment ON Exam.ExamID = AssistantExamAssignment.ExamID
               WHERE AssistantExamAssignment.AssistantID = $assistantID";
$examsResult = $conn->query($examsQuery);

if (!$examsResult) {
    die("Exams Query Failed: " . $conn->error);
}

//timetablei oluşturur
$weeklySchedule = array();
$timeslots = array('09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00');
$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
foreach ($timeslots as $timeslot) {
    foreach ($days as $day) {
        $weeklySchedule[$timeslot][$day] = '';
    }
}

function adjustTimeslot($timeslot) {
    $time = strtotime($timeslot);
    $hour = date('H', $time);
    return sprintf('%02d:00:00', $hour);
}

while ($row = $coursesResult->fetch_assoc()) {
    $adjustedTimeslot = adjustTimeslot($row['TimeSlot']);
    if (isset($weeklySchedule[$adjustedTimeslot][$row['Day']])) {

        $hour = intval(substr($adjustedTimeslot, 0, 2));
        $courseHour = intval(substr($row['TimeSlot'], 0, 2));
        $hourDiff = $courseHour - $hour;

        $adjustedHour = $hour + $hourDiff;
        if ($adjustedHour < 10) {
            $adjustedTimeslot = '0' . $adjustedHour . ':00:00';
        } else {
            $adjustedTimeslot = $adjustedHour . ':00:00';
        }
        $weeklySchedule[$adjustedTimeslot][$row['Day']] .= $row['CourseName'] . "<br>";
    }
}

$availableCoursesQuery = "SELECT CourseID, CourseName FROM Course WHERE DepartmentID = $departmentID";
$availableCoursesResult = $conn->query($availableCoursesQuery);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Assistant Page - <?php echo $user['FirstName'] . ' ' . $user['LastName']; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2 {
            color: #333;
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
        table {
            width: 80%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .rectangle {
            border: 1px solid #ccc;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: inline-block;
            margin-right: 10px;
        }
        select, button {
            padding: 5px 10px;
            margin-right: 10px;
        }
        button {
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo $user['FirstName']; ?></h1>
    <a href="logout.php">Logout</a>
    
    <h2>Weekly Schedule</h2>
    <table>
        <thead>
            <tr>
                <th class="rectangle">Timeslot</th>
                <?php foreach ($days as $day): ?>
                    <th class="rectangle"><?php echo $day; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($timeslots as $timeslot): ?>
                <tr>
                    <td class="rectangle"><?php echo $timeslot; ?></td>
                    <?php foreach ($days as $day): ?>
                        <td class="rectangle"><?php echo $weeklySchedule[$timeslot][$day]; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button onclick="location.reload();">Refresh</button>

    <h2>Register for a Course</h2>
    <form method="post">
        <label for="courseID">Select a Course:</label>
        <select name="courseID" id="courseID" required>
            <?php
            mysqli_data_seek($availableCoursesResult, 0);
            while ($row = $availableCoursesResult->fetch_assoc()) {
                echo "<option value='" . $row['CourseID'] . "'>" . $row['CourseName'] . "</option>";
            }
            ?>
        </select>
        <button type="submit">Register</button>
    </form>

    <h2>Assigned Exams</h2>
    <table>
        <thead>
            <tr>
                <th class="rectangle">Course</th>
                <th class="rectangle">Exam Date</th>
                <th class="rectangle">Exam Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $examsResult->fetch_assoc()): ?>
                <tr>
                    <td class="rectangle"><?php echo $row['CourseName']; ?></td>
                    <td class="rectangle"><?php echo $row['Day']; ?></td>
                    <td class="rectangle"><?php echo $row['TimeSlot']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
