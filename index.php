<?php
// --- CONFIGURATION ---
$apiKey = 'b608d3760a67411eb680e62050ce12fa'; // News API key
// Get search term from form
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
if (!empty($searchTerm)) {
    $query = urlencode($searchTerm . ' -anime -manga -episode -review');
} else {
    // Your custom default query for the feed
    $query = urlencode("gaming OR \"video games\" OR esports OR \"game releases\"");
}
$endpoint = "https://newsapi.org/v2/everything?apiKey={$apiKey}&q={$query}&language=en&pageSize=4&sortBy=relevancy";

// Calculate relevance score for an article
function calculateRelevanceScore($article, $searchTerms) {
    $score = 0;
    $searchTerms = array_map('strtolower', $searchTerms);
    
    $title = strtolower($article['title'] ?? '');
    $description = strtolower($article['description'] ?? '');
    $content = strtolower($article['content'] ?? '');
    
    foreach ($searchTerms as $term) {
        // Title matches are most important (weight: 10)
        $score += substr_count($title, $term) * 10;
        // Description matches are important (weight: 5)
        $score += substr_count($description, $term) * 5;
        // Content matches are less important (weight: 2)
        $score += substr_count($content, $term) * 2;
    }
    
    return $score;
}

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
    if ($response === FALSE) {
        return ['status' => 'error', 'message' => 'Failed to fetch news. Please reconsider your query and try again later.'];
    }
    return $response ? json_decode($response, true) : null;
}
$newsData = fetchNews($endpoint);

// Custom relevance sorting for search results
if (!empty($searchTerm) && isset($newsData['articles']) && count($newsData['articles']) > 0) {
    $searchTerms = array_filter(array_map('trim', explode(' ', $searchTerm)));
    
    // Calculate relevance scores for each article
    foreach ($newsData['articles'] as $index => $article) {
        $newsData['articles'][$index]['relevance_score'] = calculateRelevanceScore($article, $searchTerms);
    }
    
    // Sort articles by relevance score (highest first)
    usort($newsData['articles'], function($a, $b) {
        return $b['relevance_score'] - $a['relevance_score'];
    });
}
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
        <h1><a href="index.php"  style="text-decoration: none; color: inherit;">🎮 Gaming News Live</a></h1>
        <p>Latest updates on consoles, gaming gadgets and game releases. some articles may have irrelevant titles kindly check description</p>
    </header>
    <form class="search-bar" method="get" action="">
            <input type="text" name="q" placeholder="Search news..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Search</button>
        </form>
    <div class="news-list">
    <?php if (isset($newsData['articles']) && count($newsData['articles']) > 0): ?>
            <?php foreach ($newsData['articles'] as $news): ?>
                <div class="news-card">
                    <?php if (!empty($news['urlToImage'])): ?>
                        <img src="<?php echo htmlspecialchars($news['urlToImage']); ?>" alt="news image" class="news-image">
                    <?php endif; ?>
                    <div class="news-content">
                    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                        <h2 class="news-title">
                            <?php echo htmlspecialchars($news['title']); ?>
                        </h2>
                        <div class="news-meta">
                            <?php echo htmlspecialchars($news['source']['name'] ?? 'Unknown Source'); ?>
                            | <?php echo !empty($news['publishedAt']) ? date("M d, Y H:i", strtotime($news['publishedAt'])) : ''; ?>
                        </div>
                        <div class="news-desc">
                            <?php echo htmlspecialchars($news['description'] ?? 'No summary available.'); ?>
                        </div>
                        <span style="display:inline-block; background:#4caf50; color:white; padding:2px 8px; font-size:12px; border-radius:4px;">
                        Relevance Rank: <?php echo $news['relevance_score'] ?? 'N/A'; ?>
                        </span>
                    </div> 
                        <?php if (!empty($news['url'])): ?>
                            <a class="news-link" href="<?php echo htmlspecialchars($news['url']); ?>" target="_blank">Read Full Article</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; margin-top:40px; font-size:1.2em;">
                <?php
                if (isset($newsData['status']) && $newsData['status'] == 'error') 
                    { echo "API Error: " . htmlspecialchars($newsData['message']); }
                else {
                    echo "No gaming news found at the moment. Please reconsider your query and try again later";
                }
                ?>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>