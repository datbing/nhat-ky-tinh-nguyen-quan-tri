<?php
session_start();
header("Content-Type: application/json");

require_once "./db_my.php";
require_once "./functions.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    alert("error", "Phương thức không hợp lệ!");
    exit();
}

$action = $_POST["action"] ?? null;


/*
|==========================================================================
| PHẦN 1: XỬ LÝ ADMIN BẰNG MYSQL (Giữ nguyên)
|==========================================================================
*/

if ($action === "login") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($email === "" || $password === "") alert("error", "Vui lòng nhập email và mật khẩu!");

    try {
        $stmt = $mysql->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(["email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) alert("error", "Email không tồn tại!");
        if (!password_verify($password, $user["password"])) alert("error", "Mật khẩu không đúng!");

        $_SESSION["logged_in"] = true;
        $_SESSION["user"] = [
            "id" => $user["id"],
        ];

        $_SESSION["nestjs_token"] = call_api("POST", "/auth/adminLogin", [
            "id" => $user["id"],
            "name" => $user["fullName"],
        ])["access_token"];
        
        alert("success", "Đăng nhập thành công!");
    } catch (Exception $e) {
        alert("error", "Lỗi server: " . $e->getMessage());
    }
}

/*-------------------------------ADMIN_ACCOUNTS.PHP-------------------------------*/
/*
|--------------------------------------------------------------------
| ADD ADMIN
|--------------------------------------------------------------------
*/
if ($action === "add_admin") {

    $fullName   = trim($_POST["fullName"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $password   = trim($_POST["password"] ?? "");
    $adminLevel = intval($_POST["adminLevel"] ?? 0);

    if ($fullName === "" || $email === "" || $password === "")
        alert("error", "Vui lòng nhập đầy đủ thông tin!");

    try {
        // Kiểm tra email tồn tại
        $check = $mysql->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0)
            alert("error", "Email này đã tồn tại!");

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $mysql->prepare("
            INSERT INTO users (fullName, email, password, adminLevel)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$fullName, $email, $hash, $adminLevel]);

        alert("success", "Thêm admin thành công!");
    }
    catch (Exception $e) {
        alert("error", "Lỗi SQL: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------
| EDIT ADMIN
|--------------------------------------------------------------------
*/
if ($action === "edit_admin") {

    $id         = $_POST["id"] ?? "";
    $email      = trim($_POST["email"] ?? "");
    $fullName   = trim($_POST["fullName"] ?? "");
    $adminLevel = intval($_POST["adminLevel"] ?? 0);

    if (!$id) alert("error", "Thiếu ID admin!");

    if ($email === "" || $fullName === "")
        alert("error", "Không được để trống!");

    try {
        // kiểm tra email mới trùng email người khác?
        $check = $mysql->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $id]);

        if ($check->rowCount() > 0)
            alert("error", "Email này đã thuộc về admin khác!");

        $stmt = $mysql->prepare("
            UPDATE users 
            SET fullName = ?, email = ?, adminLevel = ?
            WHERE id = ?
        ");

        $stmt->execute([$fullName, $email, $adminLevel, $id]);

        alert("success", "Cập nhật admin thành công!");
    }
    catch (Exception $e) {
        alert("error", "Lỗi SQL: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------
| RESET ADMIN PASSWORD
|--------------------------------------------------------------------
*/
if ($action === "reset_admin_pass") {

    $id = $_POST["id"] ?? null;
    if (!$id) alert("error", "Thiếu ID!");

    try {
        $newPass = password_hash("123456", PASSWORD_BCRYPT);

        $stmt = $mysql->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newPass, $id]);

        alert("success", "Đặt lại mật khẩu thành 123456 thành công!");
    }
    catch (Exception $e) {
        alert("error", "Lỗi: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------
| DELETE ADMIN
|--------------------------------------------------------------------
*/
if ($action === "delete_admin") {

    $id = $_POST["id"] ?? null;
    if (!$id) alert("error", "Thiếu ID!");

    // Không cho xóa chính mình
    if (isset($_SESSION["user"]["id"]) && $_SESSION["user"]["id"] == $id)
        alert("error", "Bạn không thể tự xóa chính mình!");

    try {
        $stmt = $mysql->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0)
            alert("error", "Không tìm thấy admin để xóa!");

        alert("success", "Đã xóa admin!");
    }
    catch (Exception $e) {
        alert("error", "Lỗi SQL: " . $e->getMessage());
    }
}



/*
|==========================================================================
| PHẦN 2: XỬ LÝ DỮ LIỆU BẰNG NESTJS API (cURL)
|==========================================================================
*/

// --- MEMBERS ---
if ($action === "create_demo_account") {
    $res = call_api("POST", "/members/demo", $_POST);
    alert($res["status"], $res["message"]);
}

if ($action === "update_member") {
    $id = trim($_POST["studentId"] ?? "");
    $res = call_api("PUT", "/members/" . urlencode($id), $_POST);
    alert($res["status"], $res["message"]);
}

if ($action === "delete_single_member") {
    $id = trim($_POST["id"] ?? "");
    $res = call_api("DELETE", "/members/" . urlencode($id));
    alert($res["status"], $res["message"]);
}

if ($action === "reset_password") {
    $id = trim($_POST["id"] ?? "");
    $res = call_api("PATCH", "/members/" . urlencode($id) . "/reset-password");
    alert($res["status"], $res["message"]);
}

if ($action === "import_user_excel") {
    if (!isset($_FILES["file"])) alert("error", "Không có file upload!");
    
    // Tạo CURLFile để gửi qua API
    $cfile = new CURLFile($_FILES["file"]["tmp_name"], $_FILES["file"]["type"], $_FILES["file"]["name"]);
    $data = ["file" => $cfile];
    
    $res = call_api("POST", "/members/import", $data, true);
    alert($res["status"], $res["message"]);
}

if ($action === "delete_all_members") {
    $res = call_api("DELETE", "/members", ["confirm" => $_POST["confirm"] ?? ""]);
    alert($res["status"], $res["message"]);
}

if ($action === "reset_points") {
    $res = call_api("PATCH", "/members/reset-points");
    alert($res["status"], $res["message"]);
}

// --- MISSIONS ---
if ($action === "edit_mission") {
    $id = $_POST["id"] ?? "";
    $res = call_api("PUT", "/missions/" . $id, $_POST);
    alert($res["status"], $res["message"]);
}

if ($action === "reset_mission") {
    $id = $_POST["id"] ?? "";
    $res = call_api("PATCH", "/missions/" . $id . "/reset");
    alert($res["status"], $res["message"]);
}

if ($action === "approve_submission_normal") {
    $id = $_POST["id"] ?? "";
    $res = call_api("PATCH", "/submissions/" . $id . "/approve-normal");
    alert($res["status"], $res["message"]);
}

if ($action === "approve_submission_news") {
    $id = $_POST["id"] ?? "";
    $res = call_api("PATCH", "/submissions/" . $id . "/approve-news");
    alert($res["status"], $res["message"]);
}

if ($action === "delete_submission") {
    $id = $_POST["id"] ?? "";
    $res = call_api("DELETE", "/submissions/" . $id);
    alert($res["status"], $res["message"]);
}

// --- CHATBOT ---
if ($action === "save_instruction") {
    $res = call_api("POST", "/chatbot/instruction", ["text" => $_POST["text"] ?? ""]);
    alert($res["status"], $res["message"]);
}

if ($action === "save_knowledge") {
    $res = call_api("POST", "/chatbot/knowledge", ["text" => $_POST["text"] ?? ""]);
    alert($res["status"], $res["message"]);
}

// --- NEWS ---
if ($action === "delete_news") {
    $id = $_POST["id"] ?? "";
    $res = call_api("DELETE", "/news/" . $id);
    alert($res["status"], $res["message"]);
}

if ($action === "delete_all_news") {
    $res = call_api("DELETE", "/news");
    alert($res["status"], $res["message"]);
}

// --- MAIN NEWS ---
if ($action === "add_main_news") {
    $res = call_api("POST", "/main-news", $_POST);
    alert($res["status"], $res["message"]);
}

if ($action === "edit_main_news") {
    $id = $_POST["id"] ?? "";
    $res = call_api("PUT", "/main-news/" . $id, $_POST);
    alert($res["status"], $res["message"]);
}

if ($action === "delete_main_news") {
    $id = $_POST["id"] ?? "";
    $res = call_api("DELETE", "/main-news/" . $id);
    alert($res["status"], $res["message"]);
}

// --- DIGIMAP ---
if ($action === "add_digi") {
    $res = call_api("POST", "/digimap", $_POST);
    alert($res["status"], $res["message"]);
}

if ($action === "edit_digi") {
    $id = $_POST["id"] ?? "";
    $res = call_api("PUT", "/digimap/" . $id, $_POST);
    alert($res["status"], $res["message"]);
}

if ($action === "reset_digi") {
    $id = $_POST["id"] ?? "";
    $res = call_api("PATCH", "/digimap/" . $id . "/reset");
    alert($res["status"], $res["message"]);
}

if ($action === "delete_digi") {
    $id = $_POST["id"] ?? "";
    $res = call_api("DELETE", "/digimap/" . $id);
    alert($res["status"], $res["message"]);
}

// Nếu action không hợp lệ
alert("error", "Invalid action!");
?>