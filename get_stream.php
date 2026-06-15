<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// আপনার ওয়েবসাইটের ডোমেইন নাম এখানে দিন (www ছাড়া শুধু মূল নামটি দিন, যেমন: techpriyo.com)
$my_domain = "fifa2026-ecru.vercel.app"; 

$referer_host = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : '';

// ডোমেইন থেকে 'www.' থাকলে তা স্বয়ংক্রিয়ভাবে বাদ দেওয়া হচ্ছে (সেফটি ফিল্টার)
$referer_clean = preg_replace('/^www\./', '', $referer_host);
$my_domain_clean = preg_replace('/^www\./', '', $my_domain);

// সিকিউরিটি চেক:
// ১. রেফারার যদি খালি না থাকে (কিছু ব্রাউজার রেফারার পাঠায় না, তাই খালি থাকলে এলাও করা নিরাপদ)
// ২. রেফারার যদি লোকালহোস্ট বা আপনার নিজস্ব ডোমেইন না হয়, তবেই কেবল ব্লক করবে
if (!empty($referer_clean) && 
    $referer_clean !== 'localhost' && 
    $referer_clean !== '127.0.0.1' && 
    $referer_clean !== $my_domain_clean) {
    
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access Denied."]);
    exit;
}

// টার্গেট আইডি (ডিফল্ট আইডি সেট করা আছে)
$stream_id = (!empty($_GET['id'])) ? $_GET['id'] : '1781278102279';
$play_page_url = "http://172.19.178.180/play.php?id=" . urlencode($stream_id);

function fetch_content($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$play_html = fetch_content($play_page_url);
$final_stream_url = "";

if (!empty($play_html)) {
    // সোর্স কোড থেকে ব্যাকস্ল্যাশ এবং HTML এনটিটি ডিকোড করা হচ্ছে
    $html_clean = stripslashes(html_entity_decode($play_html));
    
    // ১. জাভাস্ক্রিপ্টের ভেতরের Base64 এনকোডেড URL খোঁজা হচ্ছে (const encodedUrl = "...")
    if (preg_match('/const\s+encodedUrl\s*=\s*["\']([^"\']+)["\']/i', $html_clean, $matches)) {
        $encoded_url = $matches[1];
        
        // Base64 ডিকোড করা হচ্ছে (জাভাস্ক্রিপ্টের atob এর মতো)
        $embed_url = base64_decode($encoded_url);
        
        // ডিকোড করা লিঙ্ক থেকে টোকেনটি আলাদা করা হচ্ছে
        if (strpos($embed_url, 'token=') !== false) {
            $token_parts = explode('token=', $embed_url);
            $token_raw = $token_parts[1];
            $token_clean = explode('&', $token_raw);
            $token = $token_clean[0];
            
            // বেস ডিরেক্টরি বের করা হচ্ছে (যেমন: http://172.19.178.149:18190/fifa_tsnsports4aq/)
            $base_dir = preg_replace('/embed\.html.*/i', '', $embed_url);
            
            // নিশ্চিত করা হচ্ছে যেন লিঙ্কটি http দিয়ে শুরু হয়
            if (strpos($base_dir, 'http') === false) {
                $base_dir = "http:" . $base_dir;
            }
            
            // ফাইনাল .m3u8 স্ট্রিমিং লিঙ্ক তৈরি
            $final_stream_url = $base_dir . "mono.m3u8?token=" . $token;
        }
    }
    
    // ২. যদি কোনো চ্যানেলে এনকোড ছাড়া সরাসরি লিঙ্ক থাকে (সেফটি ব্যাকআপ)
    if (empty($final_stream_url)) {
        $pos = strpos($html_clean, 'embed.html?token=');
        if ($pos !== false) {
            $start = $pos;
            while ($start > 0 && !in_array($html_clean[$start], ['"', "'", ' ', "\n", "\r", "\t", '<', '>'])) {
                $start--;
            }
            $start++;
            
            $end = $pos;
            while ($end < strlen($html_clean) && !in_array($html_clean[$end], ['"', "'", ' ', "\n", "\r", "\t", '<', '>'])) {
                $end++;
            }
            
            $embed_url = substr($html_clean, $start, $end - $start);
            $token_parts = explode('token=', $embed_url);
            $token_raw = $token_parts[1];
            $token_clean = explode('&', $token_raw);
            $token = $token_clean[0];
            
            $base_dir = preg_replace('/embed\.html.*/i', '', $embed_url);
            if (strpos($base_dir, 'http') === false) {
                $base_dir = "http:" . $base_dir;
            }
            $final_stream_url = $base_dir . "mono.m3u8?token=" . $token;
        }
    }
}

// লিঙ্ক কোনো কারণে না পাওয়া গেলে ব্যাকআপ ডেমো লিঙ্ক
if (empty($final_stream_url)) {
    $final_stream_url = "https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8";
    $status = "fallback";
} else {
    $status = "success";
}

echo json_encode([
    'status' => $status,
    'stream_id' => $stream_id,
    'url' => $final_stream_url
]);
