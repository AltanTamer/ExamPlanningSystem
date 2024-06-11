<?php
session_start();
include('db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Dean') {
    header("location: login.php");
    exit;
}

$user = $_SESSION['user'];
$facultyID = $user['FacultyID'];

// fakültenin departmanlarını döndür
$departmentsQuery = "SELECT * FROM Department WHERE FacultyID = $facultyID";
$departmentsResult = $conn->query($departmentsQuery);

if (!$departmentsResult) {
    die("Departments Query Failed: " . $conn->error);
}

// seçilen departman sınavlarını döndür
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['department'])) {
    $selectedDepartmentID = $_POST['department'];
    $examsQuery = "SELECT Exam.ExamName, Course.CourseName, Exam.ExamDate, Exam.ExamTime, Exam.NumOfClasses 
                   FROM Exam 
                   INNER JOIN Course ON Exam.CourseID = Course.CourseID 
                   WHERE Course.DepartmentID = $selectedDepartmentID";
    $examsResult = $conn->query($examsQuery);

    if (!$examsResult) {
        die("Exams Query Failed: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dean Page - <?php echo $user['FirstName'] . ' ' . $user['LastName']; ?></title>
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
            width: 100%;
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

    <h2>Select Department</h2>
    <form method="post" action="">
        <label for="department">Department:</label>
        <select name="department" id="department" required>
            <?php
            mysqli_data_seek($departmentsResult, 0);
            while ($department = $departmentsResult->fetch_assoc()) {
                echo "<option value='" . $department['DepartmentID'] . "'>" . $department['DepartmentName'] . "</option>";
            }
            ?>
        </select>
        <button type="submit">Select</button>
    </form>

    <?php if (isset($examsResult) && $examsResult->num_rows > 0): ?>
    <h2>Exams</h2>
    <table>
        <thead>
            <tr>
                <th>Exam Name</th>
                <th>Course Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Number of Classes</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($exam = $examsResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo $exam['ExamName']; ?></td>
                <td><?php echo $exam['CourseName']; ?></td>
                <td><?php echo $exam['ExamDate']; ?></td>
                <td><?php echo $exam['ExamTime']; ?></td>
                <td><?php echo $exam['NumOfClasses']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php elseif (isset($examsResult) && $examsResult->num_rows == 0): ?>
    <p>No exams found for the selected department.</p>
    <?php endif; ?>
</body>
</html>
