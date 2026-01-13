<?php
http_response_code(200);
ini_set('display_errors', 0);
error_reporting(0);

/* CONFIG */
$BOT_TOKEN = getenv("BOT_TOKEN");
$OWNER_ID  = trim(getenv("OWNER_ID"));

/* READ UPDATE */
$raw = file_get_contents("php://input");
$update = json_decode($raw, true);

if (!$update || !isset($update["message"])) {
    echo "OK";
    exit;
}

$chat_id = (string)($update["message"]["chat"]["id"] ?? "");
$text    = trim($update["message"]["text"] ?? "");

/* OWNER CHECK */
if ($chat_id !== (string)$OWNER_ID) {
    sendMessage($chat_id, "❌ Unauthorized");
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
if ($text === "/start") {
    sendMessage($chat_id, "✅ TaskMint Bot Connected\nUse /stats");
}

elseif ($text === "/stats") {
    if (!$conn) {
        sendMessage($chat_id, "❌ Database connection failed");
        echo "OK";
        exit;
    }

    $u = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM users"))["total"] ?? 0;
    $b = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(balance) total FROM users"))["total"] ?? 0;

    sendMessage(
        $chat_id,
        "📊 TaskMint Stats\n👥 Users: $u\n💰 Balance: ₹$b"
    );
}

echo "OK";

/* SEND MESSAGE */
function sendMessage($chat_id, $text) {
    $token = getenv("BOT_TOKEN");
    file_get_contents(
        "https://api.telegram.org/bot$token/sendMessage?" .
        http_build_query([
            "chat_id" => $chat_id,
            "text" => $text
        ])
    );
}
