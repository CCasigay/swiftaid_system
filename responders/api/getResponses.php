<?php
require '../responders/config/config.php';

$stmt = $pdo->query("SELECT r.id, u.name, r.location, r.status FROM responses r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending'");
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($responses);
?>
