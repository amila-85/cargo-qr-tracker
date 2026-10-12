<?php
// api_update_status.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access. Please log in."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$trackingNumber = isset($data['trackingNumber']) ? trim($data['trackingNumber']) : '';
$status         = isset($data['status']) ? trim($data['status']) : '';
$updatedBy      = !empty($data['updatedBy']) ? trim($data['updatedBy']) : ($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'System User');
$updatedAt      = !empty($data['updatedAt']) ? trim($data['updatedAt']) : date('Y-m-d H:i:s');

if (empty($trackingNumber) || empty($status)) {
    echo json_encode(["status" => "error", "message" => "Tracking number and status are required."]);
    exit;
}

// Internal delivery date calculation (kept for database record-keeping)
$daysToAdd = 0;
switch ($status) {
    case 'Out for Delivery':
        $daysToAdd = 0;
        break;
    case 'Customs Cleared':
        $daysToAdd = 2;
        break;
    case 'Shipped to Destination':
        $daysToAdd = 7;
        break;
    case 'Arrive in to Port':
        $daysToAdd = 10;
        break;
    case 'In Transit to Port':
        $daysToAdd = 14;
        break;
    case 'Warehouse':
        $daysToAdd = 21;
        break;
    default:
        $daysToAdd = 0;
        break;
}

$estimatedDeliveryDate = date('Y-m-d', strtotime("+$daysToAdd days"));

try {
    // 1. UPDATE DB RECORD
    $stmt = $conn->prepare("UPDATE BoxShipments 
                            SET Status = :status, 
                                UpdatedBy = :updatedBy, 
                                UpdatedAt = :updatedAt,
                                EstimatedDeliveryDate = :estimatedDeliveryDate
                            WHERE TrackingNumber = :trackingNumber");
    
    $stmt->execute([
        ':status'                => $status,
        ':updatedBy'             => $updatedBy,
        ':updatedAt'             => $updatedAt,
        ':estimatedDeliveryDate' => $estimatedDeliveryDate,
        ':trackingNumber'        => $trackingNumber
    ]);

    // 2. FETCH CUSTOMER DETAILS & SEND EMAIL
    $fetchStmt = $conn->prepare("SELECT ReceiverName, ReceiverEmail, InvoiceNumber FROM BoxShipments WHERE TrackingNumber = :tn LIMIT 1");
    $fetchStmt->execute([':tn' => $trackingNumber]);
    $shipment = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    $emailSent = false;
    if ($shipment && !empty($shipment['ReceiverEmail'])) {
        $to      = $shipment['ReceiverEmail'];
        $subject = "Shipment Status Update: " . $trackingNumber . " - " . $status;
        
        // Clean Email Template without Delivery Date
        $message = "Dear " . htmlspecialchars($shipment['ReceiverName']) . ",\n\n";
        $message .= "Your shipment (Invoice: " . $shipment['InvoiceNumber'] . ") status has been updated.\n\n";
        $message .= "Tracking Number: " . $trackingNumber . "\n";
        $message .= "New Status: " . $status . "\n\n";
        $message .= "Thank you for choosing ECO Cargo!\n";

        $headers = "From: no-reply@ecocargo.com\r\n" .
                   "Reply-To: support@ecocargo.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        $emailSent = mail($to, $subject, $message, $headers);
    }

    echo json_encode([
        "status"    => "success",
        "message"   => "Status updated and email sent.",
        "updatedBy" => $updatedBy,
        "updatedAt" => $updatedAt,
        "emailSent" => $emailSent
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>