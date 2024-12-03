-- Inserting data into the Users table
INSERT INTO `Users` (Username, Password, TypeID, Admin) 
VALUES 
('john_doe', 'password123', 'USER001', FALSE),
('jane_smith', 'myPassword', 'USER002', FALSE),
('alice_brown', 'alicePass', 'USER003', FALSE);

SELECT * FROM `Users`;

-- Inserting data into the Profiles table
INSERT INTO `Profiles` (Lastname, Firstname, Users_TypeID) 
VALUES 
('Doe', 'John', 'USER001'),
('Smith', 'Jane', 'USER002'),
('Brown', 'Alice', 'USER003');

SELECT * FROM `Profiles`;

-- Inserting data into the Books table
INSERT INTO `Books` (ISBN, color, height, width, Profiles_profileID) 
VALUES 
(123456789, 1, 25, 15, 1),
(987654321, 2, 30, 20, 2),
(135792468, 3, 18, 12, 3);

SELECT * FROM `Books`;