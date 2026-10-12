-- Sample data for a FRESH import of database.sql (all identities fictional).
-- Local demo only. Change or remove the demo account before deploying anywhere.
USE resume_registry_test;
INSERT INTO users (user_id, name, email, password) VALUES
(3, 'Demo User', 'demo@example.com', '$2y$12$m9NSt/B6alTnuh.h59n1LO81mNJAX1GHn9U/raStLtJgsOR8CIpRu');
INSERT INTO Profile (profile_id, user_id, first_name, last_name, email, headline, summary) VALUES
(12, 3, 'John', 'Smith', 'john.smith@example.com', 'Junior Software Developer', 'Computer science graduate interested in PHP, Python and SQL development.'),
(13, 3, 'Emily', 'Johnson', 'emily.johnson@example.com', 'Frontend Web Developer', 'Web developer focused on JavaScript, accessible interfaces and web applications.'),
(14, 3, 'Michael', 'Brown', 'michael.brown@example.com', 'Database Analyst', 'IT graduate interested in database design, SQL queries and data management.');
INSERT INTO Position (profile_id, `rank`, `year`, description) VALUES
(12, 1, 2025, 'Software development internship: assisted with PHP and MySQL features.'),
(13, 1, 2024, 'Frontend internship: developed HTML, CSS and JavaScript pages.'),
(14, 1, 2025, 'Database assistant: wrote SQL queries and supported data quality checks.');
INSERT INTO Education (profile_id, institution_id, `rank`, `year`) VALUES
(12, 1, 1, 2024),
(13, 4, 1, 2023),
(14, 5, 1, 2024);

