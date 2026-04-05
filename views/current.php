<?php
// Current Games view

$sql = "
    SELECT g.game_id, g.status,
           u1.username AS p1_name,
           u2.username AS p2_name
    FROM GAMES g
    JOIN USERS u1 ON g.player1_id = u1.user_id
    JOIN USERS u2 ON g.player2_id = u2.user_id
    WHERE (g.player1_id = ? OR g.player2_id = ?)
      AND g.status IN (1, 2)
    ORDER BY g.game_id DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($gid, $status, $p1_name, $p2_name);

$games = [];
while ($stmt->fetch()) {
    $games[] = ["id" => $gid, "status" => $status, "p1" => $p1_name, "p2" => $p2_name];
}
$stmt->close();
?>

<h3 class="section-title">Current Games</h3>
<table class="data-table">
    <tr><th>Game</th><th>Player 1</th><th>Player 2</th><th>Status</th><th></th></tr>
    <?php if (empty($games)): ?>
        <tr class="empty-row"><td colspan="5">No current games.</td></tr>
    <?php else: ?>
        <?php foreach ($games as $g):
            if ($g['status'] === 1) {
                $badge = "<span class='badge badge-placing'>Placing Ships</span>";
                $href  = "place_ships.php?game_id={$g['id']}";
            } else {
                $badge = "<span class='badge badge-active'>Active</span>";
                $href  = "game.php?game_id={$g['id']}";
            }
        ?>
        <tr>
            <td>#<?php echo $g['id']; ?></td>
            <td><?php echo htmlspecialchars($g['p1']); ?></td>
            <td><?php echo htmlspecialchars($g['p2']); ?></td>
            <td><?php echo $badge; ?></td>
            <td><a href="<?php echo $href; ?>" class="btn btn-secondary btn-sm">Open</a></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
