# Battleship

A two-player turn-based Battleship game built with PHP and MySQL. Players register, log in, challenge each other, place ships, and take turns firing at the opponent's grid. The game is asynchronous — each player can log in and take their turn whenever they want.

## Features

- User registration and login
- Challenge other registered users
- Accept or ignore incoming invites
- 10x10 interactive game board (A–J, 1–10)
- Hits and misses persisted in the database
- Game history with winner tracking

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML/CSS (plain, no framework)

## Setup

### 1. Configure the database

Copy `.env.example` to `.env` and fill in your MySQL credentials:

```
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_NAME=battleship
```

### 2. Initialize the database

Run `battle_ship.sql` in your MySQL client to create the tables and load test data:

```bash
mysql -u root -p battleship < battle_ship.sql
```

### 3. Serve the app

Use a local PHP server or any PHP host (e.g. Railway, InfinityFree):

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/login.php`.

## Test Accounts

| Username | Password    |
|----------|-------------|
| alice    | alicepass   |
| bob      | bobpass     |
| carol    | carolpass   |
| dave     | davepass    |

## File Overview

| File | Purpose |
|------|---------|
| `connect_db.php` | Database connection, reads from `.env` |
| `login.php` | Login form and authentication |
| `register.php` | New user registration |
| `logout.php` | Ends the session |
| `home.php` | Dashboard — invites sent/received, current games, history |
| `send_invite.php` | Challenge another user (creates a pending game) |
| `accept_invite.php` | Accept a challenge (moves game to placing status) |
| `place_ships.php` | Ship placement phase |
| `game.php` | Interactive game board — click a cell to fire |
| `make_move.php` | Records a move and redirects back to the board |
| `views/sent.php` | Partial: invites you sent |
| `views/received.php` | Partial: invites you received |
| `views/current.php` | Partial: active games |
| `views/history.php` | Partial: completed games |

## Game Status Codes

| Status | Meaning |
|--------|---------|
| 0 | Pending (invite not yet accepted) |
| 1 | Placing ships |
| 2 | Active (players taking turns) |
| 3 | Finished |
