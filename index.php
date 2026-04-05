<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battleship</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0a1628;
            color: #e0e8f0;
            min-height: 100vh;
        }

        header {
            background-color: #0d1f3c;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1e4d8c;
        }

        header h1 {
            font-size: 1.8rem;
            color: #4fa3e0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        nav a {
            color: #e0e8f0;
            text-decoration: none;
            margin-left: 20px;
            font-size: 1rem;
            padding: 8px 20px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        nav a.login {
            border: 1px solid #4fa3e0;
            color: #4fa3e0;
        }

        nav a.register {
            background-color: #4fa3e0;
            color: #0a1628;
            font-weight: bold;
        }

        nav a:hover {
            opacity: 0.85;
        }

        .hero {
            text-align: center;
            padding: 80px 20px 60px;
        }

        .hero h2 {
            font-size: 3rem;
            color: #4fa3e0;
            margin-bottom: 16px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .hero p {
            font-size: 1.2rem;
            color: #a0b8d0;
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.7;
        }

        .hero-buttons a {
            display: inline-block;
            padding: 14px 36px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: bold;
            margin: 8px;
            transition: opacity 0.2s;
        }

        .hero-buttons a.primary {
            background-color: #4fa3e0;
            color: #0a1628;
        }

        .hero-buttons a.secondary {
            border: 1px solid #4fa3e0;
            color: #4fa3e0;
        }

        .hero-buttons a:hover {
            opacity: 0.85;
        }

        .grid-preview {
            display: flex;
            justify-content: center;
            margin: 40px auto;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(10, 32px);
            grid-template-rows: repeat(10, 32px);
            gap: 2px;
        }

        .cell {
            background-color: #1a3a5c;
            border-radius: 2px;
        }

        .cell.hit  { background-color: #e05a4f; }
        .cell.miss { background-color: #4fa3e0; opacity: 0.4; }
        .cell.ship { background-color: #2d6a9f; }

        .how-to-play {
            max-width: 800px;
            margin: 0 auto 80px;
            padding: 0 20px;
        }

        .how-to-play h3 {
            text-align: center;
            font-size: 1.6rem;
            color: #4fa3e0;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 24px;
        }

        .step {
            background-color: #0d1f3c;
            border: 1px solid #1e4d8c;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
        }

        .step .number {
            font-size: 2rem;
            color: #4fa3e0;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .step p {
            color: #a0b8d0;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        footer {
            text-align: center;
            padding: 24px;
            border-top: 1px solid #1e4d8c;
            color: #4a6a8a;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<header>
    <h1>⚓ Battleship</h1>
    <nav>
        <a href="login.php" class="login">Login</a>
        <a href="register.php" class="register">Sign Up</a>
    </nav>
</header>

<div class="hero">
    <h2>Sink or Be Sunk</h2>
    <p>
        Challenge your friends to a classic game of Battleship. Place your fleet,
        take turns firing, and be the first to sink every enemy ship.
    </p>
    <div class="hero-buttons">
        <a href="register.php" class="primary">Play Now</a>
        <a href="login.php" class="secondary">I have an account</a>
    </div>
</div>

<div class="grid-preview">
    <div class="mini-grid">
        <?php
        $hits  = [12, 22, 45, 67];
        $misses = [3, 15, 38, 71, 84];
        $ships  = [11, 21, 31, 55, 65, 75];
        for ($i = 0; $i < 100; $i++) {
            if (in_array($i, $hits)) {
                echo '<div class="cell hit"></div>';
            } elseif (in_array($i, $misses)) {
                echo '<div class="cell miss"></div>';
            } elseif (in_array($i, $ships)) {
                echo '<div class="cell ship"></div>';
            } else {
                echo '<div class="cell"></div>';
            }
        }
        ?>
    </div>
</div>

<div class="how-to-play">
    <h3>How to Play</h3>
    <div class="steps">
        <div class="step">
            <div class="number">1</div>
            <p>Register an account and log in</p>
        </div>
        <div class="step">
            <div class="number">2</div>
            <p>Challenge another player by sending an invite</p>
        </div>
        <div class="step">
            <div class="number">3</div>
            <p>Place your 5 ships on the 10×10 grid</p>
        </div>
        <div class="step">
            <div class="number">4</div>
            <p>Take turns firing at your opponent's grid</p>
        </div>
        <div class="step">
            <div class="number">5</div>
            <p>Sink all enemy ships to win!</p>
        </div>
    </div>
</div>

<footer>
    <p>Battleship &mdash; A web-based multiplayer strategy game</p>
</footer>

</body>
</html>
