-- CPSC 332 Term Project - University Database DDL
-- Use cs332t21;


-- 1. Professors Table
CREATE TABLE Professors (
    ProfSSN VARCHAR(11) NOT NULL,
    ProfFirstName VARCHAR(50) NOT NULL,
    ProfLastName VARCHAR(50) NOT NULL,
    ProfStreet VARCHAR(100),
    ProfCity VARCHAR(50),
    ProfState VARCHAR(2),
    ProfZip VARCHAR(10),
    ProfPhoneNumber VARCHAR(20),
    ProfSex ENUM('Male', 'Female', 'Other', 'Prefer not to say'),
    ProfTitle VARCHAR(50),
    ProfSalary DECIMAL(10, 2),
    PRIMARY KEY (ProfSSN)
);

-- 2. Degrees Table (One professor can have multiple degrees)
CREATE TABLE Degrees (
    DegreeID INT AUTO_INCREMENT NOT NULL,
    ProfessorSSN VARCHAR(11) NOT NULL,
    DegreeName VARCHAR(100) NOT NULL,
    GrantingInstitution VARCHAR(100),
    YearGranted YEAR,
    PRIMARY KEY (DegreeID),
    FOREIGN KEY (ProfessorSSN) REFERENCES Professors(ProfSSN)
        ON DELETE CASCADE -- If a professor is deleted, their degrees are also deleted.
        ON UPDATE CASCADE -- If a professor's SSN is updated, update it here too.
);

-- 3. Departments Table
CREATE TABLE Departments (
    DeptNumber VARCHAR(10) NOT NULL,
    DeptName VARCHAR(100) NOT NULL,
    DeptPhoneNumber VARCHAR(20),
    DeptOfficeLocation VARCHAR(50),
    ChairpersonSSN VARCHAR(11), -- Can be NULL if a chair is not yet assigned or leaves
    PRIMARY KEY (DeptNumber),
    UNIQUE (DeptName), -- Department names should be unique
    FOREIGN KEY (ChairpersonSSN) REFERENCES Professors(ProfSSN)
        ON DELETE SET NULL -- If the chairing professor is deleted, set this to NULL.
        ON UPDATE CASCADE
);

-- 4. Courses Table
CREATE TABLE Courses (
    CourseNumber VARCHAR(10) NOT NULL, -- e.g., 'CPSC332'
    CourseTitle VARCHAR(100) NOT NULL,
    Textbook VARCHAR(255),
    Units INT,
    OfferedByDeptNumber VARCHAR(10) NOT NULL,
    PRIMARY KEY (CourseNumber),
    FOREIGN KEY (OfferedByDeptNumber) REFERENCES Departments(DeptNumber)
        ON DELETE RESTRICT -- Don't delete a department if it still offers courses.
        ON UPDATE CASCADE
);

-- 5. Prerequisites Table (Many-to-many relationship between courses)
CREATE TABLE Prerequisites (
    MainCourseNumber VARCHAR(10) NOT NULL,
    PrereqCourseNumber VARCHAR(10) NOT NULL,
    PRIMARY KEY (MainCourseNumber, PrereqCourseNumber),
    FOREIGN KEY (MainCourseNumber) REFERENCES Courses(CourseNumber)
        ON DELETE CASCADE -- If a course is deleted, its prerequisite relationships are removed.
        ON UPDATE CASCADE,
    FOREIGN KEY (PrereqCourseNumber) REFERENCES Courses(CourseNumber)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- 6. Sections Table
CREATE TABLE Sections (
    CourseNumber VARCHAR(10) NOT NULL,
    SectionNumber VARCHAR(5) NOT NULL, -- e.g., '01', 'S1'
    Classroom VARCHAR(20),
    NumberOfSeats INT,
    MeetingDays VARCHAR(10), -- e.g., 'MWF', 'TR'
    StartTime TIME,
    EndTime TIME,
    TaughtByProfSSN VARCHAR(11), -- Can be NULL if instructor not yet assigned
    PRIMARY KEY (CourseNumber, SectionNumber),
    FOREIGN KEY (CourseNumber) REFERENCES Courses(CourseNumber)
        ON DELETE CASCADE -- If a course is deleted, its sections are also deleted.
        ON UPDATE CASCADE,
    FOREIGN KEY (TaughtByProfSSN) REFERENCES Professors(ProfSSN)
        ON DELETE SET NULL -- If the teaching professor is deleted, set this to NULL.
        ON UPDATE CASCADE
);

-- 7. Students Table
CREATE TABLE Students (
    StudentID VARCHAR(20) NOT NULL, -- Campus Wide ID
    StudFirstName VARCHAR(50) NOT NULL,
    StudLastName VARCHAR(50) NOT NULL,
    StudStreet VARCHAR(100),
    StudCity VARCHAR(50),
    StudState VARCHAR(2),
    StudZip VARCHAR(10),
    StudPhoneNumber VARCHAR(20),
    MajorDeptNumber VARCHAR(10) NOT NULL,
    PRIMARY KEY (StudentID),
    FOREIGN KEY (MajorDeptNumber) REFERENCES Departments(DeptNumber)
        ON DELETE RESTRICT -- Don't delete a department if students are majoring in it.
        ON UPDATE CASCADE
);

-- 8. StudentMinors Table (Many-to-many between Students and Departments for minors)
CREATE TABLE StudentMinors (
    StudentID VARCHAR(20) NOT NULL,
    MinorDeptNumber VARCHAR(10) NOT NULL,
    PRIMARY KEY (StudentID, MinorDeptNumber),
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID)
        ON DELETE CASCADE -- If a student is deleted, their minor records are removed.
        ON UPDATE CASCADE,
    FOREIGN KEY (MinorDeptNumber) REFERENCES Departments(DeptNumber)
        ON DELETE CASCADE -- If a department is deleted, remove it as a minor option.
        ON UPDATE CASCADE
);

-- 9. Enrollment Table
CREATE TABLE Enrollment (
    EnrollmentID INT AUTO_INCREMENT NOT NULL,
    StudentID VARCHAR(20) NOT NULL,
    CourseNumber VARCHAR(10) NOT NULL,
    SectionNumber VARCHAR(5) NOT NULL,
    Grade VARCHAR(3), -- e.g., 'A', 'B+', 'A-', 'IP' (In Progress), 'W' (Withdraw)
    PRIMARY KEY (EnrollmentID),
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID)
        ON DELETE CASCADE -- If a student is deleted, their enrollment records are deleted.
        ON UPDATE CASCADE,
    FOREIGN KEY (CourseNumber, SectionNumber) REFERENCES Sections(CourseNumber, SectionNumber)
        ON DELETE CASCADE -- If a section is deleted, enrollment records for it are deleted.
        ON UPDATE CASCADE,
    UNIQUE (StudentID, CourseNumber, SectionNumber) -- A student can only be enrolled in a specific section once.
);

-- Data insertion

INSERT INTO Departments (DeptNumber, DeptName, DeptPhoneNumber, DeptOfficeLocation, ChairpersonSSN) VALUES
('CS', 'Computer Science', '657-278-3700', 'CS-504', NULL),
('MATH', 'Mathematics', '657-278-XXXX', 'MA-XXX', NULL);

-- -----------------------------------------------------------------------------
-- Professors (3 required, creating 3)
-- -----------------------------------------------------------------------------
INSERT INTO Professors (ProfSSN, ProfFirstName, ProfLastName, ProfStreet, ProfCity, ProfState, ProfZip, ProfPhoneNumber, ProfSex, ProfTitle, ProfSalary) VALUES
('111-00-1111', 'Alice', 'Wonder', '123 Rabbit Hole Ln', 'Fullerton', 'CA', '92831', '714-555-0101', 'Female', 'Professor', 95000.00),
('222-00-2222', 'Robert', 'Builder', '456 Tool Ave', 'Brea', 'CA', '92821', '714-555-0202', 'Male', 'Associate Professor', 82000.00),
('333-00-3333', 'Charles', 'Xavier', '789 Mutant Dr', 'Irvine', 'CA', '92612', '714-555-0303', 'Male', 'Full Professor', 110000.00);

-- Update Department Chairpersons (Optional, not really needed)
-- UPDATE Departments SET ChairpersonSSN = '111-00-1111' WHERE DeptNumber = 'CS';
-- UPDATE Departments SET ChairpersonSSN = '333-00-3333' WHERE DeptNumber = 'MATH';

-- -----------------------------------------------------------------------------
-- Degrees (linking to professors, at least one per professor for example)
-- -----------------------------------------------------------------------------
INSERT INTO Degrees (ProfessorSSN, DegreeName, GrantingInstitution, YearGranted) VALUES
('111-00-1111', 'PhD in Computer Science', 'Wonderland University', 2010),
('111-00-1111', 'MS in Software Engineering', 'Looking Glass College', 2006),
('222-00-2222', 'PhD in Civil Engineering', 'Construction Institute', 2005),
('333-00-3333', 'PhD in Mathematics', 'Cerebro Academy', 1995),
('333-00-3333', 'MS in Applied Mathematics', 'X-Mansion University', 1992);

-- -----------------------------------------------------------------------------
-- Courses (4 required, creating 4)
-- OfferedByDeptNumber should match DeptNumber from your Departments table ('CS' or 'MATH')
-- -----------------------------------------------------------------------------
INSERT INTO Courses (CourseNumber, CourseTitle, Textbook, Units, OfferedByDeptNumber) VALUES
('CPSC120', 'Intro to Programming', 'Starting Out with Python', 3, 'CS'),
('CPSC131', 'Data Structures', 'Problem Solving with C++', 3, 'CS'),
('MATH250A', 'Calculus I', 'Calculus by Stewart', 4, 'MATH'),
('MATH250B', 'Calculus II', 'Calculus by Stewart', 4, 'MATH');

-- -----------------------------------------------------------------------------
-- Prerequisites (Example: CPSC131 requires CPSC120, MATH250B requires MATH250A)
-- -----------------------------------------------------------------------------
INSERT INTO Prerequisites (MainCourseNumber, PrereqCourseNumber) VALUES
('CPSC131', 'CPSC120'),
('MATH250B', 'MATH250A');

-- -----------------------------------------------------------------------------
-- Sections (6 required, creating 6)
-- TaughtByProfSSN should match a ProfSSN from your Professors table.
-- CourseNumber should match a CourseNumber from your Courses table.
-- -----------------------------------------------------------------------------
INSERT INTO Sections (CourseNumber, SectionNumber, Classroom, NumberOfSeats, MeetingDays, StartTime, EndTime, TaughtByProfSSN) VALUES
('CPSC120', '01', 'CS-101', 40, 'MWF', '09:00:00', '09:50:00', '111-00-1111'),
('CPSC120', '02', 'CS-102', 35, 'TR', '10:00:00', '11:15:00', '222-00-2222'),
('CPSC131', '01', 'CS-201', 30, 'MWF', '11:00:00', '11:50:00', '111-00-1111'),
('MATH250A', '01', 'MA-110', 50, 'MWF', '08:00:00', '09:15:00', '333-00-3333'),
('MATH250A', '02', 'MA-112', 50, 'TR', '13:00:00', '14:40:00', '333-00-3333'),
('MATH250B', '01', 'MA-210', 45, 'MWF', '10:00:00', '11:15:00', '333-00-3333');

-- -----------------------------------------------------------------------------
-- Students (8 required, creating 8)
-- MajorDeptNumber should match DeptNumber from your Departments table ('CS' or 'MATH')
-- -----------------------------------------------------------------------------
INSERT INTO Students (StudentID, StudFirstName, StudLastName, StudStreet, StudCity, StudState, StudZip, StudPhoneNumber, MajorDeptNumber) VALUES
('S00000001', 'Peter', 'Parker', '1 Spider Rd', 'New York', 'NY', '10001', '212-555-1001', 'CS'),
('S00000002', 'Mary', 'Jane', '2 Spider Rd', 'New York', 'NY', '10001', '212-555-1002', 'CS'),
('S00000003', 'Bruce', 'Wayne', '1 Batcave Ln', 'Gotham', 'NJ', '07001', '973-555-2001', 'MATH'),
('S00000004', 'Clark', 'Kent', '344 Clinton St', 'Metropolis', 'IL', '62960', '618-555-3001', 'CS'),
('S00000005', 'Diana', 'Prince', '1 Amazon Cir', 'Themyscira', 'DC', '20001', '202-555-4001', 'MATH'),
('S00000006', 'Barry', 'Allen', '1 Flash Plz', 'Central City', 'MO', '63001', '314-555-5001', 'CS'),
('S00000007', 'Hal', 'Jordan', '1 Green Lantern Wy', 'Coast City', 'CA', '93001', '805-555-6001', 'MATH'),
('S00000008', 'Arthur', 'Curry', '1 Atlantis Ave', 'Atlantis', 'MA', '01721', '508-555-7001', 'CS');

-- -----------------------------------------------------------------------------
-- StudentMinors (Example: Some students have minors)
-- StudentID from Students, MinorDeptNumber from Departments
-- -----------------------------------------------------------------------------
INSERT INTO StudentMinors (StudentID, MinorDeptNumber) VALUES
('S00000001', 'MATH'), -- Peter Parker (CS Major) minors in Math
('S00000003', 'CS');   -- Bruce Wayne (Math Major) minors in CS

-- -----------------------------------------------------------------------------
-- Enrollment (creating 20)
-- StudentID from Students, CourseNumber & SectionNumber from Sections
-- Grades can be 'A', 'B+', 'A-', 'IP' (In Progress), 'W' (Withdraw), etc.
-- -----------------------------------------------------------------------------
-- Student S00000001
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000001', 'CPSC120', '01', 'A'),
('S00000001', 'MATH250A', '01', 'A-');

-- Student S00000002
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000002', 'CPSC120', '01', 'B+'),
('S00000002', 'MATH250A', '01', 'B');

-- Student S00000003
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000003', 'MATH250A', '02', 'A'),
('S00000003', 'CPSC120', '02', 'A-');

-- Student S00000004
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000004', 'CPSC131', '01', 'IP'), -- In Progress
('S00000004', 'MATH250B', '01', 'B+');

-- Student S00000005
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000005', 'MATH250B', '01', 'A'),
('S00000005', 'CPSC120', '01', 'C');

-- Student S00000006
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000006', 'CPSC120', '02', 'A'),
('S00000006', 'CPSC131', '01', 'B');

-- Student S00000007
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000007', 'MATH250A', '01', 'A-'),
('S00000007', 'MATH250B', '01', 'IP');

-- Student S00000008
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000008', 'CPSC120', '02', 'B-'),
('S00000008', 'MATH250A', '02', 'C+');

-- Add 4 more enrollment records to reach 20
INSERT INTO Enrollment (StudentID, CourseNumber, SectionNumber, Grade) VALUES
('S00000001', 'CPSC131', '01', 'A'), -- Peter takes another course
('S00000002', 'CPSC120', '02', 'B'), -- Mary Jane in another section of CPSC120
('S00000003', 'MATH250B', '01', 'A'), -- Bruce takes Calc II
('S00000004', 'CPSC120', '01', 'W'); -- Clark withdraws from a course