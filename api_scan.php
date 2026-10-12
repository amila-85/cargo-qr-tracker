<?php
// api_scan.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

$trackingNumber = isset($_GET['trackingNumber']) ? trim($_GET['trackingNumber']) : '';

if (empty($trackingNumber)) {
    echo json_encode(["status" => "error", "message" => "No tracking number provided"]);
    exit;
}

try {
    // Explicitly include UpdatedBy and UpdatedAt
    $stmt = $conn->prepare("SELECT TrackingNumber, InvoiceNumber, SenderName, ReceiverName, ReceiverEmail, BoxSize, BoxType, Status, UpdatedBy, UpdatedAt, EstimatedDeliveryDate FROM BoxShipments WHERE TrackingNumber = :tn LIMIT 1");
    $stmt->execute([':tn' => $trackingNumber]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($shipment) {
        echo json_encode([
            "status" => "success",
            "data"   => $shipment
        ]);
    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "Shipment not found"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status"  => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>