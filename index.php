<?php
http_response_code(200);
ini_set('display_errors', 0);
error_reporting(0);

/* CONFIG */
$BOT_TOKEN = getenv("BOT_TOKEN");
$OWNER_ID  = getenv("OWNER_ID");

/* READ UPDATE */
$update = json_decode(file_get_contents("php://input"), true);
$message = $update["message"] ?? null;
if (!$message) {
    echo "OK";
    exit;
}

$chat_id = $update["message"]["chat"]["id"] ?? 0;
$text = trim($message["text"] ?? "");

/* OWNER ONLY */
if ((string)$chat_id !== (string)$OWNER_ID) {
    sendMessage($chat_id, "❌ Unauthorized");
    echo "OK";
    exit;
}

if ($text === "") {
    sendMessage($chat_id, "⚠️ Empty message received");
    echo "OK";
    exit;
}

/* DATABASE */
$conn = @mysqli_connect(
    getenv("DB_HOST"),
    getenv("DB_USER"),
    getenv("DB_PASS"),
    getenv("DB_NAME")
);

/* COMMANDS */
if (strpos($text, "/start") === 0) {
    sendMessage($chat_id, "✅ TaskMint Bot Connected\nUse /stats");
}

elseif (strpos($text, "/stats") === 0) {
    if (!$conn) {
        sendMessage($chat_id, "❌ Database connection failed");
        echo "OK";
        exit;
    }

    $u = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM users"));
    $b = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(balance) total FROM users"));

    sendMessage(
        $chat_id,
        "📊 TaskMint Stats\n👥 Users: {$u['total']}\n💰 Balance: ₹{$b['total']}"
    );
}

echo "OK";

/* SEND MESSAGE */
function sendMessage($chat_id, $text) {
    global $BOT_TOKEN;
    file_get_contents(
        "https://api.telegram.org/bot$BOT_TOKEN/sendMessage?" .
        http_build_query([
            "chat_id" => $chat_id,
            "text" => $text
        ])
    );
}
