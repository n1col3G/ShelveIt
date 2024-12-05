DROP TABLE IF EXISTS Friends;
DROP TABLE IF EXISTS FriendRequests;
DROP TABLE IF EXISTS Books;
DROP TABLE IF EXISTS Users;


-- Create the Users table
CREATE TABLE Users (
    UserID INT NOT NULL PRIMARY KEY,
    Email VARCHAR(50) NOT NULL,
    Password VARCHAR(50) NOT NULL,
    Admin BOOLEAN NOT NULL DEFAULT 0,
    Lastname VARCHAR(50),
    Firstname VARCHAR(50),
    lastEdit DATETIME,
    UNIQUE (Email)
) ENGINE=InnoDB;

-- Create the Books table
CREATE TABLE Books (
    bookID INT AUTO_INCREMENT PRIMARY KEY,
    bookColor VARCHAR(7),
    bookHeight INT,
    bookWidth INT,
    bookName VARCHAR(50),
    shelfID VARCHAR(10),
    imagePath VARCHAR(255),
    Users_UserID INT NOT NULL,
    bookOrder INT,
    FOREIGN KEY (Users_UserID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (Users_UserID)
) ENGINE=InnoDB;

-- Create the Friend Requests table
CREATE TABLE FriendRequests (
    requestID INT AUTO_INCREMENT PRIMARY KEY,
    requesterID INT NOT NULL,
    recipientID INT NOT NULL,
    requestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
    FOREIGN KEY (requesterID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (recipientID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create the Friends List table
CREATE TABLE Friends (
    friendID INT AUTO_INCREMENT PRIMARY KEY,
    requestID INT NOT NULL,
    userID1 INT NOT NULL,
    userID2 INT NOT NULL,
    friendshipDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requestID) REFERENCES FriendRequests(requestID) ON DELETE CASCADE,
    FOREIGN KEY (userID1) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (userID2) REFERENCES Users(UserID) ON DELETE CASCADE,
    userID_min INT AS (LEAST(userID1, userID2)) STORED,
    userID_max INT AS (GREATEST(userID1, userID2)) STORED,
    UNIQUE (userID_min, userID_max)
) ENGINE=InnoDB;


INSERT INTO `Users` (UserID, Email, Password, Admin, Lastname, Firstname) VALUES
(0, 'ngoulet@go.olemiss.edu', 'ngoulet', TRUE, 'Goulet', 'Nicole');