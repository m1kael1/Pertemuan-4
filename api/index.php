<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, PUT, DELETE, GET');
header('Access-Control-Allow-Headers: Content-Type');

include_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Route matching
if ($uri === 'api/todos' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    getTasks();
} elseif ($uri === 'api/todos' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    createTask();
} elseif ($uri === 'api/todos' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    completeTask();
} elseif ($uri === 'api/todos' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    deleteTask();
} else {
    echo json_encode(['message' => 'Invalid Request']);
}

// Create a new Task
function createTask()
{
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->title)) {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO todos (title) VALUES (?)");
        $stmt->bind_param('s', $data->title);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Task Created']);
        } else {
            echo json_encode(['message' => 'Task Not Created']);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['message' => 'Incomplete Data']);
    }
}

// Mark a Task as Completed
function completeTask()
{
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->id)) {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE todos SET completed = 1 WHERE id = ?");
        $stmt->bind_param('i', $data->id);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Task Completed']);
        } else {
            echo json_encode(['message' => 'Task Not Completed']);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['message' => 'Invalid ID']);
    }
}

// Delete a Task
function deleteTask()
{
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->id)) {
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->bind_param('i', $data->id);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Task Deleted']);
        } else {
            echo json_encode(['message' => 'Task Not Deleted']);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['message' => 'Invalid ID']);
    }
}

// Get Tasks (Retrieve all or one by ID)
function getTasks()
{
    $conn = getConnection();

    // Check if task ID is provided in the query string
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM todos WHERE id = ?");
        $stmt->bind_param('i', $id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM todos");
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // If there are results, fetch them and return as JSON
    if ($result->num_rows > 0) {
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        echo json_encode($tasks);
    } else {
        echo json_encode(['message' => 'No Tasks Found']);
    }

    $stmt->close();
    $conn->close();
}
