<?php
// api/location.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// පරිශීලකයා ලොග් වී ඇත්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// ============================================
// 1. UPDATE WORKER LOCATION (වර්කර්ගේ ස්ථානය යාවත්කාලීන කිරීම)
// ============================================
if ($action === 'update_worker_location') {
    if ($role !== 'worker') {
        echo json_encode(['status' => 'error', 'message' => 'Only workers can update their live location here.']);
        exit;
    }

    $lat = $_POST['lat'] ?? '';
    $lng = $_POST['lng'] ?? '';

    if (empty($lat) || empty($lng)) {
        echo json_encode(['status' => 'error', 'message' => 'Latitude and Longitude are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE worker_profiles SET location_lat = ?, location_lng = ? WHERE worker_id = ?");
        $stmt->execute([$lat, $lng, $userId]);
        
        echo json_encode(['status' => 'success', 'message' => 'Location updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// 2. GET NEARBY WORKERS (ළඟම සිටින වර්කර්ලා සෙවීම - Haversine Formula)
// ============================================
if ($action === 'get_nearby_workers') {
    $clientLat = $_POST['lat'] ?? ($_GET['lat'] ?? '');
    $clientLng = $_POST['lng'] ?? ($_GET['lng'] ?? '');
    $radiusKm = $_POST['radius'] ?? ($_GET['radius'] ?? 10); // පෙරනිමියෙන් කිලෝමීටර් 10ක වටප්‍රමාණය
    $categoryId = $_POST['category_id'] ?? ($_GET['category_id'] ?? null);

    if (empty($clientLat) || empty($clientLng)) {
        echo json_encode(['status' => 'error', 'message' => 'Client location coordinates are required.']);
        exit;
    }

    try {
        // Haversine සූත්‍රය මඟින් දුර (කිලෝමීටර් වලින්) ගණනය කර ළඟම සිටින අය පෙරීම
        $sql = "SELECT u.id, u.first_name, u.last_name, u.profile_picture, wp.rating, wp.location_lat, wp.location_lng,
                c.name_en AS category_name,
                ( 6371 * acos( cos( radians(?) ) * cos( radians( wp.location_lat ) ) 
                * cos( radians( wp.location_lng ) - radians(?) ) + sin( radians(?) ) 
                * sin( radians( wp.location_lat ) ) ) ) AS distance 
                FROM users u
                JOIN worker_profiles wp ON u.id = wp.worker_id
                JOIN categories c ON wp.category_id = c.id
                WHERE u.role = 'worker' AND u.is_active = 1 AND wp.location_lat IS NOT NULL";
        
        $params = [$clientLat, $clientLng, $clientLat];

        // කාණ්ඩයක් (Category) තෝරා ඇත්නම් එයට පමණක් සීමා කිරීම
        if ($categoryId) {
            $sql .= " AND wp.category_id = ?";
            $params[] = $categoryId;
        }

        // දුර ප්‍රමාණයට අනුව පෙරීම (HAVING clause) සහ ළඟම සිටින අය මුලින් පෙන්වීම
        $sql .= " HAVING distance < ? ORDER BY distance ASC LIMIT 20";
        $params[] = $radiusKm;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $nearbyWorkers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'workers_found' => count($nearbyWorkers),
            'data' => $nearbyWorkers
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Action එක නිවැරදි නොමැතිනම්
echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
exit;
?>