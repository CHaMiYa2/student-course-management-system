-- Student Course Management System Database Setup
-- This script creates the database and all required tables

-- Create database
CREATE DATABASE IF NOT EXISTS student_course_management;
USE student_course_management;

-- Drop existing tables if they exist (in correct order due to foreign keys)
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS instructors;

-- Create students table
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    date_of_birth DATE,
    enrollment_date DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create instructors table
CREATE TABLE instructors (
    instructor_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    department VARCHAR(100),
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(10) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    credits INT NOT NULL,
    instructor_id INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id) ON DELETE SET NULL
);

-- Create enrollments table
CREATE TABLE enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE DEFAULT CURRENT_DATE,
    grade CHAR(2) DEFAULT NULL,
    status ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- Insert sample instructors
INSERT INTO instructors (first_name, last_name, email, department, hire_date) VALUES
('Dr. Sarah', 'Johnson', 'sarah.johnson@university.edu', 'Computer Science', '2020-01-15'),
('Prof. Michael', 'Chen', 'michael.chen@university.edu', 'Mathematics', '2018-03-20'),
('Dr. Emily', 'Davis', 'emily.davis@university.edu', 'Physics', '2019-07-10'),
('Prof. Robert', 'Wilson', 'robert.wilson@university.edu', 'Engineering', '2017-11-05'),
('Dr. Lisa', 'Brown', 'lisa.brown@university.edu', 'Chemistry', '2021-02-28');

-- Insert sample students
INSERT INTO students (first_name, last_name, email, phone, date_of_birth, enrollment_date) VALUES
('John', 'Smith', 'john.smith@student.edu', '555-0101', '2000-05-15', '2023-09-01'),
('Emma', 'Johnson', 'emma.johnson@student.edu', '555-0102', '2001-03-22', '2023-09-01'),
('Michael', 'Williams', 'michael.williams@student.edu', '555-0103', '1999-11-08', '2023-09-01'),
('Sarah', 'Brown', 'sarah.brown@student.edu', '555-0104', '2000-08-14', '2023-09-01'),
('David', 'Jones', 'david.jones@student.edu', '555-0105', '2001-01-30', '2023-09-01'),
('Lisa', 'Garcia', 'lisa.garcia@student.edu', '555-0106', '2000-12-05', '2023-09-01'),
('James', 'Miller', 'james.miller@student.edu', '555-0107', '1999-07-18', '2023-09-01'),
('Jennifer', 'Davis', 'jennifer.davis@student.edu', '555-0108', '2001-04-12', '2023-09-01');

-- Insert sample courses
INSERT INTO courses (course_code, course_name, credits, instructor_id, description) VALUES
('CS101', 'Introduction to Computer Science', 3, 1, 'Fundamental concepts of computer science and programming'),
('MATH201', 'Calculus I', 4, 2, 'Differential calculus and its applications'),
('PHYS101', 'Physics I', 4, 3, 'Mechanics, thermodynamics, and wave phenomena'),
('ENG201', 'Engineering Design', 3, 4, 'Principles of engineering design and problem solving'),
('CHEM101', 'General Chemistry', 4, 5, 'Basic principles of chemistry and laboratory techniques'),
('CS201', 'Data Structures', 3, 1, 'Advanced programming concepts and data structures'),
('MATH202', 'Calculus II', 4, 2, 'Integral calculus and series'),
('PHYS102', 'Physics II', 4, 3, 'Electricity, magnetism, and optics');

-- Insert sample enrollments
INSERT INTO enrollments (student_id, course_id, enrollment_date, grade, status) VALUES
(1, 1, '2023-09-01', 'A', 'completed'),
(1, 2, '2023-09-01', 'B+', 'completed'),
(2, 1, '2023-09-01', 'A-', 'completed'),
(2, 3, '2023-09-01', 'B', 'completed'),
(3, 2, '2023-09-01', 'C+', 'completed'),
(3, 4, '2023-09-01', 'A', 'completed'),
(4, 1, '2023-09-01', 'B+', 'completed'),
(4, 5, '2023-09-01', 'A-', 'completed'),
(5, 3, '2023-09-01', 'B', 'completed'),
(5, 6, '2023-09-01', 'A', 'completed'),
(6, 2, '2023-09-01', 'C', 'completed'),
(6, 7, '2023-09-01', 'B+', 'completed'),
(7, 4, '2023-09-01', 'A-', 'completed'),
(7, 8, '2023-09-01', 'B', 'completed'),
(8, 5, '2023-09-01', 'A', 'completed'),
(8, 1, '2023-09-01', 'B+', 'completed');

-- Create indexes for better performance
CREATE INDEX idx_student_email ON students(email);
CREATE INDEX idx_instructor_email ON instructors(email);
CREATE INDEX idx_course_code ON courses(course_code);
CREATE INDEX idx_enrollment_student ON enrollments(student_id);
CREATE INDEX idx_enrollment_course ON enrollments(course_id);
CREATE INDEX idx_enrollment_status ON enrollments(status);
