<?php
// ALWAYS return 200 OK
http_response_code(200);

// Hide errors from Telegram
ini_set('display_errors', 0);
error_reporting(0);

/* ===============================
   CONFIG
================================ */
$BOT_TOKEN = "8208168301:AAGeYMb-HItoZ_6ldhaASFYq7rKqeEnqsgc";
$OWNER_ID  = 8137930541;

/* ===============================
   READ UPDATE
================================ */
$raw = file_get_contents("php://input");
$update = json_decode($raw, true);

// If Telegram ping or empty update → reply OK
if (!$update) {
    echo "OK";
    exit;
}

// Accept ONLY normal messages
if (!isset($update["message"])) {
    echo "OK";
    exit;
}

$chat_id = $update["message"]["chat"]["id"] ?? 0;
$text    = trim($update["message"]["text"] ?? "");

// If no text (stickers, photos etc)
if ($text === "") {
    echo "OK";
    exit;
}

/* ===============================
   OWNER ONLY
================================ */
if ($chat_id != $OWNER_ID) {
    sendMessage($chat_id, "❌ Unauthorized");
    echo "OK";
    exit;
}

/* ===============================
   DATABASE (UPDATE LATER)
================================ */
// TEMP: comment DB until webhook is stable
$conn = @mysqli_connect(
  "sql211.infinityfree.com",
  "if0_40717053",
  "ASOYennB4G",
  "if0_40717053_122"
);

/* ===============================
   COMMANDS
================================ */
if ($text === "/start") {

    sendMessage(
        $chat_id,
        "✅ TaskMint Bot Connected\n\nUse /stats"
    );

} elseif ($text === "/stats") {

    if (!$conn) {
        sendMessage($chat_id, "⚠️ Database not connected yet");
        echo "OK";
        exit;
    }

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

echo "OK";

/* ===============================
   SEND MESSAGE
================================ */
function sendMessage($chat_id, $message) {
    global $BOT_TOKEN;
    @file_get_contents(
        "https://api.telegram.org/bot$BOT_TOKEN/sendMessage?" .
        http_build_query([
            "chat_id" => $chat_id,
            "text" => $message
        ])
    );
}
