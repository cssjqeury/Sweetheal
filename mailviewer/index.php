<?php
// Config - CHANGE THESE!
$imap_server = '{imap.hostinger.com:993/imap/ssl}INBOX';
$email = 'main@sweetheal.store';  // Your main mailbox
$password = 'Yassine04M@';  // Keep this secret!

// Connect
$conn = imap_open($imap_server, $email, $password) or die('Cannot connect: ' . imap_last_error());

// Get recent emails (last 50 - adjust as needed)
$emails = imap_search($conn, 'ALL');
rsort($emails);  // Newest first
$emails = array_slice($emails, 0, 50);

echo '<!DOCTYPE html><html><head><title>My Temp Mail Inbox</title>
<style>
body {font-family: Arial; background:#f4f4f4; padding:20px;}
.email {background:white; border:1px solid #ddd; margin:10px 0; padding:15px; border-radius:8px;}
.to {font-weight:bold; color:#0066cc;}
.from {color:#666;}
.subject {font-size:1.2em;}
.code {background:#e0ffe0; padding:10px; font-size:1.5em; margin:10px 0;}
</style></head><body>';
echo '<h1>My Personal Temp Mail (@sweetheal.store)</h1>';
echo '<p><strong>Total emails shown:</strong> ' . count($emails) . ' (refresh to update)</p>';

if ($emails) {
    foreach ($emails as $num) {
        $overview = imap_fetch_overview($conn, $num, 0);
        $header = imap_headerinfo($conn, $num);
        $to = $header->to[0]->mailbox . '@' . $header->to[0]->host;  // The unique address used
        $from = $header->from[0]->mailbox . '@' . $header->from[0]->host;
        $subject = $overview[0]->subject ?? '(no subject)';
        $date = date('M d, H:i', strtotime($overview[0]->date));

        // Get plain text body
        $body = imap_fetchbody($conn, $num, 1);
        if (!$body) $body = imap_fetchbody($conn, $num, 2);  // Try HTML part if needed

        // Simple code extraction (looks for 4-8 digit codes)
        preg_match_all('/\b\d{4,8}\b/', $body, $matches);
        $codes = array_unique($matches[0]);

        echo '<div class="email">';
        echo '<div class="to">To: ' . htmlspecialchars($to) . ' (your unique alias)</div>';
        echo '<div>From: ' . htmlspecialchars($from) . ' | ' . $date . '</div>';
        echo '<div class="subject">' . htmlspecialchars($subject) . '</div>';
        if (!empty($codes)) {
            echo '<div class="code"><strong>Possible verification codes:</strong> ' . implode(' | ', $codes) . '</div>';
        }
        echo '<div><pre>' . htmlspecialchars($body) . '</pre></div>';
        echo '</div>';
    }
} else {
    echo '<p>No emails yet – send a test to any@sweetheal.store!</p>';
}

imap_close($conn);
echo '</body></html>';
?>
