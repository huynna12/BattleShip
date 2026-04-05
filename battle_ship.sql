-- Drop tables in dependency order (children first)
DROP TABLE IF EXISTS SHIP_CELLS;
DROP TABLE IF EXISTS SHIPS;
DROP TABLE IF EXISTS MOVES;
DROP TABLE IF EXISTS GAMES;
DROP TABLE IF EXISTS USERS;

------------------------------------------------------------
-- USERS TABLE
------------------------------------------------------------
CREATE TABLE USERS (
    user_id   INT AUTO_INCREMENT PRIMARY KEY,
    username  CHAR(25) NOT NULL UNIQUE,
    password  CHAR(64) NOT NULL
);

------------------------------------------------------------
-- GAMES TABLE
-- 0 = pending, 1 = placing, 2 = active, 3 = finished
------------------------------------------------------------
CREATE TABLE GAMES (n
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
);

------------------------------------------------------------
-- MOVES TABLE
-- result: 0 = miss, 1 = hit
------------------------------------------------------------
CREATE TABLE MOVES (
    move_id   INT AUTO_INCREMENT PRIMARY KEY,
    game_id   INT NOT NULL,
    player_id INT NOT NULL,
    row_num   TINYINT NOT NULL,
    col_char  CHAR(1) NOT NULL,
    result    TINYINT NOT NULL CHECK (result IN (0, 1)),

    -- Each player can fire at a given cell at most once per game
    UNIQUE (game_id, player_id, row_num, col_char),

    FOREIGN KEY (game_id)   REFERENCES GAMES(game_id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES USERS(user_id)
);

------------------------------------------------------------
-- SHIPS TABLE
-- Each row is one ship for a player in a specific game
------------------------------------------------------------
CREATE TABLE SHIPS (
    ship_id   INT AUTO_INCREMENT PRIMARY KEY,
    game_id   INT NOT NULL,
    player_id INT NOT NULL,
    ship_num  TINYINT NOT NULL,      -- identifier 1..5 per player
    length    TINYINT NOT NULL,      -- ship size: 2, 3, 4, or 5
    is_sunk   TINYINT(1) NOT NULL DEFAULT 0,

    FOREIGN KEY (game_id)   REFERENCES GAMES(game_id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES USERS(user_id)
    -- Do NOT cascade on delete from USERS so that finished game records
    -- are preserved even if a user account is removed.
);

------------------------------------------------------------
-- SHIP_CELLS TABLE
-- Each row is one occupied cell of a ship
------------------------------------------------------------
CREATE TABLE SHIP_CELLS (
    ship_id  INT     NOT NULL,
    row_num  TINYINT NOT NULL,
    col_char CHAR(1) NOT NULL,

    PRIMARY KEY (ship_id, row_num, col_char),

    FOREIGN KEY (ship_id)
        REFERENCES SHIPS(ship_id)
        ON DELETE CASCADE
);

------------------------------------------------------------
-- Test data
------------------------------------------------------------
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

-- Sample games
INSERT INTO GAMES (player1_id, player2_id, status, cur_turn, winner_id) VALUES
  (1, 2, 0, NULL, NULL),  -- Game 1: alice -> bob (pending)
  (2, 1, 0, NULL, NULL),  -- Game 2: bob -> alice (pending)
  (1, 3, 1,    1, NULL),  -- Game 3: alice vs carol (placing, alice's turn)
  (2, 3, 2,    2, NULL),  -- Game 4: bob vs carol (active, bob's turn)
  (1, 2, 3, NULL,    1),  -- Game 5: finished, alice won
  (3, 4, 3, NULL,    4);  -- Game 6: finished, dave won

-- To grant a dedicated web user access (optional):
-- GRANT SELECT, INSERT, UPDATE, DELETE ON battleship.* TO 'your_web_user'@'%';
