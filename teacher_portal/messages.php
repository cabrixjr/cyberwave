<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$user_id = $_SESSION['user_id'];

// 1. GET CURRENT CHAT DETAILS
$chat_with = isset($_GET['chat_with']) ? (int)$_GET['chat_with'] : null;
$chat_user = null;
if ($chat_with) {
    $stmt = $db->prepare("SELECT id, full_name, role FROM users WHERE id = ?");
    $stmt->execute([$chat_with]);
    $chat_user = $stmt->fetch();
}

// 2. FETCH RECENT CONVERSATIONS (Sidebar)
$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.full_name, u.role, 
    (SELECT message FROM messages 
     WHERE (sender_id = u.id AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = u.id) 
     ORDER BY created_at DESC LIMIT 1) as last_msg
    FROM users u
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$recent_chats = $stmt->fetchAll();

// 3. FETCH ALL USERS FOR "NEW CHAT" SEARCH
$stmt = $db->prepare("SELECT id, full_name, role FROM users WHERE id != ? LIMIT 20");
$stmt->execute([$user_id]);
$all_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f1f5f9; --white: #ffffff; --border: #e2e8f0; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; overflow: hidden; }
        
        .messaging-container { display: grid; grid-template-columns: 350px 1fr; height: calc(100vh - 70px); max-width: 1600px; margin: 0 auto; background: var(--white); }

        /* Sidebar */
        .contacts-panel { background: #f8fafc; border-right: 1px solid var(--border); display: flex; flex-direction: column; }
        .panel-header { padding: 25px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .new-chat-trigger { background: var(--primary); color: white; border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; font-size: 1.2rem; }
        
        .contact-list { flex: 1; overflow-y: auto; }
        .contact-card { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; }
        .contact-card.active { background: #eff6ff; border-right: 4px solid var(--primary); }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: #dbeafe; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; }

        /* Chat Window */
        .chat-window { display: flex; flex-direction: column; background: white; }
        .chat-header { padding: 15px 30px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .chat-body { flex: 1; padding: 30px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
        
        .msg { max-width: 65%; padding: 12px 18px; border-radius: 18px; font-size: 0.95rem; }
        .msg-received { align-self: flex-start; background: #f1f5f9; color: #1e293b; border-bottom-left-radius: 4px; }
        .msg-sent { align-self: flex-end; background: var(--primary); color: white; border-bottom-right-radius: 4px; }

        /* Footer */
        .chat-footer { padding: 20px 30px; border-top: 1px solid var(--border); }
        .input-wrapper { display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 8px 15px; border-radius: 15px; border: 1px solid var(--border); }
        .message-input { flex: 1; border: none; background: transparent; outline: none; padding: 10px; font-family: inherit; }
        .send-btn { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 400px; margin: 10vh auto; border-radius: 24px; padding: 25px; }
        .search-bar { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); margin-bottom: 15px; }
        .user-item { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 10px; cursor: pointer; text-decoration: none; color: inherit; }
        .user-item:hover { background: #f1f5f9; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="messaging-container">
        <aside class="contacts-panel">
            <div class="panel-header">
                <h3 style="margin:0;">Messages</h3>
                <button class="new-chat-trigger" onclick="document.getElementById('newChatModal').style.display='block'">+</button>
            </div>
            <div class="contact-list">
                <?php foreach($recent_chats as $chat): ?>
                    <a href="messages.php?chat_with=<?php echo $chat['id']; ?>&name=<?php echo urlencode($chat['full_name']); ?>" 
                       class="contact-card <?php echo ($chat_with == $chat['id']) ? 'active' : ''; ?>">
                        <div class="avatar"><?php echo substr($chat['full_name'], 0, 1); ?></div>
                        <div style="overflow: hidden;">
                            <div style="font-weight: 700;"><?php echo htmlspecialchars($chat['full_name']); ?></div>
                            <small style="color: #64748b; white-space: nowrap;"><?php echo htmlspecialchars($chat['last_msg'] ?? 'New connection'); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="chat-window">
            <?php if($chat_user): ?>
                <div class="chat-header">
                    <span style="font-weight: 800;"><?php echo htmlspecialchars($chat_user['full_name']); ?></span>
                    <small style="color: #10b981;">Active Now</small>
                </div>
                
                <div class="chat-body" id="chatContainer">
                    <?php
                    // Fetch real messages between these two users
                    $stmt = $db->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
                    $stmt->execute([$user_id, $chat_with, $chat_with, $user_id]);
                    $history = $stmt->fetchAll();

                    foreach($history as $m):
                        $class = ($m['sender_id'] == $user_id) ? 'msg-sent' : 'msg-received';
                    ?>
                        <div class="msg <?php echo $class; ?>">
                            <?php echo htmlspecialchars($m['message']); ?>
                            <?php if($m['attachment_path']): ?>
                                <div style="margin-top:5px; font-size:0.8rem; border-top:1px solid rgba(255,255,255,0.2); padding-top:5px;">
                                    📎 <a href="<?php echo $m['attachment_path']; ?>" target="_blank" style="color:inherit;">View Attachment</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form class="chat-footer" action="handlers/send_message.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="receiver_id" value="<?php echo $chat_with; ?>">
                    <div class="input-wrapper">
                        <label for="file-upload" style="cursor:pointer; font-size:1.2rem;">📎</label>
                        <input type="file" id="file-upload" name="attachment" style="display:none;">
                        
                        <input type="text" name="message" class="message-input" placeholder="Write something..." required>
                        <button type="submit" class="send-btn">Send</button>
                    </div>
                </form>
            <?php else: ?>
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#94a3b8;">
                    <div style="font-size: 4rem;">💬</div>
                    <h3>Start a Conversation</h3>
                    <p>Select a worker or employer to begin chatting.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div id="newChatModal" class="modal">
        <div class="modal-content">
            <h3>New Message</h3>
            <input type="text" class="search-bar" placeholder="Search by name..." onkeyup="searchUsers(this.value)">
            <div id="userList">
                <?php foreach($all_users as $u): ?>
                    <a href="messages.php?chat_with=<?php echo $u['id']; ?>&name=<?php echo urlencode($u['full_name']); ?>" class="user-item">
                        <div class="avatar"><?php echo substr($u['full_name'], 0, 1); ?></div>
                        <div>
                            <strong><?php echo $u['full_name']; ?></strong><br>
                            <small><?php echo $u['role']; ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function searchUsers(query) {
            let items = document.getElementsByClassName('user-item');
            for (let item of items) {
                item.style.display = item.innerText.toLowerCase().includes(query.toLowerCase()) ? 'flex' : 'none';
            }
        }
        // Auto-scroll to bottom of chat
        const container = document.getElementById('chatContainer');
        if(container) container.scrollTop = container.scrollHeight;

        window.onclick = function(e) {
            if(e.target == document.getElementById('newChatModal')) document.getElementById('newChatModal').style.display='none';
        }
    </script>
</body>
</html>