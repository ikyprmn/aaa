<?php
include 'db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'updated_stok' => []];

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
    $response['message'] = 'Tidak ada item yang dipilih.';
    echo json_encode($response);
    exit();
}

$conn->begin_transaction(); 

try {
    foreach ($data['items'] as $item) {
        $menu_id = (int)$item['menu_id'];
        $quantity = (int)$item['quantity'];

        if ($quantity <= 0) {
            continue; 
        }

        
        $stmt_select = $conn->prepare("SELECT stok FROM menu WHERE id = ? FOR UPDATE"); // Lock row
        $stmt_select->bind_param("i", $menu_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();

        if ($result_select->num_rows === 0) {
            throw new Exception("Menu dengan ID $menu_id tidak ditemukan.");
        }

        $row = $result_select->fetch_assoc();
        $current_stok = $row['stok'];
        $stmt_select->close();

        if ($current_stok < $quantity) {
            throw new Exception("Stok untuk item ini tidak mencukupi. Stok tersedia: $current_stok.");
        }

        
        $new_stok = $current_stok - $quantity;
        $stmt_update = $conn->prepare("UPDATE menu SET stok = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $new_stok, $menu_id);
        if (!$stmt_update->execute()) {
            throw new Exception("Gagal mengurangi stok untuk menu ID $menu_id: " . $stmt_update->error);
        }
        $stmt_update->close();

        $response['updated_stok'][] = ['menu_id' => $menu_id, 'new_stok' => $new_stok];
    }

    $conn->commit(); 
    $response['success'] = true;
    $response['message'] = 'Pesanan berhasil ditempatkan dan stok diperbarui.';

} catch (Exception $e) {
    $conn->rollback(); 
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>