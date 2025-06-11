<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

require_once 'includes/config.php';

// Get GitHub API key from settings
$github_api_key = '';
$result = $conn->query("SELECT github_api_key FROM settings LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $github_api_key = $row['github_api_key'];
}

// Get applicants with GitHub usernames
$applicants = [];
$result = $conn->query("SELECT id, name, github FROM applicants WHERE github != ''");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $applicants[] = $row;
    }
}

// Process GitHub data if API key exists
$github_data = [];
if (!empty($github_api_key)) {
    foreach ($applicants as $applicant) {
        if (!empty($applicant['github'])) {
            $username = $applicant['github'];
            $url = "https://api.github.com/users/$username";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'HR Recruitment System');
            if (!empty($github_api_key)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token $github_api_key"
                ]);
            }
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            if (isset($data['id'])) {
                $github_data[] = [
                    'id' => $applicant['id'],
                    'name' => $applicant['name'],
                    'username' => $username,
                    'bio' => $data['bio'] ?? 'No bio',
                    'location' => $data['location'] ?? 'Not specified',
                    'created_at' => isset($data['created_at']) ? date('Y-m-d', strtotime($data['created_at'])) : 'Unknown',
                    'followers' => $data['followers'] ?? 0,
                    'following' => $data['following'] ?? 0,
                    'public_repos' => $data['public_repos'] ?? 0,
                    'score' => ($data['followers'] * 2) + ($data['public_repos'] * 3) + $data['following']
                ];
            }
        }
    }
    
    // Sort by score
    usort($github_data, function($a, $b) {
        return $b['score'] - $a['score'];
    });
}
?>

<div class="content-header">
    <h1>GitHub Ranking</h1>
</div>

<div class="github-container">
    <?php if (empty($github_api_key)): ?>
    <div class="alert alert-warning">
        GitHub API key is not set. Please set it in Settings to enable GitHub ranking.
    </div>
    <?php endif; ?>
    
    <?php if (!empty($github_data)): ?>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Candidate</th>
                <th>Bio</th>
                <th>Location</th>
                <th>Account Created</th>
                <th>Followers</th>
                <th>Following</th>
                <th>Public Repos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($github_data as $index => $profile): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($profile['name']); ?></strong><br>
                    <small><a href="https://github.com/<?php echo htmlspecialchars($profile['username']); ?>" target="_blank"><?php echo htmlspecialchars($profile['username']); ?></a></small>
                </td>
                <td><?php echo htmlspecialchars($profile['bio']); ?></td>
                <td><?php echo htmlspecialchars($profile['location']); ?></td>
                <td><?php echo $profile['created_at']; ?></td>
                <td><?php echo $profile['followers']; ?></td>
                <td><?php echo $profile['following']; ?></td>
                <td><?php echo $profile['public_repos']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php elseif (!empty($github_api_key)): ?>
    <div class="alert alert-info">
        No GitHub data available. Applicants need to provide their GitHub username in the application form.
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>