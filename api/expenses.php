<?php
$hostname = "localhost";
$database = "dbexpense";
$username = "root";
$password = "";

$db = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
http_response_code(404);
$response = new stdClass();

$jsonbody = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle POST request (create new expense)
    try {
        $stmt = $db->prepare("INSERT INTO expenses (amount, `Desc`, dateTime) VALUES 
                              (:amount, :desc, :dateTime)");
        $stmt->execute(array(':amount' => $jsonbody->amount, ':desc' =>
                             $jsonbody->desc, ':dateTime' => $jsonbody->dateTime));
        http_response_code(200);
        $response->message = "Expense created successfully.";
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred: " . $ee->getMessage();
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Handle GET request (retrieve all expenses)
    try {
        $stmt = $db->prepare("SELECT * FROM expenses");
        $stmt->execute();
        $response->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred: " . $ee->getMessage();
    }
} else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Handle PUT request (update existing expense)
    try {
        if ($jsonbody) {
            $stmt = $db->prepare("UPDATE expenses SET amount = :amount, `Desc` = :desc, dateTime = :dateTime WHERE dateTime = :dateTime");
            $stmt->execute(array(
                ':amount' => $jsonbody->amount,
                ':desc' => $jsonbody->desc,
                ':dateTime' => $jsonbody->dateTime
            ));
            http_response_code(200);
            $response->message = "Expense updated successfully.";
        } else {
            http_response_code(400);  // Bad Request
            $response->error = "Invalid JSON format in the request body.";
        }
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred: " . $ee->getMessage();
    }
} else if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    try {
        if ($jsonbody && isset($jsonbody->desc)) {
            $stmt = $db->prepare("DELETE FROM expenses WHERE `Desc` = :desc");
            $stmt->execute(array(
                ':desc' => $jsonbody->desc
            ));

            // Check if any row was affected
            $rowsAffected = $stmt->rowCount();

            if ($rowsAffected > 0) {
                http_response_code(200);
                $response->message = "Expense deleted successfully.";
            } else {
                http_response_code(404);  // Not Found
                $response->error = "Expense with given description not found.";
            }
        } else {
            http_response_code(400);  // Bad Request
            $response->error = "Invalid or missing 'desc' in the request body.";
        }
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred: " . $ee->getMessage();
    }
}

echo json_encode($response);
exit();
?>
