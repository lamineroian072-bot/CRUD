<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'get_dashboard_data':
        try {
            $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
            $orders = $pdo->query("SELECT o.*, p.model, p.storage FROM orders o JOIN products p ON o.product_id = p.id ORDER BY o.id DESC")->fetchAll();

            $totalProducts = count($products);
            $totalOrders = count($orders);
            
            $stmtRev = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'Completed'");
            $totalRevenue = (float)$stmtRev->fetchColumn();

            $brandNewCount = 0;
            $secondhandCount = 0;
            $totalValuation = 0;
            $totalMSRP = 0;
            $totalPrice = 0;
            $marginCount = 0;
            
            $rapidMovers = [];

            foreach ($products as $p) {
                if (strtolower($p['condition_type']) === 'brand new') {
                    $brandNewCount++;
                } else {
                    $secondhandCount++;
                }
                
                $totalValuation += ((float)$p['price'] * (int)$p['stock']);
                
                if ((float)$p['msrp'] > 0) {
                    $totalMSRP += (float)$p['msrp'];
                    $totalPrice += (float)$p['price'];
                    $marginCount++;
                }

                // Rapid Movers (e.g. stock <= 3 but not 0)
                if ($p['stock'] > 0 && $p['stock'] <= 3) {
                    $rapidMovers[] = $p;
                }
            }
            
            $avgDepreciation = 0;
            if ($totalMSRP > 0) {
                $avgDepreciation = (($totalMSRP - $totalPrice) / $totalMSRP) * 100;
            }

            echo json_encode([
                'success' => true,
                'metrics' => [
                    'totalProducts' => $totalProducts,
                    'brandNewCount' => $brandNewCount,
                    'secondhandCount' => $secondhandCount,
                    'totalOrders' => $totalOrders,
                    'totalRevenue' => $totalRevenue,
                    'totalValuation' => $totalValuation,
                    'avgDepreciation' => round($avgDepreciation, 1),
                    'rapidMoversCount' => count($rapidMovers)
                ],
                'products' => $products,
                'orders' => $orders
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_product':
        try {
            $model = trim($_POST['model'] ?? '');
            $condition = $_POST['condition_type'] ?? 'Brand New';
            $storage = trim($_POST['storage'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $msrp = floatval($_POST['msrp'] ?? 0);
            $condition_grade = trim($_POST['condition_grade'] ?? '');
            $mileage = trim($_POST['mileage'] ?? '');
            $warranty_status = trim($_POST['warranty_status'] ?? '');
            $inspection_status = trim($_POST['inspection_status'] ?? '');
            $thumbnail_url = trim($_POST['thumbnail_url'] ?? '');

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newFilename = uniqid('prod_') . '.' . $ext;
                $targetPath = 'uploads/' . $newFilename;
                if (!is_dir('uploads')) mkdir('uploads', 0777, true);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $thumbnail_url = $targetPath;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO products (model, condition_type, storage, price, stock, msrp, condition_grade, mileage, warranty_status, inspection_status, thumbnail_url) VALUES (:m, :c, :s, :p, :st, :msrp, :cg, :mil, :ws, :is, :tu)");
            $stmt->execute([':m' => $model, ':c' => $condition, ':s' => $storage, ':p' => $price, ':st' => $stock, ':msrp' => $msrp, ':cg' => $condition_grade, ':mil' => $mileage, ':ws' => $warranty_status, ':is' => $inspection_status, ':tu' => $thumbnail_url]);

            echo json_encode(['success' => true, 'message' => 'Product added successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_product':
        try {
            $id = intval($_POST['id'] ?? 0);
            $model = trim($_POST['model'] ?? '');
            $condition = $_POST['condition_type'] ?? 'Brand New';
            $storage = trim($_POST['storage'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $msrp = floatval($_POST['msrp'] ?? 0);
            $condition_grade = trim($_POST['condition_grade'] ?? '');
            $mileage = trim($_POST['mileage'] ?? '');
            $warranty_status = trim($_POST['warranty_status'] ?? '');
            $inspection_status = trim($_POST['inspection_status'] ?? '');
            $thumbnail_url = trim($_POST['thumbnail_url'] ?? '');

            // Get current thumbnail
            $stmtCurrent = $pdo->prepare("SELECT thumbnail_url FROM products WHERE id = :id");
            $stmtCurrent->execute([':id' => $id]);
            $currentData = $stmtCurrent->fetch();
            if (empty($thumbnail_url)) {
                $thumbnail_url = $currentData['thumbnail_url'] ?? '';
            }

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newFilename = uniqid('prod_') . '.' . $ext;
                $targetPath = 'uploads/' . $newFilename;
                if (!is_dir('uploads')) mkdir('uploads', 0777, true);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // Optionally delete old file if it exists and is in uploads/
                    if ($thumbnail_url && file_exists($thumbnail_url) && strpos($thumbnail_url, 'uploads/') === 0) {
                        unlink($thumbnail_url);
                    }
                    $thumbnail_url = $targetPath;
                }
            }

            $stmt = $pdo->prepare("UPDATE products SET model = :m, condition_type = :c, storage = :s, price = :p, stock = :st, msrp = :msrp, condition_grade = :cg, mileage = :mil, warranty_status = :ws, inspection_status = :is, thumbnail_url = :tu WHERE id = :id");
            $stmt->execute([':m' => $model, ':c' => $condition, ':s' => $storage, ':p' => $price, ':st' => $stock, ':msrp' => $msrp, ':cg' => $condition_grade, ':mil' => $mileage, ':ws' => $warranty_status, ':is' => $inspection_status, ':tu' => $thumbnail_url, ':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Product updated successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_product':
        try {
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_order':
        try {
            $customer = trim($_POST['customer'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            $productId = intval($_POST['product_id'] ?? 0);
            $qty = intval($_POST['quantity'] ?? 1);
            $status = $_POST['status'] ?? 'Completed';

            $stmtProd = $pdo->prepare("SELECT price, stock FROM products WHERE id = :id");
            $stmtProd->execute([':id' => $productId]);
            $prod = $stmtProd->fetch();

            if (!$prod) {
                echo json_encode(['success' => false, 'message' => 'Selected product not found.']);
                exit;
            }

            $total = $prod['price'] * $qty;
            $orderCode = '#ORD-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare("INSERT INTO orders (order_code, customer, contact, product_id, quantity, total_amount, status) VALUES (:code, :cust, :cont, :pid, :qty, :tot, :st)");
            $stmt->execute([
                ':code' => $orderCode,
                ':cust' => $customer,
                ':cont' => $contact,
                ':pid' => $productId,
                ':qty' => $qty,
                ':tot' => $total,
                ':st' => $status
            ]);

            // Deduct stock if order completed
            if ($status === 'Completed' && $prod['stock'] >= $qty) {
                $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :pid")->execute([':qty' => $qty, ':pid' => $productId]);
            }

            echo json_encode(['success' => true, 'message' => 'Order created successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_order':
        try {
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Order deleted successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>