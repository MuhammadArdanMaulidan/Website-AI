<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

require_once 'includes/config.php';

// Get employee count
$employee_count = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
if ($result) {
    $row = $result->fetch_assoc();
    $employee_count = $row['count'];
}

// Get applicant count
$applicant_count = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM applicants");
if ($result) {
    $row = $result->fetch_assoc();
    $applicant_count = $row['count'];
}

// Handle applicant search
$search_applicant = isset($_GET['search_applicant']) ? $conn->real_escape_string($_GET['search_applicant']) : '';
$applicant_where = $search_applicant ? "WHERE name LIKE '%$search_applicant%'" : '';

// Get recent applicants
$recent_applicants = [];
$result = $conn->query("SELECT * FROM applicants $applicant_where ORDER BY application_date DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_applicants[] = $row;
    }
}

// Handle employee search
$search_employee = isset($_GET['search_employee']) ? $conn->real_escape_string($_GET['search_employee']) : '';
$employee_where = $search_employee ? "WHERE status = 'active' AND name LIKE '%$search_employee%'" : "WHERE status = 'active'";

// Get active employees
$active_employees = [];
$result = $conn->query("SELECT * FROM employees $employee_where ORDER BY name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $active_employees[] = $row;
    }
}

// Handle delete applicant
if (isset($_GET['delete_applicant'])) {
    $id = intval($_GET['delete_applicant']);
    $conn->query("DELETE FROM applicants WHERE id = $id");
    header("Location: dashboard.php");
    exit();
}

// Handle CRUD for employees
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_employee'])) {
        // Add new employee
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $position = $conn->real_escape_string($_POST['position']);
        $department = $conn->real_escape_string($_POST['department']);
        $hire_date = $conn->real_escape_string($_POST['hire_date']);
        
        // Check if email already exists
        $check = $conn->query("SELECT id FROM employees WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            $sql = "INSERT INTO employees (name, email, phone, position, department, hire_date, status) 
                    VALUES ('$name', '$email', '$phone', '$position', '$department', '$hire_date', 'active')";
            if ($conn->query($sql)) {
                header("Location: dashboard.php?success=Employee added successfully");
                exit();
            } else {
                $error = "Error adding employee: " . $conn->error;
            }
        }
    } elseif (isset($_POST['update_employee'])) {
        // Update employee
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $position = $conn->real_escape_string($_POST['position']);
        $department = $conn->real_escape_string($_POST['department']);
        $hire_date = $conn->real_escape_string($_POST['hire_date']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Check if email exists for other employees
        $check = $conn->query("SELECT id FROM employees WHERE email = '$email' AND id != $id");
        if ($check->num_rows > 0) {
            $error = "Email already exists for another employee!";
        } else {
            $sql = "UPDATE employees SET 
                    name = '$name',
                    email = '$email',
                    phone = '$phone',
                    position = '$position',
                    department = '$department',
                    hire_date = '$hire_date',
                    status = '$status'
                    WHERE id = $id";
            if ($conn->query($sql)) {
                header("Location: dashboard.php?success=Employee updated successfully");
                exit();
            } else {
                $error = "Error updating employee: " . $conn->error;
            }
        }
    }
}

// Handle delete employee
if (isset($_GET['delete_employee'])) {
    $id = intval($_GET['delete_employee']);
    $conn->query("UPDATE employees SET status = 'inactive' WHERE id = $id");
    header("Location: dashboard.php?success=Employee deactivated successfully");
    exit();
}
?>

<div class="content-header">
    <h1>Dashboard</h1>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Active Employees</h3>
        <p><?php echo $employee_count; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Applicants</h3>
        <p><?php echo $applicant_count; ?></p>
    </div>
</div>

<div class="dashboard-sections">
    <div class="section">
        <div class="section-header">
            <h2>Recent Applicants</h2>
            <form method="GET" class="search-form">
                <input type="text" name="search_applicant" placeholder="Search by name..." value="<?php echo htmlspecialchars($search_applicant); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Applied On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_applicants as $applicant): ?>
                <tr>
                    <td><?php echo htmlspecialchars($applicant['name']); ?></td>
                    <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                    <td><?php echo htmlspecialchars($applicant['phone']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($applicant['application_date'])); ?></td>
                    <td class="actions">
                        <button class="btn-view" onclick="showApplicantDetails(<?php echo $applicant['id']; ?>)">View</button>
                        <a href="dashboard.php?delete_applicant=<?php echo $applicant['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this applicant?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Applicant Details Modal -->
        <div id="applicantDetailsModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeApplicantDetails()">&times;</span>
                <h2>Applicant Details</h2>
                <div id="applicantDetailsContent"></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2>Active Employees</h2>
            <div>
                <form method="GET" class="search-form">
                    <input type="text" name="search_employee" placeholder="Search by name..." value="<?php echo htmlspecialchars($search_employee); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <button class="btn btn-primary" onclick="openEmployeeModal()">Add Employee</button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Hire Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($active_employees as $employee): ?>
                <tr>
                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                    <td><?php echo htmlspecialchars($employee['email']); ?></td>
                    <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                    <td><?php echo htmlspecialchars($employee['position']); ?></td>
                    <td><?php echo htmlspecialchars($employee['department']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($employee['hire_date'])); ?></td>
                    <td><span class="status-badge <?php echo $employee['status']; ?>"><?php echo ucfirst($employee['status']); ?></span></td>
                    <td class="actions">
                        <button class="btn-edit" onclick="openEmployeeModal(<?php echo $employee['id']; ?>, '<?php echo htmlspecialchars($employee['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($employee['email'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($employee['phone'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($employee['position'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($employee['department'], ENT_QUOTES); ?>', '<?php echo $employee['hire_date']; ?>', '<?php echo $employee['status']; ?>')">Edit</button>
                        <a href="dashboard.php?delete_employee=<?php echo $employee['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to deactivate this employee?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Employee Modal -->
<div id="employeeModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEmployeeModal()">&times;</span>
        <h2 id="modalTitle">Add New Employee</h2>
        <form method="POST" id="employeeForm">
            <input type="hidden" name="id" id="employeeId">
            <input type="hidden" name="update_employee" id="updateFlag" value="0">
            
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="position">Position *</label>
                    <input type="text" id="position" name="position" required>
                </div>
                <div class="form-group">
                    <label for="department">Department *</label>
                    <input type="text" id="department" name="department" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="hire_date">Hire Date *</label>
                <input type="date" id="hire_date" name="hire_date" required>
            </div>
            
            <div class="form-group" id="statusField" style="display:none;">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_employee" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeEmployeeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Employee Modal Functions
function openEmployeeModal(id = null, name = '', email = '', phone = '', position = '', department = '', hire_date = '', status = 'active') {
    const modal = document.getElementById('employeeModal');
    const title = document.getElementById('modalTitle');
    const form = document.getElementById('employeeForm');
    const updateFlag = document.getElementById('updateFlag');
    const statusField = document.getElementById('statusField');
    
    if (id) {
        title.textContent = 'Edit Employee';
        updateFlag.value = '1';
        document.getElementById('employeeId').value = id;
        document.getElementById('name').value = name;
        document.getElementById('email').value = email;
        document.getElementById('phone').value = phone;
        document.getElementById('position').value = position;
        document.getElementById('department').value = department;
        document.getElementById('hire_date').value = hire_date;
        document.getElementById('status').value = status;
        statusField.style.display = 'block';
    } else {
        title.textContent = 'Add New Employee';
        updateFlag.value = '0';
        form.reset();
        statusField.style.display = 'none';
    }
    
    modal.style.display = 'block';
}

function closeEmployeeModal() {
    document.getElementById('employeeModal').style.display = 'none';
}

// Applicant Details Functions
function showApplicantDetails(id) {
    fetch('get_applicant_details.php?id=' + id)
        .then(response => response.text())
        .then(data => {
            document.getElementById('applicantDetailsContent').innerHTML = data;
            document.getElementById('applicantDetailsModal').style.display = 'block';
        });
}

function closeApplicantDetails() {
    document.getElementById('applicantDetailsModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const employeeModal = document.getElementById('employeeModal');
    const applicantModal = document.getElementById('applicantDetailsModal');
    
    if (event.target == employeeModal) {
        closeEmployeeModal();
    }
    if (event.target == applicantModal) {
        closeApplicantDetails();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>