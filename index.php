<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ===============================
   CONFIG
================================ */
$BOT_TOKEN = "8208168301:AAGeYMb-HItoZ_6ldhaASFYq7rKqeEnqsgc";
$OWNER_ID  = 8137930541;

/* ===============================
   READ TELEGRAM UPDATE
================================ */
$update = json_decode(file_get_contents("php://input"), true);

if (!$update) {
  exit;
}

$chat_id = $update["message"]["chat"]["id"] ?? 0;
$text    = trim($update["message"]["text"] ?? "");

/* ===============================
   ALLOW ONLY OWNER
================================ */
if ($chat_id != $OWNER_ID) {
  sendMessage($chat_id, "❌ Unauthorized");
  exit;
}

/* ===============================
   DATABASE (TaskMint – InfinityFree)
   👉 REPLACE with YOUR real DB values
================================ */
$conn = mysqli_connect(
  "sqlXXX.infinityfree.com",
  "if0_xxxxx",
  "PASSWORD",
  "if0_xxxxx_taskmint"
);

if (!$conn) {
  sendMessage($chat_id, "❌ DB Error");
  exit;
}

/* ===============================
   COMMANDS
================================ */
if ($text === "/start") {

  sendMessage(
    $chat_id,
    "✅ TaskMint Bot Connected\n\nUse /stats"
  );

} elseif ($text === "/stats") {

  $users = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM users")
  )["total"] ?? 0;

  $balance = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT SUM(balance) total FROM users")
  )["total"] ?? 0;

  sendMessage(
    $chat_id,
    "📊 TaskMint Stats\n\n👥 Users: $users\n💰 Balance: ₹$balance"
  );

}

/* ===============================
   SEND MESSAGE FUNCTION
================================ */
function sendMessage($chat_id, $message) {
  global $BOT_TOKEN;

  file_get_contents(
    "https://api.telegram.org/bot$BOT_TOKEN/sendMessage?" .
    http_build_query([
      "chat_id" => $chat_id,
      "text" => $message
    ])
  );
}
