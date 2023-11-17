-- create the databases
CREATE DATABASE IF NOT EXISTS todo_list_api;

-- create the users for each database
CREATE USER 'root'@'%' IDENTIFIED BY 'password';
GRANT CREATE, ALTER, INDEX, LOCK TABLES, REFERENCES, UPDATE, DELETE, DROP, SELECT, INSERT ON `todo_list_api`.* TO 'root'@'%';

FLUSH PRIVILEGES;
