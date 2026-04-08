<?php
    session_start();
    if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
        header("Location: /login");
        exit();
    }
    require "functions.php";
    $type = $_GET["type"] ?? "";
    $mission = intval($_GET["mission"] ?? 1);

    /*
    |--------------------------------------------------------------------------
    | GỌI API NESTJS ĐỂ LẤY FULL DỮ LIỆU
    |--------------------------------------------------------------------------
    */
    $endpoint = "/export-data?type=$type&missionId=$mission";
    $rows = call_api("GET", $endpoint);

    // Kiểm tra lỗi nếu API không trả về mảng dữ liệu hợp lệ
    if (!$rows || isset($rows['statusCode']) || !is_array($rows)) {
        die("LỖI: Không thể lấy dữ liệu từ Server API!");
    }

    // Xác định tên file
    if ($type === "union") {
        $filename = "RankingByUnion.xls";
    } elseif ($type === "personal") {
        $filename = "RankingByPersonal.xls";
    } elseif ($type === "mission") {
        $filename = "RankingByMission_$mission.xls";
    } else {
        die("INVALID EXPORT TYPE!");
    }

    /*
|--------------------------------------------------------------------------
| XUẤT EXCEL CHUẨN (Dạng .csv để không bị cảnh báo)
|--------------------------------------------------------------------------
*/

// Sửa lại đuôi file thành .csv
$filename = str_replace('.xls', '.csv', $filename);

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Mở luồng xuất dữ liệu trực tiếp ra trình duyệt
$output = fopen('php://output', 'w');

// IN BOM UTF-8 ĐỂ EXCEL HIỂN THỊ ĐÚNG TIẾNG VIỆT
fputs($output, "\xEF\xBB\xBF");

$rank = 1;

if ($type === "union") {
    // Xuất dòng Tiêu đề
    fputcsv($output, ['Hạng', 'Chi đoàn', 'Tổng điểm', 'Số thành viên']);
    // Xuất từng dòng dữ liệu
    foreach ($rows as $r) {
        fputcsv($output, [$rank++, $r["unionGroup"], $r["total_points"], $r["member_count"]]);
    }
} 
else if ($type === "personal") {
    fputcsv($output, ['Hạng', 'MSĐV', 'Họ tên', 'Chi đoàn', 'Tổng điểm']);
    foreach ($rows as $r) {
        fputcsv($output, [$rank++, $r["studentId"] . "\t", $r["fullName"], $r["unionGroup"], $r["points"]]);
    }
} 
else if ($type === "mission") {
    fputcsv($output, ['Hạng', 'MSĐV', 'Họ tên', 'Chi đoàn', 'Điểm nhiệm vụ']);
    foreach ($rows as $r) {
        fputcsv($output, [$rank++, $r["studentId"] . "\t", $r["fullName"], $r["unionGroup"], $r["mission_point"]]);
    }
}

fclose($output);
exit;
?>