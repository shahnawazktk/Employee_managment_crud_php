<?php
// Enable error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection class with improved security
class DatabaseConnection {
    private $connection;

    public function __construct() {
        // Use environment variables or a secure configuration file in a real-world scenario
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "php_employee_management";

        try {
            $this->connection = new mysqli($servername, $username, $password, $database);
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

            // Set charset to prevent potential encoding issues
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            // Log error (in a real application, use proper logging)
            error_log($e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }

    public function getAllEmployees() {
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $this->connection->prepare("SELECT * FROM employee ORDER BY id DESC");
            $stmt->execute();
            $result = $stmt->get_result();

            if (!$result) {
                throw new Exception("Query failed: " . $this->connection->error);
            }

            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Fetch employees
$employees = null;
$errorMessage = "";

try {
    $db = new DatabaseConnection();
    $employees = $db->getAllEmployees();

    if ($employees === false) {
        $errorMessage = "Unable to retrieve employee data.";
    }
} catch (Exception $e) {
    $errorMessage = "An error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Responsive and improved styling */
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
        }

        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .custom-navbar {
            background-color: #343a40;
        }

        /* Enhanced responsiveness for table */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .table-container {
                padding: 1rem;
            }
        }

        /* Action buttons styling */
        .table-actions .btn {
            margin: 0 0.25rem 0.25rem 0;
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Responsive Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">Employee Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create-new-employee.php">Add Employee</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Employee List</h2>
                        <a href="create-new-employee.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add New Employee
                        </a>
                    </div>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($employees && $employees->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $employees->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                            <td class="table-actions">
                                                <a href="edit-employee.php?id=<?php echo urlencode($row['id']); ?>" 
                                                   class="btn btn-primary btn-sm">Edit</a>
                                                <a href="delete-employee.php?id=<?php echo urlencode($row['id']); ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <h4>No Employees Found</h4>
                            <p>Click "Add New Employee" to get started.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>