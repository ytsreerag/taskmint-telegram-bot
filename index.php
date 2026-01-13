<?php
http_response_code(200);
error_reporting(0);

/* CONFIG */
$BOT_TOKEN = getenv("BOT_TOKEN");
$OWNER_ID  = (string)getenv("OWNER_ID");

/* READ UPDATE */
$update = json_decode(file_get_contents("php://input"), true);

$message = $update["message"]
        ?? $update["edited_message"]
        ?? null;

if (!$message) {
    echo "OK";
    exit;
}

$chat_id = (string)($message["chat"]["id"] ?? "");
$text    = trim($message["text"] ?? "");

/* OWNER CHECK */
if ($chat_id !== $OWNER_ID) {
    sendMessage($chat_id, "❌ Unauthorized");
    echo "OK";
    exit;
}

/* CONFIRM OWNER (you can remove later) */
if ($text === "") {
    sendMessage($chat_id, "⚠️ Owner message without text");
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
        "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage?" .
        http_build_query([
            "chat_id" => $chat_id,
            "text"    => $text
        ])
    );
}
