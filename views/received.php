<?php
// Invites Received view

$sql  = "SELECT g.game_id, u.username
         FROM GAMES g
         JOIN USERS u ON g.player1_id = u.user_id
         WHERE g.player2_id = ? AND g.status = 0
         ORDER BY g.game_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($gid, $challenger);

$invites = [];
while ($stmt->fetch()) {
    $invites[] = ["id" => $gid, "challenger" => $challenger];
}
$stmt->close();
?>

<h3 class="section-title">Invites Received</h3>
<table class="data-table">
    <tr><th>Game</th><th>From</th><th>Action</th></tr>
    <?php if (empty($invites)): ?>
        <tr class="empty-row"><td colspan="3">No invites received.</td></tr>
    <?php else: ?>
        <?php foreach ($invites as $inv): ?>
        <tr>
            <td>#<?php echo $inv['id']; ?></td>
            <td><?php echo htmlspecialchars($inv['challenger']); ?></td>
            <td>
                <form method="post" action="accept_invite.php">
                    <input type="hidden" name="game_id" value="<?php echo $inv['id']; ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Accept</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
