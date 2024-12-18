<?php
// Enable error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection with improved security
class DatabaseConnection {
    private $connection;

    public function __construct() {
        // Use environment variables or a secure configuration file in a real-world scenario
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "php_employee_management";

        // Use prepared statements to prevent SQL injection
        try {
            $this->connection = new mysqli($servername, $username, $password, $database);
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
        } catch (Exception $e) {
            // Log error (in a real application, use proper logging)
            error_log($e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }

    public function insertEmployee($name, $email, $phone, $address) {
        // Use prepared statement for secure insertion
        $stmt = $this->connection->prepare("INSERT INTO employee (name, email, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $address);
        
        if ($stmt->execute()) {
            return true;
        } else {
            // Log error (in a real application, use proper logging)
            error_log("Insert error: " . $stmt->error);
            return false;
        }
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Input validation and sanitization
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables
$name = $email = $phone = $address = "";
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $name = validateInput($_POST['name'] ?? '');
    $email = validateInput($_POST['email'] ?? '');
    $phone = validateInput($_POST['phone'] ?? '');
    $address = validateInput($_POST['address'] ?? '');

    // Comprehensive validation
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (strlen($name) < 2 || strlen($name) > 50) {
        $errors[] = "Name must be between 2 and 50 characters";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be 10 digits";
    }

    if (empty($address)) {
        $errors[] = "Address is required";
    } elseif (strlen($address) < 5 || strlen($address) > 200) {
        $errors[] = "Address must be between 5 and 200 characters";
    }

    // If no errors, proceed with insertion
    if (empty($errors)) {
        try {
            $db = new DatabaseConnection();
            if ($db->insertEmployee($name, $email, $phone, $address)) {
                // Redirect to prevent form resubmission
                header("Location: index.php?success=1");
                exit();
            } else {
                $errors[] = "Failed to add employee. Please try again.";
            }
        } catch (Exception $e) {
            $errors[] = "An unexpected error occurred: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management | New Employee</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Responsive and improved styling */
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
        }

        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .custom-navbar {
            background-color: #748EC6;
        }

        /* Enhanced responsiveness */
        @media (max-width: 768px) {
            .form-container {
                padding: 1rem;
            }

            .col-form-label {
                text-align: left;
                margin-bottom: 0.5rem;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn-container > * {
                margin-bottom: 0.5rem;
                width: 100%;
            }
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">Employee Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="create-new-employee.php">Add Employee</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="form-container">
                    <h2 class="text-center mb-4">Add New Employee</h2>

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Employee Form -->
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($name); ?>" 
                                   required minlength="2" maxlength="50">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($phone); ?>" 
                                   required pattern="[0-9]{10}">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($address); ?>" 
                                   required minlength="5" maxlength="200">
                        </div>

                        <div class="d-flex btn-container justify-content-between">
                            <button type="submit" class="btn btn-primary flex-grow-1 me-2">Submit</button>
                            <a href="index.php" class="btn btn-secondary flex-grow-1">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>