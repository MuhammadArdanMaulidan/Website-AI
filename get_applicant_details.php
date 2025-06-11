<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

if (!isset($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

$id = intval($_GET['id']);
$applicant = null;

$result = $conn->query("SELECT * FROM applicants WHERE id = $id");
if ($result && $result->num_rows > 0) {
    $applicant = $result->fetch_assoc();
} else {
    header("HTTP/1.1 404 Not Found");
    exit();
}
?>

<div class="applicant-details-container">
    <div class="detail-row">
        <strong>Name:</strong> <?php echo htmlspecialchars($applicant['name']); ?>
    </div>
    <div class="detail-row">
        <strong>Email:</strong> <?php echo htmlspecialchars($applicant['email']); ?>
    </div>
    <div class="detail-row">
        <strong>Phone:</strong> <?php echo htmlspecialchars($applicant['phone']); ?>
    </div>
    
    <?php if (!empty($applicant['linkedin'])): ?>
    <div class="detail-row">
        <strong>LinkedIn:</strong> 
        <a href="<?php echo htmlspecialchars($applicant['linkedin']); ?>" target="_blank">View Profile</a>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($applicant['github'])): ?>
    <div class="detail-row">
        <strong>GitHub:</strong> 
        <a href="https://github.com/<?php echo htmlspecialchars($applicant['github']); ?>" target="_blank"><?php echo htmlspecialchars($applicant['github']); ?></a>
    </div>
    <?php endif; ?>
    
    <div class="detail-row">
        <strong>Work Experience:</strong>
        <div class="detail-content"><?php echo nl2br(htmlspecialchars($applicant['experience'])); ?></div>
    </div>
    
    <div class="detail-row">
        <strong>Skills:</strong>
        <div class="skills-list">
            <?php
            $skills = explode(',', $applicant['skills']);
            foreach ($skills as $skill) {
                echo '<span class="skill-tag">' . htmlspecialchars(trim($skill)) . '</span>';
            }
            ?>
        </div>
    </div>
    
    <div class="detail-row">
        <strong>Education:</strong>
        <div class="detail-content"><?php echo nl2br(htmlspecialchars($applicant['education'])); ?></div>
    </div>
    
    <?php if (!empty($applicant['cv_path'])): ?>
    <div class="detail-row">
        <strong>CV:</strong>
        <a href="<?php echo htmlspecialchars($applicant['cv_path']); ?>" class="btn-view" target="_blank">View CV</a>
    </div>
    <?php endif; ?>
    
    <div class="detail-row">
        <strong>Application Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($applicant['application_date'])); ?>
    </div>
</div>