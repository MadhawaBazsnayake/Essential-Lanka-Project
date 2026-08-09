<?php
// client/chat.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// පාරිභෝගිකයෙක් ලෙස ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'Customer';

// URL එකෙන් worker_id සහ job_id ලබාගැනීම
$activeWorkerId = $_GET['worker_id'] ?? null;
$activeJobId = $_GET['job_id'] ?? null;
$activeWorkerName = "Select a Chat";

// Worker කෙනෙක් තෝරලා තියෙනවා නම් එයාගේ නම Database එකෙන් ගැනීම
if ($activeWorkerId) {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$activeWorkerId]);
    $worker = $stmt->fetch();
    if ($worker) {
        $activeWorkerName = $worker['first_name'] . ' ' . $worker['last_name'];
    }
}

// Fetch active chat contacts
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id as worker_id, u.first_name, u.last_name, j.id as job_id, j.title as job_title
    FROM jobs j
    LEFT JOIN bids b ON b.job_id = j.id
    JOIN users u ON (j.assigned_worker_id = u.id OR b.worker_id = u.id)
    WHERE j.client_id = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$userId]);
$contacts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    body { overflow: hidden; }
    .chat-section { padding: 30px 5%; height: 100vh; position: relative; display: flex; align-items: center; justify-content: center; }
    
    .chat-container { width: 100%; max-width: 1300px; height: calc(100vh - 60px); background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 25px 50px rgba(0,0,0,0.08); border-radius: 35px; display: grid; grid-template-columns: 350px 1fr; overflow: hidden; position: relative; z-index: 10; }

    /* Left Sidebar: Contacts */
    .chat-sidebar { border-right: 1.5px solid #f0ebe1; background: rgba(255,255,255,0.5); display: flex; flex-direction: column; }
    .sidebar-header { padding: 25px; border-bottom: 1.5px solid #f0ebe1; display: flex; justify-content: space-between; align-items: center; }
    .sidebar-header h2 { font-size: 1.5rem; font-weight: 800; color: #1a1a1a; display: flex; align-items: center; gap: 8px; }
    .btn-back { width: 40px; height: 40px; border-radius: 50%; background: #ffffff; border: 1px solid #e5dfd5; display: flex; justify-content: center; align-items: center; color: #666; transition: 0.3s; text-decoration: none; font-size: 1.2rem; cursor: pointer; }
    .btn-back:hover { background: #f8fafc; color: #1a1a1a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .btn-call { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
    .btn-call:hover { background: #10b981; color: white; }

    .contact-list { overflow-y: auto; flex: 1; padding: 15px; }
    .contact-item { display: flex; align-items: center; gap: 15px; padding: 15px; border-radius: 20px; cursor: pointer; transition: 0.3s; margin-bottom: 5px; border: 1.5px solid transparent; text-decoration: none; color: inherit; }
    .contact-item:hover { background: #ffffff; box-shadow: 0 5px 15px rgba(0,0,0,0.03); }
    .contact-item.active { background: #ffffff; border-color: #10b981; box-shadow: 0 10px 25px rgba(16,185,129,0.1); }
    
    .contact-avatar { width: 50px; height: 50px; border-radius: 50%; background: #e2e8f0; display: flex; justify-content: center; align-items: center; color: #64748b; font-weight: 700; font-size: 1.1rem; position: relative; flex-shrink: 0; }
    .contact-item.active .contact-avatar { background: rgba(16,185,129,0.1); color: #10b981; }
    .status-dot { position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; background: #10b981; border: 2px solid #ffffff; border-radius: 50%; }

    .contact-info { flex: 1; overflow: hidden; }
    .contact-info h4 { font-size: 1rem; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .contact-info p { font-size: 0.85rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    
    /* Right Side: Chat Window */
    .chat-window { display: flex; flex-direction: column; background: #ffffff; position: relative; }
    
    .chat-header { padding: 20px 30px; border-bottom: 1.5px solid #f0ebe1; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); z-index: 10; }
    .chat-user-info { display: flex; align-items: center; gap: 15px; }
    .chat-user-info h3 { font-size: 1.2rem; font-weight: 800; color: #1a1a1a; margin-bottom: 2px; }
    .chat-user-info p { font-size: 0.85rem; color: #10b981; font-weight: 600; display: flex; align-items: center; gap: 5px; }

    .chat-messages { flex: 1; padding: 30px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; background: #f8fafc; }
    
    .msg-bubble { max-width: 70%; padding: 15px 20px; font-size: 0.95rem; line-height: 1.5; position: relative; word-wrap: break-word; }
    .msg-received { align-self: flex-start; background: #ffffff; color: #1a1a1a; border-radius: 20px 20px 20px 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #e5dfd5; }
    .msg-sent { align-self: flex-end; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border-radius: 20px 20px 5px 20px; box-shadow: 0 10px 20px rgba(16,185,129,0.2); }
    .msg-time { font-size: 0.7rem; margin-top: 5px; display: block; opacity: 0.7; }
    .msg-received .msg-time { color: #64748b; }
    .msg-sent .msg-time { color: rgba(255,255,255,0.8); text-align: right; }
    
    .msg-link { color: #ffffff; font-weight: 700; text-decoration: underline; }

    /* Chat Input Area */
    .chat-input-area { padding: 20px 30px; border-top: 1.5px solid #f0ebe1; background: #ffffff; display: flex; align-items: center; gap: 12px; }
    .btn-attach { width: 45px; height: 45px; border-radius: 50%; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; justify-content: center; align-items: center; color: #64748b; font-size: 1.2rem; cursor: pointer; transition: 0.3s; flex-shrink: 0; }
    .btn-attach:hover { background: #e2e8f0; color: #1a1a1a; transform: translateY(-2px); }
    .btn-location { background: rgba(59,130,246,0.1); color: #3b82f6; border-color: rgba(59,130,246,0.2); }
    .btn-location:hover { background: #3b82f6; color: white; }
    
    .input-wrapper { flex: 1; position: relative; display: flex; align-items: center; }
    .chat-input { width: 100%; padding: 15px 20px; border-radius: 50px; border: 2px solid #e5dfd5; background: #f8fafc; font-size: 0.95rem; font-family: inherit; outline: none; transition: 0.3s; }
    .chat-input:focus { border-color: #10b981; background: #ffffff; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    
    .btn-send { width: 50px; height: 50px; border-radius: 50%; background: #1a1a1a; border: none; color: white; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; cursor: pointer; transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.15); flex-shrink: 0; }
    .btn-send:hover { background: #10b981; transform: scale(1.05); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }

    /* WebRTC Call Screen Overlay */
    .call-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(20px); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; pointer-events: none; transition: 0.4s; }
    .call-overlay.active { opacity: 1; pointer-events: auto; }
    .call-avatar-wrapper { position: relative; margin-bottom: 30px; }
    .call-avatar { width: 150px; height: 150px; border-radius: 50%; background: #e2e8f0; display: flex; justify-content: center; align-items: center; font-size: 3rem; color: #64748b; font-weight: 800; border: 4px solid #10b981; position: relative; z-index: 2; object-fit: cover; }
    .pulse-ring { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 150px; height: 150px; border-radius: 50%; border: 2px solid #10b981; z-index: 1; animation: pulse 2s infinite; }
    .pulse-ring:nth-child(2) { animation-delay: 0.5s; }
    .pulse-ring:nth-child(3) { animation-delay: 1s; }
    
    @keyframes pulse { 0% { width: 150px; height: 150px; opacity: 1; } 100% { width: 350px; height: 350px; opacity: 0; } }

    .call-info { text-align: center; color: white; margin-bottom: 60px; }
    .call-info h2 { font-size: 2rem; font-weight: 800; margin-bottom: 10px; }
    .call-info p { font-size: 1.1rem; color: #94a3b8; font-weight: 500; }

    .call-actions { display: flex; gap: 25px; }
    .call-btn { width: 65px; height: 65px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 1.8rem; border: none; cursor: pointer; transition: 0.3s; color: white; }
    .call-btn.mute { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); }
    .call-btn.mute:hover { background: rgba(255,255,255,0.2); }
    .call-btn.end { background: #ef4444; box-shadow: 0 10px 30px rgba(239,68,68,0.4); }
    .call-btn.end:hover { background: #dc2626; transform: scale(1.1); }

    /* Empty State */
    .chat-empty { display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; background: #f8fafc; color: #a3a3a3; text-align: center; }
    .chat-empty i { font-size: 5rem; margin-bottom: 15px; color: #cbd5e1; }
    .chat-empty h3 { font-size: 1.5rem; font-weight: 800; color: #64748b; margin-bottom: 5px; }

    @media (max-width: 992px) {
        .chat-container { grid-template-columns: 1fr; }
        .chat-sidebar { display: <?php echo $activeWorkerId ? 'none' : 'flex'; ?>; }
        .chat-window { display: <?php echo $activeWorkerId ? 'flex' : 'none'; ?>; }
    }
</style>

<!-- Call Screen Overlay -->
<div class="call-overlay" id="callScreen">
    <div class="call-avatar-wrapper">
        <div class="pulse-ring"></div>
        <div class="pulse-ring"></div>
        <div class="pulse-ring"></div>
        <div class="call-avatar">
            <?php 
            $initials = "W";
            if($activeWorkerName != "Select a Chat"){
                $parts = explode(" ", $activeWorkerName);
                $initials = substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : '');
            }
            echo htmlspecialchars($initials);
            ?>
        </div>
    </div>
    
    <div class="call-info">
        <h2 id="callName"><?php echo htmlspecialchars($activeWorkerName); ?></h2>
        <p id="callStatus">Calling via Secure Network...</p>
    </div>

    <div class="call-actions">
        <button class="call-btn mute" onclick="toggleMute()" id="muteBtn"><i class="ph-fill ph-microphone"></i></button>
        <button class="call-btn end" onclick="endCall()"><i class="ph-fill ph-phone-slash"></i></button>
        <button class="call-btn mute" onclick="toggleVideo()" id="videoBtn"><i class="ph-fill ph-video-camera-slash"></i></button>
    </div>
</div>

<section class="chat-section bg-cream">
    <div class="blob-bg"></div>

    <div class="chat-container fade-up show">
        
        <!-- Left Sidebar: Contact List -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <h2><i class="ph-fill ph-chats" style="color: #10b981;"></i> Messages</h2>
                <a href="dashboard.php" class="btn-back" title="Back to Dashboard"><i class="ph-bold ph-x"></i></a>
            </div>
            
            <div class="contact-list">
                <?php if (empty($contacts)): ?>
                    <div style="text-align: center; padding: 20px; color: #a3a3a3; font-size: 0.9rem;">
                        No active chat contacts yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): 
                        $init = strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1));
                        $isActive = ($activeWorkerId == $contact['worker_id'] && $activeJobId == $contact['job_id']);
                    ?>
                        <a href="chat.php?worker_id=<?php echo $contact['worker_id']; ?>&job_id=<?php echo $contact['job_id']; ?>" class="contact-item <?php echo $isActive ? 'active' : ''; ?>">
                            <div class="contact-avatar"><?php echo htmlspecialchars($init); ?> <div class="status-dot"></div></div>
                            <div class="contact-info">
                                <h4><?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?></h4>
                                <p>Job #<?php echo $contact['job_id']; ?> - <?php echo htmlspecialchars($contact['job_title']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Panel: Chat Interface -->
        <?php if ($activeWorkerId && $activeJobId): ?>
            <div class="chat-window">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-user-info">
                        <a href="chat.php" class="btn-back" style="display: none; margin-right: 5px;" id="mobileBackBtn"><i class="ph-bold ph-arrow-left"></i></a>
                        <div class="contact-avatar">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div>
                            <h3><?php echo htmlspecialchars($activeWorkerName); ?></h3>
                            <p><span class="status-dot" style="position:relative; bottom:0; right:0; display:inline-block;"></span> Online (Job #<?php echo htmlspecialchars($activeJobId); ?>)</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn-back btn-call" onclick="startCall()" title="Start Voice/Video Call"><i class="ph-fill ph-phone-call"></i></button>
                    </div>
                </div>

                <!-- Chat Messages Area -->
                <div class="chat-messages" id="chatMessages">
                    <div style="text-align: center; color: #94a3b8; font-size: 0.85rem; margin-top: 20px;">
                        Loading messages...
                    </div>
                </div>

                <!-- Chat Input Area -->
                <div class="chat-input-area">
                    <input type="file" id="chatFileAttach" style="display: none;" onchange="handleFileAttachment(event)">
                    <button class="btn-attach" onclick="document.getElementById('chatFileAttach').click()" title="Attach File"><i class="ph-bold ph-paperclip"></i></button>
                    
                    <button class="btn-attach btn-location" onclick="sendLiveLocation()" title="Send Live Location"><i class="ph-bold ph-map-pin"></i></button>
                    
                    <div class="input-wrapper">
                        <input type="text" id="messageInput" class="chat-input" placeholder="Type your message to worker..." onkeypress="handleKeyPress(event)">
                    </div>
                    <button class="btn-send" onclick="sendMessage()" title="Send"><i class="ph-bold ph-paper-plane-right"></i></button>
                </div>
            </div>
        <?php else: ?>
            <div class="chat-empty">
                <i class="ph-fill ph-chats-teardrop"></i>
                <h3>Your Messages</h3>
                <p>Select a worker from the left menu to start chatting.</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<script>
    const currentUserId = <?php echo $userId; ?>;
    const activeWorkerId = <?php echo $activeWorkerId ? $activeWorkerId : 'null'; ?>;
    const activeJobId = <?php echo $activeJobId ? $activeJobId : 'null'; ?>;
    const chatContainer = document.getElementById('chatMessages');

    if (window.innerWidth <= 992) {
        const mobileBackBtn = document.getElementById('mobileBackBtn');
        if (mobileBackBtn) mobileBackBtn.style.display = 'flex';
    }

    // --- 1. AJAX Chat Functionality (MySQL) ---
    
    function loadMessages() {
        if (!activeWorkerId || !activeJobId) return;

        fetch(`../api/chat-fetch.php?job_id=${activeJobId}&other_user_id=${activeWorkerId}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    chatContainer.innerHTML = ''; 
                    data.messages.forEach(msg => {
                        const isSent = (msg.sender_id == currentUserId);
                        const typeClass = isSent ? 'msg-sent' : 'msg-received';
                        
                        const dateObj = new Date(msg.created_at);
                        const timeString = dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                        const msgHtml = `
                            <div class="msg-bubble ${typeClass} fade-up show">
                                ${msg.message}
                                <span class="msg-time">${timeString}</span>
                            </div>
                        `;
                        chatContainer.insertAdjacentHTML('beforeend', msgHtml);
                    });
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            })
            .catch(error => console.error('Error fetching messages:', error));
    }

    function sendMessage() {
        const inputField = document.getElementById('messageInput');
        const text = inputField.value.trim();
        
        if (text !== '' && activeWorkerId && activeJobId) {
            
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('job_id', activeJobId);
            formData.append('receiver_id', activeWorkerId);
            formData.append('message', text);

            fetch('../api/chat-send.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    inputField.value = '';
                    loadMessages(); 
                } else {
                    alert("Failed to send message.");
                }
            })
            .catch(error => console.error('Error sending message:', error));
            
            const msgHtml = `
                <div class="msg-bubble msg-sent fade-up show">
                    ${text}
                    <span class="msg-time">Sending...</span>
                </div>
            `;
            chatContainer.insertAdjacentHTML('beforeend', msgHtml);
            chatContainer.scrollTop = chatContainer.scrollHeight;
            inputField.value = '';
        }
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') sendMessage();
    }

    if (activeWorkerId && activeJobId) {
        loadMessages();
        setInterval(loadMessages, 3000); 
    }

    // --- 2. Attachments & Location ---
    function handleFileAttachment(event) {
        const file = event.target.files[0];
        if (file) {
            alert(`File "${file.name}" selected. File upload backend logic needs to be implemented.`);
        }
    }

    function sendLiveLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const mapLink = `https://www.google.com/maps?q=${lat},${lng}`;
                    const msgText = `📍 My Live Location:<br><a href="${mapLink}" target="_blank" class="msg-link">View on Google Maps</a>`;
                    
                    document.getElementById('messageInput').value = msgText;
                    sendMessage();
                },
                function(error) {
                    alert("Unable to get location. Please check browser permissions.");
                }
            );
        } else {
            alert("Geolocation is not supported by your browser.");
        }
    }

    // --- 3. WebRTC Call System UI ---
    let isMuted = false;
    let isVideoOn = false;

    function startCall() {
        document.getElementById('callScreen').classList.add('active');
    }

    function endCall() {
        document.getElementById('callScreen').classList.remove('active');
        document.getElementById('callStatus').innerText = "Calling via Secure Network...";
    }

    function toggleMute() {
        isMuted = !isMuted;
        const btn = document.getElementById('muteBtn');
        btn.innerHTML = isMuted ? '<i class="ph-fill ph-microphone-slash"></i>' : '<i class="ph-fill ph-microphone"></i>';
        btn.style.background = isMuted ? 'rgba(239, 68, 68, 0.8)' : 'rgba(255,255,255,0.1)';
    }

    function toggleVideo() {
        isVideoOn = !isVideoOn;
        const btn = document.getElementById('videoBtn');
        btn.innerHTML = isVideoOn ? '<i class="ph-fill ph-video-camera"></i>' : '<i class="ph-fill ph-video-camera-slash"></i>';
        btn.style.background = isVideoOn ? 'rgba(16, 185, 129, 0.8)' : 'rgba(255,255,255,0.1)';
    }
</script>

</body>
</html>