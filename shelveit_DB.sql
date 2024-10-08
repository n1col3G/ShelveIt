-- Creating the Users table
CREATE TABLE Users (
    Username VARCHAR(50),
    Password VARCHAR(50),
    TypeID VARCHAR(50) PRIMARY KEY,
    Admin BOOLEAN
);

-- Creating the Profiles table
CREATE TABLE Profiles (
    Lastname VARCHAR(50),
    Firstname VARCHAR(50),
    profileID INT AUTO_INCREMENT PRIMARY KEY,
    Users_TypeID VARCHAR(50),
    FOREIGN KEY (Users_TypeID) REFERENCES Users(TypeID)
);

-- Creating the Books table
CREATE TABLE Books (
    bookID INT AUTO_INCREMENT PRIMARY KEY,
    ISBN INT,
    color INT,
    height INT,
    width INT,
    Profiles_profileID INT,
    FOREIGN KEY (Profiles_profileID) REFERENCES Profiles(profileID)
);
