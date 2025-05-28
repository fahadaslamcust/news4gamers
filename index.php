<?php
// --- CONFIGURATION ---
$apiKey = 'pub_2ec143549ccd4df682753ba78465379a'; // NewsData.io API key

// Topics to search for
$topics = [
    "console gaming",
    "PC gaming",
    "Android gaming",
    "game availability"
];
// Get search term from form
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '. this topic or topics is related to gaming search under these categories: ';
if ($searchTerm !== '') {
    $query = urlencode(implode(" OR ", $topics));
} else {
    $query = urlencode("your query might not be related to gaming, please try again later");
}
$endpoint = "https://newsdata.io/api/1/news?apikey={$apiKey}&q={$query}&language=en&category=technology,entertainment,business";
// Fetch news from API
function fetchNews($endpoint) {
    $options = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: PHP\r\n"
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($endpoint, false, $context);
    return $response ? json_decode($response, true) : null;
}

$newsData = fetchNews($endpoint);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gaming News - Live Updates</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 50%, #a5b4fc 100%);
            color: #23272f;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        header {
            background:rgb(17, 82, 213);
            padding: 24px 0 16px 0;
            text-align: center;
            box-shadow: 0 2px 10px #0003;
        }
        header h1 {
            margin: 0;
            font-size: 2em;
            color: #72ffb6;
            letter-spacing: 1px;
        }
        .news-list {
            max-width: 800px;
            margin: 32px auto;
            padding: 0 16px;
        }
        .news-card {
            background: #23272f;
            margin-bottom: 24px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #0002;
            overflow: hidden;
            transition: transform 0.15s;
        }
        .news-card:hover {
            transform: scale(1.015);
        }
        .news-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #121212;
            display: block;
        }
        .news-content {
            padding: 18px 18px 14px 18px;
        }
        .news-title {
            margin: 0 0 8px 0;
            font-size: 1.2em;
            color: #72ffb6;
        }
        .news-meta {
            font-size: 0.92em;
            color: #9dabba;
            margin-bottom: 10px;
        }
        .news-desc {
            color: #e4e6eb;
            margin-bottom: 10px;
        }
        .news-link {
            color: #72e0ff;
            text-decoration: none;
            font-weight: bold;
        }
        .news-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .news-image { height: 120px; }
        }
        .search-bar {
            max-width: 400px;
            margin: 24px auto 0 auto;
            display: flex;
            justify-content: center;
        }
        .search-bar input[type="text"] {
            flex: 1;
            padding: 10px 12px;
            border-radius: 6px 0 0 6px;
            border: none;
            font-size: 1em;
        }
        .search-bar button {
            padding: 10px 18px;
            border-radius: 0 6px 6px 0;
            border: none;
            background: #72ffb6;
            color: #181a20;
            font-weight: bold;
            cursor: pointer;
            font-size: 1em;
        }
        .search-bar button:hover {
            background: #72e0ff;
        }
    </style>
</head>
<body>
    <header>
        <h1>🎮 Gaming News Live</h1>
        <p>Latest updates on consoles, PC, Android gaming, and game releases</p>
    </header>
    <form class="search-bar" method="get" action="">
            <input type="text" name="q" placeholder="Search news..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Search</button>
        </form>
    <div class="news-list">
        <?php if (isset($newsData['results']) && count($newsData['results']) > 0): ?>
            <?php foreach ($newsData['results'] as $news): ?>
                <div class="news-card">
                    <?php if (!empty($news['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($news['image_url']); ?>" alt="news image" class="news-image">
                    <?php endif; ?>
                    <div class="news-content">
                        <h2 class="news-title">
                            <?php echo htmlspecialchars($news['title']); ?>
                        </h2>
                        <div class="news-meta">
                            <?php echo htmlspecialchars($news['source_id'] ?? ''); ?>
                            | <?php echo !empty($news['pubDate']) ? date("M d, Y H:i", strtotime($news['pubDate'])) : ''; ?>
                        </div>
                        <div class="news-desc">
                            <?php echo htmlspecialchars($news['description'] ?? 'No summary available.'); ?>
                        </div>
                        <?php if (!empty($news['link'])): ?>
                            <a class="news-link" href="<?php echo htmlspecialchars($news['link']); ?>" target="_blank" rel="noopener">Read more &rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; margin-top:40px; font-size:1.2em;">
                <?php
                if (isset($newsData['status']) && $newsData['status'] == 'error') {
                    echo "API Error: " . htmlspecialchars($newsData['message']);
                } else {
                    echo "No gaming news found at the moment. Please reconsider your query and try again later";
                }
                ?>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>