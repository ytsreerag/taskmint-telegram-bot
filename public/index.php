<?php
http_response_code(200);
ini_set('display_errors', 0);
error_reporting(0);

/* CONFIG */
$BOT_TOKEN = getenv("BOT_TOKEN");
$OWNER_ID  = intval(getenv("OWNER_ID"));

/* READ UPDATE */
$update = json_decode(file_get_contents("php://input"), true);
if (!$update || !isset($update["message"])) {
    echo "OK";
    exit;
}

$chat_id = $update["message"]["chat"]["id"] ?? 0;
$text    = trim($update["message"]["text"] ?? "");

/* OWNER ONLY */
if ($chat_id !== $OWNER_ID) {
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

if ($text === "/stats") {
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
        http_build_query(["chat_id"=>$chat_id,"text"=>$text])
    );
}
