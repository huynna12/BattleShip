# Overview of the game
Battleship is a two-player turn-based game played on a 10x10 grid. Each player has five ships of different lengths and tries to sink the opponent’s ships by guessing their locations. Players take turns choosing grid coordinates to fire at, and each shot is recorded as either a hit or a miss. The game ends when one player’s entire fleet is sunk.

In this web application, users can register, log in, challenge other users, accept challenges, and view current or completed games. The game is not real-time; each player can log in and take their turn whenever they want. For Homework 2, the application includes a draft Battleship board where players can click a cell to make a move, and hits and misses are stored in the database. Full ship placement and winner detection will be added in the next assignment.
# Overview of the files
- connect_db.php
- login.php
- register.php
- logout.php

- home.php
    Main menu after logging in.
    Contains four sections:
    Invite Sent – shows users you challenged and pending invites.
    Invite Received – shows challenges from other users.
    Current Games – shows active or placing games.
    History Games – shows completed games and winners.
    This file uses ?view=sent, ?view=received, etc., to switch between sections.

- send_invite.php
    Inserts a new row into the GAMES table when you challenge another user.
    Sets game status to 0 (pending).

- accept_invite.php
    Allows the second player to accept a challenge.
    Updates GAMES.status to 1 (placing ships).

- game.php
    Displays a 60x60 Battleship grid (A–J and 1–10).
    Shows X for a miss and O for a hit.
    Each empty cell is a clickable button that submits a move.
    This is the required “draft game board” for Homework 2.

- make_move.php
    Accepts a move from game.php and inserts a row into MOVES.
    Redirects back to the game board so the new X/O appears.

# SQL statements needed for setting up 
## Create tables 
-- USERS TABLE
CREATE TABLE USERS (
    user_id   INT AUTO_INCREMENT PRIMARY KEY,
    username  CHAR(25) NOT NULL UNIQUE,
    password  CHAR(64) NOT NULL
);

-- GAMES TABLE
CREATE TABLE GAMES (
    game_id     INT AUTO_INCREMENT PRIMARY KEY,
    player1_id  INT NOT NULL,
    player2_id  INT NOT NULL,
    -- 0 = pending, 1 = placing, 2 = active, 3 = finish
    status      TINYINT NOT NULL,
    cur_turn    INT NULL,
    winner_id   INT NULL,
    CHECK (status IN (0,1,2,3))
);

-- SHIP_CELLS TABLE
CREATE TABLE SHIP_CELLS (
    game_id   INT,
    player_id INT,
    row_num   TINYINT,
    col_char  CHAR(1),
    PRIMARY KEY (game_id, player_id, row_num, col_char)
);

-- MOVES TABLE
CREATE TABLE MOVES (
    move_id   INT AUTO_INCREMENT PRIMARY KEY,
    game_id   INT NOT NULL,
    player_id INT NOT NULL,
    row_num   TINYINT NOT NULL,
    col_char  CHAR(1) NOT NULL,
    result    TINYINT CHECK (result IN (0,1)),
    UNIQUE (game_id, player_id, row_num, col_char)
);

## Add some data 
-- Create users for testing
INSERT INTO USERS (username, password) VALUES
  ('alice', SHA2('alicepass', 256)),
  ('bob',   SHA2('bobpass',   256)),
  ('carol', SHA2('carolpass', 256)),
  ('dave',  SHA2('davepass', 256));

-- Expected user_ids:
-- alice -> 1
-- bob   -> 2
-- carol -> 3
-- dave  -> 4

INSERT INTO GAMES (player1_id, player2_id, status, cur_turn, winner_id) VALUES
  (1, 2, 0, NULL, NULL),  -- Game 1: alice -> bob (pending)
  (2, 1, 0, NULL, NULL),  -- Game 2: bob -> alice (pending)
  (1, 3, 1,    1, NULL),  -- Game 3: alice vs carol (placing)
  (2, 3, 2,    2, NULL),  -- Game 4: bob vs carol (active)
  (1, 2, 3, NULL,    1),  -- Game 5: finished, alice won
  (3, 4, 3, NULL,    4);  -- Game 6: finished, dave won

## Grant access (optional, for a dedicated web user)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON battleship.* TO 'your_web_user'@'%';

# Notes: 
- I do not have the place ships part yet. 
- Most of the links on the page are functional, you may check them. 
- 
