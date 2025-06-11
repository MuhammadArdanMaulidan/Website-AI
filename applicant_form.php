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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi dan sanitasi input
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $linkedin = $conn->real_escape_string(trim($_POST['linkedin']));
    $github = $conn->real_escape_string(trim($_POST['github']));
    $experience = $conn->real_escape_string(trim($_POST['experience']));
    $skills = $conn->real_escape_string(trim($_POST['skills']));
    $education = $conn->real_escape_string(trim($_POST['education']));
    
    // Validasi field wajib
    if (empty($name) || empty($email) || empty($phone) || empty($experience) || empty($skills) || empty($education)) {
        $error = "Please fill all required fields!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Handle file upload
        $cv_path = '';
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf'];
            $file_type = $_FILES['cv']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $target_dir = "uploads/cv/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_ext = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.' . $file_ext;
                $target_file = $target_dir . $file_name;
                
                if (move_uploaded_file($_FILES['cv']['tmp_name'], $target_file)) {
                    $cv_path = $target_file;
                } else {
                    $error = "Failed to upload CV file.";
                }
            } else {
                $error = "Only PDF files are allowed for CV.";
            }
        } else {
            $error = "CV file is required.";
        }
        
        if (empty($error)) {
            $sql = "INSERT INTO applicants (name, email, phone, linkedin, github, experience, skills, education, cv_path, application_date)
                    VALUES ('$name', '$email', '$phone', '$linkedin', '$github', '$experience', '$skills', '$education', '$cv_path', NOW())";
            
            if ($conn->query($sql)) {
                $success = "Application submitted successfully!";
                // Reset form values
                $_POST = array();
            } else {
                $error = "Error submitting application: " . $conn->error;
                // Delete uploaded file if there was an error
                if (!empty($cv_path) && file_exists($cv_path)) {
                    unlink($cv_path);
                }
            }
        }
    }
}
?>

<div class="content-header">
    <h1>Applicant Form</h1>
</div>

<div class="form-container">
    <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Full Name <span class="required">*</span></label>
            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="linkedin">LinkedIn Profile</label>
                <input type="url" id="linkedin" name="linkedin" value="<?php echo isset($_POST['linkedin']) ? htmlspecialchars($_POST['linkedin']) : ''; ?>" placeholder="https://linkedin.com/in/username">
            </div>
            <div class="form-group">
                <label for="github">GitHub Username</label>
                <input type="text" id="github" name="github" value="<?php echo isset($_POST['github']) ? htmlspecialchars($_POST['github']) : ''; ?>" placeholder="username only (no URL)">
            </div>
        </div>
        
        <div class="form-group">
            <label for="experience">Work Experience <span class="required">*</span></label>
            <textarea id="experience" name="experience" rows="4" required><?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="skills">Skills <span class="required">*</span></label>
            <textarea id="skills" name="skills" rows="3" required><?php echo isset($_POST['skills']) ? htmlspecialchars($_POST['skills']) : ''; ?></textarea>
            <small>Separate skills with commas (e.g., PHP, JavaScript, HTML)</small>
        </div>
        
        <div class="form-group">
            <label for="education">Education <span class="required">*</span></label>
            <textarea id="education" name="education" rows="3" required><?php echo isset($_POST['education']) ? htmlspecialchars($_POST['education']) : ''; ?></textarea>
            <small>Include university name and degree (e.g., Bachelor of Computer Science, University of Indonesia)</small>
        </div>
        
        <div class="form-group">
            <label for="cv">Upload CV (PDF only) <span class="required">*</span></label>
            <input type="file" id="cv" name="cv" accept=".pdf" required>
            <small>Maximum file size: 2MB</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Submit Application</button>
            <button type="reset" class="btn btn-secondary">Reset Form</button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>