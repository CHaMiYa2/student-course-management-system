# Student Course Management System

A complete web-based Student Course Management System built with PHP, MySQL, HTML, CSS, and JavaScript. This system provides full CRUD operations for managing students, instructors, courses, and enrollments with a normalized database structure.

## Features

### Core Functionality
- **Dashboard**: Overview with statistics and recent enrollments
- **Students Management**: Add, edit, delete, and view student details with enrollment history
- **Instructors Management**: Manage instructor information and assigned courses
- **Courses Management**: Create and manage courses with instructor assignments
- **Enrollments Management**: Handle student course enrollments with grades and status tracking

### Technical Features
- **Database**: MySQL with 3NF normalization
- **Security**: CSRF protection, input validation, prepared statements
- **Responsive Design**: Mobile-friendly interface using CSS Grid and Flexbox
- **Search & Filter**: Real-time search across all modules
- **Export**: CSV export functionality for all data tables
- **Validation**: Client-side and server-side form validation
- **Error Handling**: Comprehensive error handling and user feedback

## Database Schema

### Tables
1. **students** - Student information (ID, name, email, phone, DOB, enrollment date)
2. **instructors** - Instructor information (ID, name, email, department, hire date)
3. **courses** - Course information (ID, code, name, credits, instructor, description)
4. **enrollments** - Student-course relationships (ID, student, course, date, status, grade)

### Relationships
- Students → Enrollments (1:many)
- Courses → Enrollments (1:many)
- Instructors → Courses (1:many)
- Unique constraint on student-course combinations

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   git clone <repository-url>
   cd student-course-management
   ```

2. **Database Setup**
   - Create a MySQL database named `student_course_management`
   - Import the database schema:
   ```bash
   mysql -u root -p student_course_management < setup_database.sql
   ```

3. **Configuration**
   - Edit `config/database.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'student_course_management');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Web Server Configuration**
   - Point your web server to the `student-course-management` directory
   - Ensure PHP has write permissions for sessions

5. **Access the Application**
   - Navigate to `http://localhost/student-course-management/`
   - The system will be ready to use with sample data

## File Structure

```
student-course-management/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── header.php            # Common header with navigation
│   └── footer.php            # Common footer
├── css/
│   └── style.css             # Main stylesheet
├── js/
│   └── script.js             # JavaScript functionality
├── pages/
│   ├── students/             # Student management
│   │   ├── index.php         # List students
│   │   ├── create.php        # Add student
│   │   ├── edit.php          # Edit student
│   │   ├── delete.php        # Delete student
│   │   └── view.php          # View student details
│   ├── instructors/          # Instructor management
│   │   ├── index.php         # List instructors
│   │   ├── create.php        # Add instructor
│   │   ├── edit.php          # Edit instructor
│   │   ├── delete.php        # Delete instructor
│   │   └── view.php          # View instructor details
│   ├── courses/              # Course management
│   │   ├── index.php         # List courses
│   │   ├── create.php        # Add course
│   │   ├── edit.php          # Edit course
│   │   ├── delete.php        # Delete course
│   │   └── view.php          # View course details
│   └── enrollments/          # Enrollment management
│       ├── index.php         # List enrollments
│       ├── create.php        # Add enrollment
│       ├── edit.php          # Edit enrollment
│       └── delete.php        # Delete enrollment
├── setup_database.sql        # Database schema and sample data
├── index.php                 # Main dashboard
└── README.md                 # This file
```

## Usage

### Dashboard
- View system statistics (total students, instructors, courses, enrollments)
- Access quick actions for common tasks
- View recent enrollments

### Students Module
- **List**: View all students with search functionality
- **Add**: Create new student records with validation
- **Edit**: Update student information
- **Delete**: Remove students (with enrollment cascade)
- **View**: Detailed student profile with enrollment history

### Instructors Module
- **List**: View all instructors with course assignments
- **Add**: Create new instructor records
- **Edit**: Update instructor information
- **Delete**: Remove instructors (courses become unassigned)
- **View**: Detailed instructor profile with assigned courses

### Courses Module
- **List**: View all courses with instructor and enrollment info
- **Add**: Create new courses with instructor assignment
- **Edit**: Update course information
- **Delete**: Remove courses (with enrollment cascade)
- **View**: Detailed course information with enrolled students

### Enrollments Module
- **List**: View all enrollments with student and course details
- **Add**: Create new enrollments with validation
- **Edit**: Update enrollment status and grades
- **Delete**: Remove enrollments

## Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Prevention**: HTML escaping for all output
- **Error Handling**: Secure error messages without exposing system details

## Responsive Design

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

Uses modern CSS features:
- CSS Grid for layouts
- Flexbox for components
- Media queries for breakpoints
- Modern color scheme and typography

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Customization

### Styling
- Modify `css/style.css` for visual changes
- Update color scheme in CSS variables
- Adjust responsive breakpoints

### Functionality
- Add new features in respective module directories
- Extend database schema as needed
- Modify validation rules in form processing

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Check database exists

2. **Permission Errors**
   - Ensure web server has read access to all files
   - Check PHP has write permissions for sessions

3. **Page Not Found**
   - Verify web server configuration
   - Check file paths and permissions

### Error Logging
- PHP errors are logged to the server's error log
- Database errors are logged with `error_log()`
- User-friendly error messages are displayed

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For support or questions:
- Check the troubleshooting section
- Review the code comments
- Create an issue in the repository

---

**Version**: 1.0.0  
**Last Updated**: December 2024  
**Author**: Student Course Management System Team
