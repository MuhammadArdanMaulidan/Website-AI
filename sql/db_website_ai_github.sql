-- Database: `db_website_ai_github`

CREATE DATABASE IF NOT EXISTS `db_website_ai_github` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_website_ai_github`;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `employees`
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `hire_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `applicants`
CREATE TABLE `applicants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `github` varchar(100) DEFAULT NULL,
  `experience` text NOT NULL,
  `skills` text NOT NULL,
  `education` text NOT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `application_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','reviewed','interviewed','hired','rejected') NOT NULL DEFAULT 'new',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `github_api_key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for testing
INSERT INTO `users` (`name`, `email`, `password`) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password is "password"

INSERT INTO `employees` (`name`, `email`, `phone`, `position`, `department`, `hire_date`, `status`) VALUES
('John Doe', 'john@example.com', '1234567890', 'Software Developer', 'IT', '2020-01-15', 'active'),
('Jane Smith', 'jane@example.com', '0987654321', 'HR Manager', 'HR', '2019-05-20', 'active'),
('Mike Johnson', 'mike@example.com', '5551234567', 'Marketing Specialist', 'Marketing', '2021-03-10', 'active');

INSERT INTO `applicants` (`name`, `email`, `phone`, `linkedin`, `github`, `experience`, `skills`, `education`, `cv_path`, `status`) VALUES
('Alice Brown', 'alice@example.com', '1112223333', 'linkedin.com/in/alice', 'alicebrown', '5 years as web developer', 'PHP, JavaScript, HTML, CSS', 'BS Computer Science, State University', 'uploads/cv/alice_cv.pdf', 'new'),
('Bob Wilson', 'bob@example.com', '4445556666', 'linkedin.com/in/bob', 'bobwilson', '3 years as UI designer', 'Figma, Adobe XD, CSS', 'BA Design, Art College', 'uploads/cv/bob_cv.pdf', 'reviewed');