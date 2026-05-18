<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
declare(strict_types=1);
session_start();
require_once __DIR__ . "/db.php";

function esc(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function set_flash(string $message, string $type = "success"): void
{
    $_SESSION["flash"] = ["message" => $message, "type" => $type];
}

function get_flash(): ?array
{
    if (!isset($_SESSION["flash"])) {
        return null;
    }
    $flash = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    return $flash;
}

if (isset($_GET["logout"])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

if (isset($_POST["action"]) && $_POST["action"] === "login") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $userType = $_POST["user_type"] ?? "admin";

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);

    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    if ($user && $password === $user['password_hash']) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $userType;

        header("Location: index.php?page=dashboard");
        exit;
    }

    set_flash("Invalid login credentials", "error");
    header("Location: index.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add_course") {
        $code = strtoupper(trim($_POST["course_code"] ?? ""));
        $name = trim($_POST["course_name"] ?? "");
        $semester = (int)($_POST["semester"] ?? 0);

        if ($code !== "" && $name !== "" && $semester > 0) {
            $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, semester) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $code, $name, $semester);
            $stmt->execute();
            $stmt->close();
            set_flash("Course created successfully.");
        } else {
            set_flash("Please provide valid course details.", "error");
        }
        header("Location: index.php?page=courses");
        exit;
    }

    if ($action === "update_course") {
        $id = (int)($_POST["course_id"] ?? 0);
        $code = strtoupper(trim($_POST["course_code"] ?? ""));
        $name = trim($_POST["course_name"] ?? "");
        $semester = (int)($_POST["semester"] ?? 0);

        if ($id > 0 && $code !== "" && $name !== "" && $semester > 0) {
            $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, semester = ? WHERE id = ?");
            $stmt->bind_param("ssii", $code, $name, $semester, $id);
            $stmt->execute();
            $stmt->close();
            set_flash("Course updated successfully.");
        } else {
            set_flash("Invalid course update data.", "error");
        }
        header("Location: index.php?page=courses");
        exit;
    }

    if ($action === "delete_course") {
        $id = (int)($_POST["course_id"] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            set_flash("Course deleted successfully.");
        }
        header("Location: index.php?page=courses");
        exit;
    }

    if ($action === "add_co") {
        $courseId = (int)($_POST["course_id"] ?? 0);
        $coCode = strtoupper(trim($_POST["co_code"] ?? ""));
        $description = trim($_POST["description"] ?? "");
        $target = (float)($_POST["target_percentage"] ?? 0);

        if ($courseId > 0 && $coCode !== "" && $description !== "" && $target > 0) {
            $stmt = $conn->prepare("INSERT INTO cos (course_id, co_code, description, target_percentage) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $courseId, $coCode, $description, $target);
            $stmt->execute();
            $stmt->close();
            set_flash("CO created successfully.");
        } else {
            set_flash("Please provide valid CO details.", "error");
        }
        header("Location: index.php?page=cos");
        exit;
    }

    if ($action === "update_co") {
        $id = (int)($_POST["co_id"] ?? 0);
        $courseId = (int)($_POST["course_id"] ?? 0);
        $coCode = strtoupper(trim($_POST["co_code"] ?? ""));
        $description = trim($_POST["description"] ?? "");
        $target = (float)($_POST["target_percentage"] ?? 0);

        if ($id > 0 && $courseId > 0 && $coCode !== "" && $description !== "" && $target > 0) {
            $stmt = $conn->prepare("UPDATE cos SET course_id = ?, co_code = ?, description = ?, target_percentage = ? WHERE id = ?");
            $stmt->bind_param("issdi", $courseId, $coCode, $description, $target, $id);
            $stmt->execute();
            $stmt->close();
            set_flash("CO updated successfully.");
        } else {
            set_flash("Invalid CO update data.", "error");
        }
        header("Location: index.php?page=cos");
        exit;
    }

    if ($action === "delete_co") {
        $id = (int)($_POST["co_id"] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM cos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            set_flash("CO deleted successfully.");
        }
        header("Location: index.php?page=cos");
        exit;
    }

    if ($action === "add_question") {
        $courseId = (int)($_POST["course_id"] ?? 0);
        $coId = (int)($_POST["co_id"] ?? 0);
        $text = trim($_POST["question_text"] ?? "");
        $maxMarks = (int)($_POST["max_marks"] ?? 0);

        if ($courseId > 0 && $coId > 0 && $text !== "" && $maxMarks > 0) {
            $stmt = $conn->prepare("INSERT INTO questions (course_id, co_id, question_text, max_marks) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $courseId, $coId, $text, $maxMarks);
            $stmt->execute();
            $stmt->close();
            set_flash("Question added successfully.");
        } else {
            set_flash("Please provide valid question details.", "error");
        }
        header("Location: index.php?page=questions");
        exit;
    }

    if ($action === "add_mark") {
        $studentRoll = trim($_POST["student_roll"] ?? "");
        $questionId = (int)($_POST["question_id"] ?? 0);
        $obtained = (float)($_POST["obtained_marks"] ?? 0);

        if ($studentRoll !== "" && $questionId > 0 && $obtained >= 0) {
            $stmt = $conn->prepare("INSERT INTO marks (student_roll, question_id, obtained_marks) VALUES (?, ?, ?)");
            $stmt->bind_param("sid", $studentRoll, $questionId, $obtained);
            $stmt->execute();
            $stmt->close();
            set_flash("Marks saved successfully.");
        } else {
            set_flash("Invalid marks entry.", "error");
        }
        header("Location: index.php?page=marks");
        exit;
    }

    if ($action === "add_mapping") {
        $coId = (int)($_POST["co_id"] ?? 0);
        $poNumber = (int)($_POST["po_number"] ?? 0);
        $level = (int)($_POST["mapping_level"] ?? 0);

        if ($coId > 0 && $poNumber > 0 && $poNumber <= 12 && $level >= 1 && $level <= 3) {
            $stmt = $conn->prepare("INSERT INTO co_po_mapping (co_id, po_number, mapping_level) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE mapping_level = VALUES(mapping_level)");
            $stmt->bind_param("iii", $coId, $poNumber, $level);
            $stmt->execute();
            $stmt->close();
            set_flash("CO-PO mapping saved.");
        } else {
            set_flash("Invalid mapping values.", "error");
        }
        header("Location: index.php?page=mapping");
        exit;
    }
}

$isLoggedIn = isset($_SESSION["user_id"]);
$page = $_GET["page"] ?? "dashboard";
$role = $_SESSION['role'] ?? '';

if ($role === 'teacher') {

    $allowedPages = ['dashboard', 'cos', 'questions', 'marks', 'reports'];

    if (!in_array($page, $allowedPages)) {
        die('Access Denied');
    }
}

if ($role === 'student') {

    $allowedPages = ['reports', 'dashboard'];

    if (!in_array($page, $allowedPages)) {
        die('Access Denied');
    }
}



if (!$isLoggedIn) {
    $flash = get_flash();
    ?> 
  <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CO-PO Attainment System | Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="style.css?v=5">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="auth-body">

<div class="auth-wrapper">
    <div class="auth-card">

        <h1>CO-PO Attainment System</h1>
       
        <p class="subtitle">Tezpur University</p>

        <form method="POST" class="auth-form">

            <input type="hidden" name="action" value="login">

            <label>User Type</label>
            <select name="user_type" required>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>

            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required>

            <label>Password</label>

<div class="password-wrapper">
    <input
        type="password"
        id="password"
        name="password"
        placeholder="Enter password"
        required
    >
    <span class="toggle-password" onclick="togglePassword()">👁️</span>
</div>

            <button type="submit">Login</button>

        </form>

    </div>
</div>
<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.querySelector(".toggle-password");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.textContent = "🙈";
    } else {
        passwordInput.type = "password";
        toggleIcon.textContent = "👁️";
    }
}
</script>

</body>
</html>
<?php
exit;
}





$flash = get_flash();

$courseCount = (int)$conn->query("SELECT COUNT(*) AS c FROM courses")->fetch_assoc()["c"];
$coCount = (int)$conn->query("SELECT COUNT(*) AS c FROM cos")->fetch_assoc()["c"];
$questionCount = (int)$conn->query("SELECT COUNT(*) AS c FROM questions")->fetch_assoc()["c"];
$marksCount = (int)$conn->query("SELECT COUNT(*) AS c FROM marks")->fetch_assoc()["c"];

$courses = $conn->query("SELECT * FROM courses ORDER BY id DESC");
$courseList = $conn->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code ASC");
$coList = $conn->query("SELECT c.id, c.co_code, c.description, cr.course_code FROM cos c JOIN courses cr ON c.course_id = cr.id ORDER BY cr.course_code, c.co_code");
$questionList = $conn->query("SELECT q.id, q.question_text, q.max_marks, cr.course_code, c.co_code FROM questions q JOIN courses cr ON q.course_id = cr.id JOIN cos c ON q.co_id = c.id ORDER BY q.id DESC");
$marksList = $conn->query("SELECT m.id, m.student_roll, m.obtained_marks, q.max_marks, q.question_text FROM marks m JOIN questions q ON m.question_id = q.id ORDER BY m.id DESC");
$mappingList = $conn->query("SELECT mp.id, c.co_code, cr.course_code, mp.po_number, mp.mapping_level FROM co_po_mapping mp JOIN cos c ON mp.co_id = c.id JOIN courses cr ON c.course_id = cr.id ORDER BY cr.course_code, c.co_code, mp.po_number");

$activePage = in_array($page, ["dashboard", "courses", "cos", "questions", "marks", "mapping", "reports"], true) ? $page : "dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CO-PO Attainment System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <h2>CO-PO System</h2>
            <p>Tezpur University</p>
        </div>
        <nav>
        <?php if ($_SESSION["role"] === "admin"): ?>
            <a class="<?= $activePage === 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
    <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

<a class="<?= $activePage === "courses" ? "active" : ""; ?>" href="index.php?page=courses">Course Management</a>

<a class="<?= $activePage === "cos" ? "active" : ""; ?>" href="index.php?page=cos">CO Management</a>

<a class="<?= $activePage === "questions" ? "active" : ""; ?>" href="index.php?page=questions">Questions</a>

<a class="<?= $activePage === "marks" ? "active" : ""; ?>" href="index.php?page=marks">Marks Entry</a>

<a class="<?= $activePage === "mapping" ? "active" : ""; ?>" href="index.php?page=mapping">CO-PO Mapping</a>

<a class="<?= $activePage === "reports" ? "active" : ""; ?>" href="index.php?page=reports">Reports</a>

<?php elseif ($_SESSION["role"] === "teacher"): ?>
    <a class="<?= $activePage === 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
    <i class="fa-solid fa-gauge"></i> Dashboard
</a>
  <a class="<?= $activePage === "courses" ? "active" : ""; ?>" href="index.php?page=courses">Course Management</a>
  <a class="<?= $activePage === "cos" ? "active" : ""; ?>" href="index.php?page=cos">CO Management</a>

<a class="<?= $activePage === "questions" ? "active" : ""; ?>" href="index.php?page=questions">Questions</a>

<a class="<?= $activePage === "marks" ? "active" : ""; ?>" href="index.php?page=marks">Marks Entry</a>

<a class="<?= $activePage === "reports" ? "active" : ""; ?>" href="index.php?page=reports">Reports</a>


<?php elseif ($_SESSION["role"] === "student"): ?>
    <a class="<?= $activePage === 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
    <i class="fa-solid fa-gauge"></i> Dashboard
</a>
<a class="<?= $activePage === "reports" ? "active" : ""; ?>" href="index.php?page=reports">Reports</a>

<?php endif; ?>
        </nav>
        <a href="index.php?logout=1" class="logout-btn">
    <i class="fa-solid fa-right-from-bracket"></i> Logout
</a>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <button id="menuToggle" class="menu-btn" type="button">☰</button>
            <div>
                <h1><?= esc(ucfirst($activePage)); ?></h1>
                <p>Welcome, <?= esc($_SESSION["admin_username"] ?? "Admin"); ?></p>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="alert <?= esc($flash["type"]); ?>"><?= esc($flash["message"]); ?></div>
        <?php endif; ?>

        <?php if ($activePage === "dashboard"): ?>
            <section class="card-grid">
                <article class="card stat-card"><h3>Total Courses</h3><p><?= $courseCount; ?></p></article>
                <article class="card stat-card"><h3>Total COs</h3><p><?= $coCount; ?></p></article>
                <article class="card stat-card"><h3>Total Questions</h3><p><?= $questionCount; ?></p></article>
                <article class="card stat-card"><h3>Total Marks Entries</h3><p><?= $marksCount; ?></p></article>
            </section>
            <section class="card">
                <h2>Quick Overview</h2>
                <p>Use the sidebar to manage courses, map COs to POs, enter marks, and review attainment reports.</p>
            </section>
        <?php endif; ?>

        <?php if ($activePage === "courses"): ?>
            <section class="card">
                <h2>Add Course</h2>
                <form method="post" class="grid-form">
                    <input type="hidden" name="action" value="add_course">
                    <input type="text" name="course_code" placeholder="Course Code (e.g. CS301)" required>
                    <input type="text" name="course_name" placeholder="Course Name" required>
                    <input type="number" name="semester" min="1" max="8" placeholder="Semester" required>
                    <button type="submit">Save Course</button>
                </form>
            </section>
            <section class="card">
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Code</th><th>Name</th><th>Semester</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php while ($row = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?= esc($row["course_code"]); ?></td>
                                <td><?= esc($row["course_name"]); ?></td>
                                <td><?= (int)$row["semester"]; ?></td>
                                <td>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="update_course">
                                        <input type="hidden" name="course_id" value="<?= (int)$row["id"]; ?>">
                                        <input type="text" name="course_code" value="<?= esc($row["course_code"]); ?>" required>
                                        <input type="text" name="course_name" value="<?= esc($row["course_name"]); ?>" required>
                                        <input type="number" name="semester" min="1" max="8" value="<?= (int)$row["semester"]; ?>" required>
                                        <button type="submit" class="btn-update">
                                        <i class="fa-solid fa-pen"></i> Update
                                        </button>
                                    </form>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="delete_course">
                                        <input type="hidden" name="course_id" value="<?= (int)$row["id"]; ?>">
                                        <button type="submit"
        class="btn-delete"
        onclick="return confirm('Delete this course?')">
    <i class="fa-solid fa-trash"></i> Delete
</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($activePage === "cos"): ?>
            <section class="card">
                <h2>Add CO</h2>
                <form method="post" class="grid-form">
                    <input type="hidden" name="action" value="add_co">
                    <select name="course_id" required>
                        <option value="">Select Course</option>
                        <?php while ($course = $courseList->fetch_assoc()): ?>
                            <option value="<?= (int)$course["id"]; ?>"><?= esc($course["course_code"] . " - " . $course["course_name"]); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="text" name="co_code" placeholder="CO Code (e.g. CO1)" required>
                    <input type="text" name="description" placeholder="CO Description" required>
                    <input type="number" step="0.01" name="target_percentage" placeholder="Target %" required>
                    <button type="submit">Save CO</button>
                </form>
            </section>
            <section class="card">
                <h2>CO List</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Course</th><th>CO</th><th>Description</th><th>Target%</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php
                        $coTable = $conn->query("SELECT c.*, cr.course_code, cr.course_name FROM cos c JOIN courses cr ON c.course_id = cr.id ORDER BY c.id DESC");
                        while ($row = $coTable->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= esc($row["course_code"]); ?></td>
                                <td><?= esc($row["co_code"]); ?></td>
                                <td><?= esc($row["description"]); ?></td>
                                <td><?= esc($row["target_percentage"]); ?></td>
                                <td>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="update_co">
                                        <input type="hidden" name="co_id" value="<?= (int)$row["id"]; ?>">
                                        <select name="course_id" required>
                                            <?php
                                            $coursesForEdit = $conn->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code ASC");
                                            while ($course = $coursesForEdit->fetch_assoc()):
                                                $selected = ((int)$row["course_id"] === (int)$course["id"]) ? "selected" : "";
                                                ?>
                                                <option value="<?= (int)$course["id"]; ?>" <?= $selected; ?>><?= esc($course["course_code"]); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <input type="text" name="co_code" value="<?= esc($row["co_code"]); ?>" required>
                                        <input type="text" name="description" value="<?= esc($row["description"]); ?>" required>
                                        <input type="number" step="0.01" name="target_percentage" value="<?= esc($row["target_percentage"]); ?>" required>
                                        <button type="submit" class="secondary">Update</button>
                                    </form>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="delete_co">
                                        <input type="hidden" name="co_id" value="<?= (int)$row["id"]; ?>">
                                        <button type="submit" class="danger" onclick="return confirm('Delete this CO?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($activePage === "questions"): ?>
            <section class="card">
                <h2>Add Question</h2>
                <form method="post" class="grid-form">
                    <input type="hidden" name="action" value="add_question">
                    <select name="course_id" required>
                        <option value="">Select Course</option>
                        <?php
                        $allCourses = $conn->query("SELECT id, course_code FROM courses ORDER BY course_code ASC");
                        while ($course = $allCourses->fetch_assoc()):
                            ?>
                            <option value="<?= (int)$course["id"]; ?>"><?= esc($course["course_code"]); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="co_id" required>
                        <option value="">Select CO</option>
                        <?php
                        $allCos = $conn->query("SELECT id, co_code FROM cos ORDER BY co_code ASC");
                        while ($co = $allCos->fetch_assoc()):
                            ?>
                            <option value="<?= (int)$co["id"]; ?>"><?= esc($co["co_code"]); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="text" name="question_text" placeholder="Question text" required>
                    <input type="number" name="max_marks" min="1" placeholder="Max marks" required>
                    <button type="submit">Add Question</button>
                </form>
            </section>
            <section class="card">
                <h2>Question Bank</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Course</th><th>CO</th><th>Question</th><th>Max Marks</th></tr></thead>
                        <tbody>
                        <?php while ($row = $questionList->fetch_assoc()): ?>
                            <tr>
                                <td><?= esc($row["course_code"]); ?></td>
                                <td><?= esc($row["co_code"]); ?></td>
                                <td><?= esc($row["question_text"]); ?></td>
                                <td><?= (int)$row["max_marks"]; ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($activePage === "marks"): ?>
            <section class="card">
                <h2>Enter Marks</h2>
                <form method="post" class="grid-form">
                    <input type="hidden" name="action" value="add_mark">
                    <input type="text" name="student_roll" placeholder="Student Roll No" required>
                    <select name="question_id" required>
                        <option value="">Select Question</option>
                        <?php
                        $qForMarks = $conn->query("SELECT id, question_text FROM questions ORDER BY id DESC");
                        while ($q = $qForMarks->fetch_assoc()):
                            ?>
                            <option value="<?= (int)$q["id"]; ?>">Q<?= (int)$q["id"]; ?> - <?= esc($q["question_text"]); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="number" step="0.01" name="obtained_marks" min="0" placeholder="Obtained Marks" required>
                    <button type="submit">Save Marks</button>
                </form>
            </section>
            <section class="card">
                <h2>Marks Records</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Student</th><th>Question</th><th>Obtained</th><th>Max</th><th>%</th></tr></thead>
                        <tbody>
                        <?php while ($row = $marksList->fetch_assoc()): ?>
                            <?php $percentage = $row["max_marks"] > 0 ? (($row["obtained_marks"] / $row["max_marks"]) * 100) : 0; ?>
                            <tr>
                                <td><?= esc($row["student_roll"]); ?></td>
                                <td><?= esc($row["question_text"]); ?></td>
                                <td><?= esc((string)$row["obtained_marks"]); ?></td>
                                <td><?= esc((string)$row["max_marks"]); ?></td>
                                <td><?= number_format($percentage, 2); ?>%</td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($activePage === "mapping"): ?>
            <section class="card">
                <h2>Map CO to PO</h2>
                <form method="post" class="grid-form">
                    <input type="hidden" name="action" value="add_mapping">
                    <select name="co_id" required>
                        <option value="">Select CO</option>
                        <?php
                        $cosForMap = $conn->query("SELECT id, co_code FROM cos ORDER BY co_code ASC");
                        while ($co = $cosForMap->fetch_assoc()):
                            ?>
                            <option value="<?= (int)$co["id"]; ?>"><?= esc($co["co_code"]); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="po_number" required>
                        <option value="">Select PO</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i; ?>">PO<?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="mapping_level" required>
                        <option value="">Level (1-3)</option>
                        <option value="1">1 - Low</option>
                        <option value="2">2 - Medium</option>
                        <option value="3">3 - High</option>
                    </select>
                    <button type="submit">Save Mapping</button>
                </form>
            </section>
            <section class="card">
                <h2>Mapping Matrix</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Course</th><th>CO</th><th>PO</th><th>Level</th></tr></thead>
                        <tbody>
                        <?php while ($row = $mappingList->fetch_assoc()): ?>
                            <tr>
                                <td><?= esc($row["course_code"]); ?></td>
                                <td><?= esc($row["co_code"]); ?></td>
                                <td>PO<?= (int)$row["po_number"]; ?></td>
                                <td><?= (int)$row["mapping_level"]; ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($activePage === "reports"): ?>
            <section class="card">
                <h2>CO Attainment Report</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Course</th><th>CO</th><th>Target%</th><th>Achieved%</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php
                        $reportQuery = "
                            SELECT
                                cr.course_code,
                                c.co_code,
                                c.target_percentage,
                                ROUND(AVG((m.obtained_marks / q.max_marks) * 100), 2) AS achieved_percentage
                            FROM cos c
                            JOIN courses cr ON c.course_id = cr.id
                            LEFT JOIN questions q ON q.co_id = c.id
                            LEFT JOIN marks m ON m.question_id = q.id
                            GROUP BY c.id
                            ORDER BY cr.course_code, c.co_code
                        ";
                        $reportRows = $conn->query($reportQuery);
                        while ($row = $reportRows->fetch_assoc()):
                            $achieved = (float)($row["achieved_percentage"] ?? 0);
                            $target = (float)$row["target_percentage"];
                            $status = $achieved >= $target ? "Attained" : "Below Target";
                            ?>
                            <tr>
                                <td><?= esc($row["course_code"]); ?></td>
                                <td><?= esc($row["co_code"]); ?></td>
                                <td><?= number_format($target, 2); ?>%</td>
                                <td><?= number_format($achieved, 2); ?>%</td>
                                <td><span class="badge <?= $status === "Attained" ? "success" : "error"; ?>"><?= esc($status); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>
<script src="script.js"></script>
</body>
</html>
