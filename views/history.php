<?php
// History view

$sql = "SELECT g.game_id, g.winner_id,
               u1.username AS p1_name,
               u2.username AS p2_name
        FROM GAMES g
        JOIN USERS u1 ON g.player1_id = u1.user_id
        JOIN USERS u2 ON g.player2_id = u2.user_id
        WHERE (g.player1_id = ? OR g.player2_id = ?)
          AND g.status = 3
        ORDER BY g.game_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($gid, $winner_id, $p1_name, $p2_name);

$games = [];
while ($stmt->fetch()) {
    $games[] = ["id" => $gid, "winner_id" => $winner_id, "p1" => $p1_name, "p2" => $p2_name];
}
$stmt->close();
?>

<h3 class="section-title">Game History</h3>
<table class="data-table">
    <tr><th>Game</th><th>Player 1</th><th>Player 2</th><th>Result</th></tr>
    <?php if (empty($games)): ?>
        <tr class="empty-row"><td colspan="4">No finished games.</td></tr>
    <?php else: ?>
        <?php foreach ($games as $g):
            if ($g['winner_id'] === null) {
                $result = "<span class='badge'>No winner</span>";
            } elseif ($g['winner_id'] == $user_id) {
                $result = "<span class='badge badge-won'>You won</span>";
            } else {
                $result = "<span class='badge badge-lost'>You lost</span>";
            }
        ?>
        <tr>
            <td>#<?php echo $g['id']; ?></td>
            <td><?php echo htmlspecialchars($g['p1']); ?></td>
            <td><?php echo htmlspecialchars($g['p2']); ?></td>
            <td><?php echo $result; ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
