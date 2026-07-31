<?php
header('Content-Type: application/json');
require_once 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $stmt = $pdo->query("SELECT * FROM records ORDER BY id DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'create':
    case 'update':
        $name = $_POST['name'] ?? '';
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? 'Active';
        $id = $_POST['id'] ?? null;

        $imageName = 'default.png';
        
        // Retain existing image if updating without selecting a new file
        if ($id && $action === 'update') {
            $stmt = $pdo->prepare("SELECT image FROM records WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch();
            if ($existing && !empty($existing['image'])) {
                $imageName = $existing['image'];
            }
        }

        // Process uploaded profile image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = './uploads/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $destPath = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $imageName = $newFileName;
                }
            }
        }

        if ($action === 'create') {
            $code = 'REC-' . rand(100, 999);
            $stmt = $pdo->prepare("INSERT INTO records (record_code, name, role, image, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $role, $imageName, $status]);
            echo json_encode(["success" => true, "message" => "Record created successfully"]);
        } else {
            $stmt = $pdo->prepare("UPDATE records SET name = ?, role = ?, image = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $role, $imageName, $status, $id]);
            echo json_encode(["success" => true, "message" => "Record updated successfully"]);
        }
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM records WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["success" => true, "message" => "Record deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Missing ID"]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Invalid Endpoint Action"]);
        break;
}
?>