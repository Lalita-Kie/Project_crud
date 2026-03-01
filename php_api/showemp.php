<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$host = "localhost";
$username = "root";
$password = "";
$dbname = "profile_db"; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// 1. ดึงข้อมูลพนักงาน (GET)
if ($method === 'GET') {
    try {
        $stmt = $conn->query("SELECT * FROM employees ORDER BY emp_id DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $data]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    exit;
}

// 2. จัดการข้อมูล (POST - เพิ่ม, แก้ไข, ลบ)
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    // การลบข้อมูล
    if ($action === 'delete') {
        $emp_id = $_POST['emp_id'] ?? '';
        try {
            $stmt = $conn->prepare("DELETE FROM employees WHERE emp_id = :emp_id");
            $stmt->bindParam(':emp_id', $emp_id);
            $stmt->execute();
            echo json_encode(["message" => "ลบข้อมูลสำเร็จ"]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "ไม่สามารถลบข้อมูลได้: " . $e->getMessage()]);
        }
        exit;
    }

    // การเพิ่ม / แก้ไขข้อมูล
    if ($action === 'add' || $action === 'update') {
        $emp_id = $_POST['emp_id'] ?? null;
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $address = $_POST['address'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = time() . '_' . uniqid() . '.' . $file_extension; 
            move_uploaded_file($tmp_name, "uploads/" . $imageName);
        }

        try {
            if ($action === 'add') {
                $sql = "INSERT INTO employees (first_name, last_name, address, phone, image) 
                        VALUES (:first_name, :last_name, :address, :phone, :image)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':address' => $address,
                    ':phone' => $phone,
                    ':image' => $imageName
                ]);
                echo json_encode(["message" => "เพิ่มข้อมูลพนักงานสำเร็จ"]);
            } else if ($action === 'update') {
                if ($imageName) {
                    $sql = "UPDATE employees SET first_name=:first_name, last_name=:last_name, address=:address, phone=:phone, image=:image WHERE emp_id=:emp_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':first_name' => $first_name, ':last_name' => $last_name, ':address' => $address, ':phone' => $phone, ':image' => $imageName, ':emp_id' => $emp_id]);
                } else {
                    $sql = "UPDATE employees SET first_name=:first_name, last_name=:last_name, address=:address, phone=:phone WHERE emp_id=:emp_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':first_name' => $first_name, ':last_name' => $last_name, ':address' => $address, ':phone' => $phone, ':emp_id' => $emp_id]);
                }
                echo json_encode(["message" => "แก้ไขข้อมูลพนักงานสำเร็จ"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
        }
        exit;
    }
}
?>