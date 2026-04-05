<?php
// Load .env file if it exists (for local development)
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}
 
$server   = $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?: 'localhost';
$username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
$dbname   = $_ENV['DB_NAME']     ?? getenv('DB_NAME')     ?: 'battleship';
 
$conn = new mysqli($server, $username, $password, $dbname);
 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-create tables if they don't exist yet
$conn->query("CREATE TABLE IF NOT EXISTS USERS (
    user_id   INT AUTO_INCREMENT PRIMARY KEY,
    username  CHAR(25) NOT NULL UNIQUE,
    password  CHAR(64) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS GAMES (
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
)");

$conn->query("CREATE TABLE IF NOT EXISTS MOVES (
    move_id   INT AUTO_INCREMENT PRIMARY KEY,
    game_id   INT NOT NULL,
    player_id INT NOT NULL,
    row_num   TINYINT NOT NULL,
    col_char  CHAR(1) NOT NULL,
    result    TINYINT NOT NULL CHECK (result IN (0, 1)),
    UNIQUE (game_id, player_id, row_num, col_char),
    FOREIGN KEY (game_id)   REFERENCES GAMES(game_id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES USERS(user_id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS SHIPS (
    ship_id   INT AUTO_INCREMENT PRIMARY KEY,
    game_id   INT NOT NULL,
    player_id INT NOT NULL,
    ship_num  TINYINT NOT NULL,
    length    TINYINT NOT NULL,
    is_sunk   TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (game_id)   REFERENCES GAMES(game_id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES USERS(user_id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS SHIP_CELLS (
    ship_id  INT     NOT NULL,
    row_num  TINYINT NOT NULL,
    col_char CHAR(1) NOT NULL,
    PRIMARY KEY (ship_id, row_num, col_char),
    FOREIGN KEY (ship_id) REFERENCES SHIPS(ship_id) ON DELETE CASCADE
)");