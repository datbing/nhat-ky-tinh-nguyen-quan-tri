<?php
  define("NESTJS_API_URL", "http://localhost:3000/admin");
  define("NESTJS_TOKEN", $_SESSION['nestjs_token'] ?? "chuoi_token_cua_ban");


  function alert($type, $message) {
    echo json_encode([
        "status" => $type,
        "message" => $message
    ]);
    exit();
  }

  function fetchUser(){
    global $mysql;
    $id = $_SESSION["user"]["id"];
    $stmt = $mysql->prepare("SELECT id, fullName, email, adminLevel, createdAt FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(["id" => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
  }

  function call_api($method, $endpoint, $data = [], $isFileUpload = false) {
    $url = NESTJS_API_URL . $endpoint;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = [
        "Authorization: Bearer " . NESTJS_TOKEN
    ];

    if (!empty($data)) {
        if ($isFileUpload) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = "Content-Type: application/json";
        }
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ==========================================
    // 1. XỬ LÝ TOKEN HẾT HẠN (HTTP 401)
    // ==========================================
    if ($httpCode == 401) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if ($isAjax) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập đã hết hạn!']);
            exit();
        } else {
            echo "<script>
                alert('Phiên đăng nhập của bạn đã hết hạn. Vui lòng đăng nhập lại!');
                window.location.href = '/login.php'; // Lưu ý sửa lại đúng tên file login của bạn
            </script>";
            exit();
        }
    }

    // ==========================================
    // 2. XỬ LÝ RESPONSE BÌNH THƯỜNG
    // ==========================================
    $result = json_decode($response, true);

    if ($httpCode >= 400) {
        $msg = $result['message'] ?? 'Lỗi từ API (Code: '.$httpCode.')';
        if (is_array($msg)) {
            $msg = implode(", ", $msg);
        }
        
        if (function_exists('alert')) {
            alert("error", $msg);
        }
    }

    return $result;
  }
?>