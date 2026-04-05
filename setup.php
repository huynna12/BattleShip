<?php
require_once "connect_db.php";

$statements = [
    "DROP TABLE IF EXISTS SHIP_CELLS",
    "DROP TABLE IF EXISTS SHIPS",
    "DROP TABLE IF EXISTS MOVES",
    "DROP TABLE IF EXISTS GAMES",
    "DROP TABLE IF EXISTS USERS",

    "CREATE TABLE USERS (
        user_id   INT AUTO_INCREMENT PRIMARY KEY,
        username  CHAR(25) NOT NULL UNIQUE,
        password  CHAR(64) NOT NULL
    )",

    "CREATE TABLE GAMES (
        game_id     INT AUTO_INCREMENT PRIMARY KEY,
        player1_id  INT NOT NULL,
        player2_id  INT NOT NULL,
        status      TINYINT NOT NULL,
        cur_turn    INT NULL,
        winner_id   INT NULL,
        CHECK (status IN (0, 1, 2, 3)),
        FOREIGN KEY (player1_id) REFERENCES USERS(user_id),
        FOREIGN KEY (player2_id) REFERENCES USERS(user_id),
        FOREIGN KEY (cur_turn)   REFERENCES USERS(user_id),
        FOREIGN KEY (winner_id)  REFERENCES USERS(user_id)
    )",

    "CREATE TABLE MOVES (
        move_id   INT AUTO_INCREMENT PRIMARY KEY,
        game_id   INT NOT NULL,
        player_id INT NOT NULL,
        row_num   TINYINT NOT NULL,
        col_char  CHAR(1) NOT NULL,
        result    TINYINT NOT NULL CHECK (result IN (0, 1)),
        UNIQUE (game_id, player_id, row_num, col_char),
        FOREIGN KEY (game_id)   REFERENCES GAMES(game_id) ON DELETE CASCADE,
        FOREIGN KEY (player_id) REFERENCES USERS(user_id)
    )",

    "CREATE TABLE SHIPS (
        ship_id   INT AUTO_INCREMENT PRIMARY KEY,
        game_id   INT NOT NULL,
        player_id INT NOT NULL,
        ship_num  TINYINT NOT NULL,
        length    TINYINT NOT NULL,
        is_sunk   TINYINT(1) NOT NULL DEFAULT 0,
        FOREIGN KEY (game_id)   REFERENCES GAMES(game_id) ON DELETE CASCADE,
        FOREIGN KEY (player_id) REFERENCES USERS(user_id)
    )",

    "CREATE TABLE SHIP_CELLS (
        ship_id  INT     NOT NULL,
        row_num  TINYINT NOT NULL,
        col_char CHAR(1) NOT NULL,
        PRIMARY KEY (ship_id, row_num, col_char),
        FOREIGN KEY (ship_id) REFERENCES SHIPS(ship_id) ON DELETE CASCADE
    )",

    "INSERT INTO USERS (username, password) VALUES
        ('alice', SHA2('alicepass', 256)),
        ('bob',   SHA2('bobpass',   256)),
        ('carol', SHA2('carolpass', 256)),
        ('dave',  SHA2('davepass',  256))",

    "INSERT INTO GAMES (player1_id, player2_id, status, cur_turn, winner_id) VALUES
        (1, 2, 0, NULL, NULL),
        (2, 1, 0, NULL, NULL),
        (1, 3, 1,    1, NULL),
        (2, 3, 2,    2, NULL),
        (1, 2, 3, NULL,    1),
        (3, 4, 3, NULL,    4)",
];

$errors = [];
foreach ($statements as $sql) {
    if (!$conn->query($sql)) {
        $errors[] = $conn->error;
    }
}

if (empty($errors)) {
    echo "<p style='color:green;'>Database setup complete. <a href='login.php'>Go to login</a></p>";
} else {
    echo "<p style='color:red;'>Errors:</p><ul>";
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e) . "</li>";
    }
    echo "</ul>";
}
