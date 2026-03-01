<?php
// แก้ไข: ใส่ .php ให้ครบ
include 'profile_db.php'; 

// ตรวจสอบข้อมูลจาก $_POST (เพราะหน้าบ้านส่งเป็น FormData)
// ใช้ ?? '' เพื่อป้องกัน Error หากค่าไม่ได้ถูกส่งมา
$first_name = $_POST['first_name'] ?? null;
$last_name  = $_POST['last_name'] ?? null;
$address    = $_POST['address'] ?? null;
$phone      = $_POST['phone'] ?? null;
// (หมายเหตุ: emp_id ไม่จำเป็นต้องเช็คตอน INSERT เพราะฐานข้อมูลมักจะ Auto Increment ให้อยู่แล้ว)

if (!$first_name || !$last_name || !$address || !$phone) {
    echo json_encode([
        "success" => false,
        "message" => "ข้อมูลกรอกไม่ครบถ้วน"
    ]);
    exit;
}

// จัดการอัปโหลดไฟล์รูปภาพ (รับจาก $_FILES)
$imageName = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['image']['tmp_name'];
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = uniqid() . '.' . $file_extension; 
    // ย้ายไฟล์รูปไปเก็บไว้ในโฟลเดอร์ uploads
    move_uploaded_file($tmp_name, "uploads/" . $imageName);
} else {
    echo json_encode([
        "success" => false,
        "message" => "กรุณาอัปโหลดรูปภาพ"
    ]);
    exit;
}

try {
    // แก้ไข: ลบลูกน้ำ (,) ที่เกินออก
    $sql = "INSERT INTO employees 
            (first_name, last_name, address, phone, image) 
            VALUES 
            (:first_name, :last_name, :address, :phone, :image)";

    $stmt = $conn->prepare($sql);
    
    // แก้ไข: ลบ :password ออก เพราะใน SQL ไม่มี (หรือถ้าฐานข้อมูลคุณมีช่อง password จริงๆ ต้องไปเติมใน SQL ข้างบนด้วย)
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name'  => $last_name,
        ':address'    => $address,
        ':phone'      => $phone,
        ':image'      => $imageName
    ]);

    echo json_encode([
        "success" => true,
        "message" => "เพิ่มข้อมูลพนักงานเรียบร้อย"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()
    ]);
}
?>