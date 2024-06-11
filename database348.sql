    CREATE DATABASE 348_project;

    USE 348_project;

    CREATE TABLE Faculty (
        FacultyID INT AUTO_INCREMENT PRIMARY KEY,
        FacultyName VARCHAR(100) NOT NULL
    );

    CREATE TABLE Department (
        DepartmentID INT AUTO_INCREMENT PRIMARY KEY,
        DepartmentName VARCHAR(100) NOT NULL,
        FacultyID INT,
        FOREIGN KEY (FacultyID) REFERENCES Faculty(FacultyID)
    );

    CREATE TABLE Course (
        CourseID INT AUTO_INCREMENT PRIMARY KEY,
        CourseName VARCHAR(100) NOT NULL,
        DepartmentID INT,
        FacultyID INT,
        Day1 ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
        TimeSlot1 TIME NOT NULL,
        Day2 ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
        TimeSlot2 TIME NOT NULL,
        FOREIGN KEY (DepartmentID) REFERENCES Department(DepartmentID),
        FOREIGN KEY (FacultyID) REFERENCES Faculty(FacultyID)
    );

    CREATE TABLE Employee (
        EmployeeID INT AUTO_INCREMENT PRIMARY KEY,
        FirstName VARCHAR(100) NOT NULL,
        LastName VARCHAR(100) NOT NULL,
        Role ENUM('Assistant', 'Secretary', 'Head of Department', 'Head of Secretary', 'Dean') NOT NULL,
        DepartmentID INT,
        FacultyID INT,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        Score INT DEFAULT 0,  
        FOREIGN KEY (DepartmentID) REFERENCES Department(DepartmentID),
        FOREIGN KEY (FacultyID) REFERENCES Faculty(FacultyID)
    );

    CREATE TABLE CourseRegistration (
        RegistrationID INT AUTO_INCREMENT PRIMARY KEY,
        CourseID INT,
        EmployeeID INT,
        FOREIGN KEY (CourseID) REFERENCES Course(CourseID),
        FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeID)
    );

    CREATE TABLE Exam (
        ExamID INT AUTO_INCREMENT PRIMARY KEY,
        ExamName VARCHAR(100) NOT NULL,
        CourseID INT,
        ExamDate DATE NOT NULL,
        ExamTime TIME NOT NULL,
        NumOfClasses INT NOT NULL,
        FacultyID INT,
        FOREIGN KEY (CourseID) REFERENCES Course(CourseID),
        FOREIGN KEY (FacultyID) REFERENCES Faculty(FacultyID)
    );

    CREATE TABLE AssistantExamAssignment (
        AssignmentID INT AUTO_INCREMENT PRIMARY KEY,
        ExamID INT,
        AssistantID INT,
        FOREIGN KEY (ExamID) REFERENCES Exam(ExamID),
        FOREIGN KEY (AssistantID) REFERENCES Employee(EmployeeID)
    );

    INSERT INTO Faculty (FacultyName) VALUES 
    ('Engineering'),
    ('Education');

    INSERT INTO Department (DepartmentName, FacultyID) VALUES 
    ('Computer Engineering', 1),
    ('Mechanical Engineering', 1),
    ('Sports Education', 2);

    INSERT INTO Course (CourseName, DepartmentID, FacultyID, Day1, TimeSlot1, Day2, TimeSlot2) VALUES 
    ('CSE348', 1, 1, 'Monday', '09:00:00', 'Wednesday', '09:00:00'),
    ('PES101', 3, 2, 'Tuesday', '13:00:00', 'Thursday', '13:00:00'),
    ('ME201', 2, 1, 'Tuesday', '13:00:00', 'Thursday', '13:00:00'),
    ('CSE101', 1, 1, 'Friday', '12:00:00', 'Wednesday', '15:00:00');

    INSERT INTO Employee (FirstName, LastName, Role, DepartmentID, FacultyID, username, password, score) VALUES 
    ('as1', '1-1', 'Assistant', 1, 1, 'as1', '1',0), 
    ('se1', '1-1', 'Secretary', 1, 1, 'se1', '1',0), 
    ('hd1', '1-1', 'Head of Department', 1, 1, 'hd1', '1',0), 
    ('hs1', '1', 'Head of Secretary', NULL, 1, 'hs1', '1',0), 
    ('de1', '1', 'Dean', NULL, 1, 'de1', '1',0),
    ('as2', '1-1', 'Assistant', 1, 1, 'as2', '1',0), 
    ('as3', '2-2', 'Assistant', 2, 2, 'as3', '1',0),
    ('as4', '3-1', 'Assistant', 3, 1, 'as4', '1',0);
 


    INSERT INTO CourseRegistration (CourseID, EmployeeID) VALUES 
    (1, 1), 
    (2, 1), 
    (3, 1);

    INSERT INTO Exam (ExamName, CourseID, ExamDate, ExamTime, NumOfClasses, FacultyID) VALUES 
    ('Midterm', 1, '2024-04-30', '18:00:00', 2, 1);

    INSERT INTO AssistantExamAssignment (ExamID, AssistantID) VALUES 
    (1, 1);
