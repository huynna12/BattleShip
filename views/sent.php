<?php
// Invites Sent view

/* ---------------------------
   1. Challenge a user
   --------------------------- */
$sql  = "SELECT user_id, username FROM USERS WHERE user_id <> ? ORDER BY username";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($opp_id, $opp_name);

$users = [];
while ($stmt->fetch()) {
    $users[] = ["id" => $opp_id, "name" => $opp_name];
}
$stmt->close();
?>

<h3 class="section-title">Challenge a Player</h3>
<table class="data-table">
    <tr><th>Username</th><th>Action</th></tr>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?php echo htmlspecialchars($u['name']); ?></td>
        <td>
            <form method="post" action="send_invite.php">
                <input type="hidden" name="opponent_id" value="<?php echo $u['id']; ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Challenge</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php
/* ---------------------------
   2. Pending invites sent
   --------------------------- */
$sql  = "SELECT g.game_id, u.username
         FROM GAMES g
         JOIN USERS u ON g.player2_id = u.user_id
         WHERE g.player1_id = ? AND g.status = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($gid, $opp_name);

$pending = [];
while ($stmt->fetch()) {
    $pending[] = ["id" => $gid, "name" => $opp_name];
}
$stmt->close();
?>

<h3 class="section-title">Pending Invites</h3>
<table class="data-table">
    <tr><th>Game</th><th>Opponent</th></tr>
    <?php if (empty($pending)): ?>
        <tr class="empty-row"><td colspan="2">No pending invites.</td></tr>
    <?php else: ?>
        <?php foreach ($pending as $p): ?>
        <tr>
            <td>#<?php echo $p['id']; ?></td>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
