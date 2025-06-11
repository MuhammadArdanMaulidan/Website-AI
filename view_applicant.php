<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);
$applicant = null;

$result = $conn->query("SELECT * FROM applicants WHERE id = $id");
if ($result && $result->num_rows > 0) {
    $applicant = $result->fetch_assoc();
} else {
    header("Location: dashboard.php");
    exit();
}
?>

<div class="content-header">
    <h1>Applicant Details</h1>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<div class="applicant-details">
    <div class="detail-card">
        <h2>Personal Information</h2>
        <div class="detail-row">
            <span class="detail-label">Name:</span>
            <span class="detail-value"><?php echo htmlspecialchars($applicant['name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($applicant['email']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Phone:</span>
            <span class="detail-value"><?php echo htmlspecialchars($applicant['phone']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">LinkedIn:</span>
            <span class="detail-value">
                <?php if (!empty($applicant['linkedin'])): ?>
                    <a href="<?php echo htmlspecialchars($applicant['linkedin']); ?>" target="_blank">View Profile</a>
                <?php else: ?>
                    Not provided
                <?php endif; ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">GitHub:</span>
            <span class="detail-value">
                <?php if (!empty($applicant['github'])): ?>
                    <a href="https://github.com/<?php echo htmlspecialchars($applicant['github']); ?>" target="_blank"><?php echo htmlspecialchars($applicant['github']); ?></a>
                <?php else: ?>
                    Not provided
                <?php endif; ?>
            </span>
        </div>
    </div>

    <div class="detail-card">
        <h2>Work Experience</h2>
        <div class="detail-full">
            <?php echo nl2br(htmlspecialchars($applicant['experience'])); ?>
        </div>
    </div>

    <div class="detail-card">
        <h2>Skills</h2>
        <div class="skills-list">
            <?php
            $skills = explode(',', $applicant['skills']);
            foreach ($skills as $skill) {
                echo '<span class="skill-tag">' . htmlspecialchars(trim($skill)) . '</span>';
            }
            ?>
        </div>
    </div>

    <div class="detail-card">
        <h2>Education</h2>
        <div class="detail-full">
            <?php echo nl2br(htmlspecialchars($applicant['education'])); ?>
        </div>
    </div>

    <div class="detail-card">
        <h2>CV</h2>
        <div class="detail-row">
            <?php if (!empty($applicant['cv_path'])): ?>
                <a href="<?php echo htmlspecialchars($applicant['cv_path']); ?>" class="btn btn-primary" target="_blank">View CV</a>
            <?php else: ?>
                <span class="detail-value">No CV uploaded</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-card">
        <h2>Application Date</h2>
        <div class="detail-row">
            <span class="detail-value"><?php echo date('F j, Y, g:i a', strtotime($applicant['application_date'])); ?></span>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>