<?php
require './include/header.php';
// ==========================================
// GỌI API DASHBOARD TỪ NESTJS (GỌI 1 LẦN DUY NHẤT)
// ==========================================
$data = call_api("GET", "/dashboard");

// Nếu API lỗi hoặc không có dữ liệu, khởi tạo mảng rỗng để tránh văng lỗi giao diện
if (!$data) {
    echo "<div class='alert alert-danger'>Lỗi: Không thể kết nối với hệ thống Backend NestJS!</div>";
    $data = [
        'totalMembers' => 0, 'pendingSubs' => 0, 'openMissions' => 0, 'totalNews' => 0,
        'topMembers' => [], 'unionStats' => [], 'latestSubs' => []
    ];
}

// Giải nén dữ liệu từ API trả về
$totalMembers = $data['totalMembers'];
$pendingSubs  = $data['pendingSubs'];
$openMissions = $data['openMissions'];
$totalNews    = $data['totalNews'];
$topMembers   = $data['topMembers'];
$unionStats   = $data['unionStats'];

?>


<div class="row">
  <!-- Tổng đoàn viên -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3><?= $totalMembers ?></h3>
        <p>Tổng đoàn viên</p>
      </div>
      <div class="icon"><i class="fa fa-users"></i></div>
    </div>
  </div>

  <!-- Nhiệm vụ mở -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?= $openMissions ?></h3>
        <p>Nhiệm vụ đang mở</p>
      </div>
      <div class="icon"><i class="fa fa-tasks"></i></div>
    </div>
  </div>

  <!-- Pending submissions -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning" onClick="location.href='/mission_approval';" style="cursor: pointer;">
      <div class="inner">
        <h3><?= $pendingSubs ?></h3>
        <p>Bài chờ duyệt</p>
      </div>
      <div class="icon"><i class="fa fa-clock"></i></div>
    </div>
  </div>

  <!-- Tin tức -->
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3><?= $totalNews ?></h3>
        <p>Tổng tin tức</p>
      </div>
      <div class="icon"><i class="fa fa-newspaper"></i></div>
    </div>
  </div>

</div>

<!-- ========================= -->
<!-- TOP 5 ĐOÀN VIÊN XUẤT SẮC -->
<!-- ========================= -->
<div class="card mt-3">
  <div class="card-header bg-success text-white">
    <h3 class="card-title">Top 5 đoàn viên xuất sắc</h3>
  </div>
  <div class="card-body">
    <table class="table table-hover">
      <thead class="bg-light">
        <tr>
          <th>MSĐV</th>
          <th>Họ tên</th>
          <th>Điểm</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topMembers as $m): ?>
          <tr>
            <td><?= $m["studentId"] ?></td>
            <td><?= $m["fullName"] ?></td>
            <td><b><?= $m["points"] ?></b></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ======================= -->
<!-- THỐNG KÊ CHI ĐOÀN -->
<!-- ======================= -->
<div class="card">
  <div class="card-header bg-secondary text-white">
    <h3 class="card-title">Thống kê chi đoàn</h3>
  </div>
  <div class="card-body">
    <table class="table table-bordered text-center">
      <thead class="bg-light">
        <tr>
          <th>Chi đoàn</th>
          <th>Tổng số</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($unionStats as $u): ?>
          <tr>
            <td><?= $u["unionGroup"] ?></td>
            <td><?= $u["total"] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require './include/footer.php'; ?>
