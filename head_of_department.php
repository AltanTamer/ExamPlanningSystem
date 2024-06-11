<?php
session_start();
include('db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Head of Department') {
    header("location: login.php");
    exit;
}

$user = $_SESSION['user'];

// departman sınavlarını döndürür
$departmentID = $user['DepartmentID'];
$examsQuery = "SELECT ExamID, ExamName, CourseName, ExamDate, ExamTime, NumOfClasses 
               FROM Exam 
               INNER JOIN Course ON Exam.CourseID = Course.CourseID 
               WHERE (Course.DepartmentID = $departmentID OR Course.DepartmentID IS NULL) AND Exam.FacultyID = {$user['FacultyID']}
               ORDER BY ExamDate, ExamTime ASC";
$examsResult = $conn->query($examsQuery);

//score işlemleri
$sumScoreQuery = "SELECT SUM(Score) AS TotalScore 
                  FROM Employee 
                  WHERE Role = 'Assistant' AND DepartmentID = $departmentID";
$sumScoreResult = $conn->query($sumScoreQuery);
$totalScore = $sumScoreResult->fetch_assoc()['TotalScore'];

$assistantsQuery = "SELECT EmployeeID, FirstName, LastName, Score AS TotalScore 
                    FROM Employee 
                    WHERE DepartmentID = $departmentID AND Role = 'Assistant'";
$assistantsResult = $conn->query($assistantsQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Head of Department Page - <?php echo $user['FirstName'] . ' ' . $user['LastName']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2 {
            color: #333;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 10px;
        }
        table {
            border-collapse: collapse;
            width: 50%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .a {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo $user['FirstName']; ?></h1>
    <a href="logout.php" class="a">Logout</a>
    
    <h2>Exam Schedule</h2>
    <?php if ($examsResult->num_rows > 0): ?>
    <ul>
        <?php while ($exam = $examsResult->fetch_assoc()): ?>
        <li><?php echo $exam['ExamName'] . " - " . $exam['CourseName'] . " - " . date('Y-m-d', strtotime($exam['ExamDate'])) . " " . date('H:i', strtotime($exam['ExamTime'])); ?></li>
        <?php endwhile; ?>
    </ul>
    <?php else: ?>
    <p>No exams scheduled.</p>
    <?php endif; ?>

    <h2>Assistants Workload Report</h2>
    <?php if ($assistantsResult->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Assistant Name</th>
                <th>Workload Percentage</th>
                <th>Total Score</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            while ($assistant = $assistantsResult->fetch_assoc()): 
                $workloadPercentage = ($assistant['TotalScore'] / $totalScore) * 100;
            ?>
            <tr>
                <td><?php echo $assistant['FirstName'] . " " . $assistant['LastName']; ?></td>
                <td><?php echo round($workloadPercentage, 2) . "%"; ?></td>
                <td><?php echo $assistant['TotalScore']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No assistants assigned.</p>
    <?php endif; ?>
</body>
</html>
