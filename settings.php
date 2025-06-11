<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

require_once 'includes/config.php';

$success = '';
$error = '';

// Handle theme change
if (isset($_POST['change_theme'])) {
    $theme = $_POST['theme'];
    setcookie('theme', $theme, time() + (86400 * 30), "/"); // 30 days
    $success = "Theme changed successfully!";
}

// Handle GitHub API key update
if (isset($_POST['update_github_key'])) {
    $github_api_key = $conn->real_escape_string($_POST['github_api_key']);
    
    // Check if settings row exists
    $result = $conn->query("SELECT COUNT(*) as count FROM settings");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $sql = "UPDATE settings SET github_api_key = '$github_api_key'";
    } else {
        $sql = "INSERT INTO settings (github_api_key) VALUES ('$github_api_key')";
    }
    
    if ($conn->query($sql)) {
        $success = "GitHub API key updated successfully!";
    } else {
        $error = "Error updating GitHub API key: " . $conn->error;
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "New passwords don't match";
    } else {
        // Verify current password
        $user_id = $_SESSION['user_id'];
        $result = $conn->query("SELECT password FROM users WHERE id = $user_id");
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashed_password' WHERE id = $user_id");
            $success = "Password changed successfully!";
        } else {
            $error = "Current password is incorrect";
        }
    }
}

// Get current GitHub API key
$github_api_key = '';
$result = $conn->query("SELECT github_api_key FROM settings LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $github_api_key = $row['github_api_key'];
}

// Get current theme
$current_theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'default';
?>

<div class="content-header">
    <h1>Settings</h1>
</div>

<div class="settings-container">
    <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="settings-section">
        <h2>Theme Settings</h2>
        <form method="POST">
            <div class="form-group">
                <label for="theme">Select Theme</label>
                <select id="theme" name="theme">
                    <option value="default" <?php echo $current_theme == 'default' ? 'selected' : ''; ?>>Default</option>
                    <option value="dark" <?php echo $current_theme == 'dark' ? 'selected' : ''; ?>>Dark Mode</option>
                    <option value="light" <?php echo $current_theme == 'light' ? 'selected' : ''; ?>>Light Mode</option>
                    <option value="blue" <?php echo $current_theme == 'blue' ? 'selected' : ''; ?>>Blue Theme</option>
                </select>
            </div>
            <button type="submit" name="change_theme" class="btn btn-primary">Change Theme</button>
        </form>
    </div>
    
    <div class="settings-section">
        <h2>GitHub API</h2>
        <form method="POST">
            <div class="form-group">
                <label for="github_api_key">GitHub API Key</label>
                <input type="text" id="github_api_key" name="github_api_key" value="<?php echo htmlspecialchars($github_api_key); ?>" placeholder="Enter your GitHub API key">
                <small>Get your API key from <a href="https://github.com/settings/tokens" target="_blank">GitHub</a></small>
            </div>
            <button type="submit" name="update_github_key" class="btn btn-primary">Save API Key</button>
        </form>
    </div>
    
    <div class="settings-section">
        <h2>Change Password</h2>
        <form method="POST">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
        </form>
    </div>
    
    <div class="settings-section">
        <h2>Logout</h2>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>