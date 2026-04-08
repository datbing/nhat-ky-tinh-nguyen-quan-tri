<?php
require './include/header.php';


$response = call_api("GET", "/rankings");

if ($response && isset($response['rankingByMission'])) {
    $ranking = $response['rankingByMission']; 
} else {
    $ranking = [];
    echo "<div class='alert alert-danger'>Lỗi: Không thể tải bảng xếp hạng từ Backend.</div>";
}
?>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">📌 Xếp hạng theo từng nhiệm vụ</h3>

  </div>
  
  <div class="card-body">
    
    <?php foreach ($ranking as $missionId => $data): ?>
      <?php $mission = $data["mission"]; ?>
      
      <div class="border rounded p-3 mb-4 shadow-sm">
        <h4 class="mb-3">
          🏅 Nhiệm vụ #<?= $mission["id"] ?>:
          <b class="text-primary"><?= htmlspecialchars($mission["missionName"]) ?></b>
          <a href="/process/exportExcel?type=mission&mission=<?= $mission["id"] ?>" class="btn btn-success btn-sm">
            <i class="fa fa-file-excel"></i> Xuất Excel
          </a>
        </h4>

        <table class="table table-bordered table-striped text-center">
          <thead class="bg-dark text-white">
            <tr>
              <th style="width:80px">Rank</th>
              <th>MSSV</th>
              <th>Họ tên</th>
              <th>Chi đoàn</th>
              <th>Điểm nhiệm vụ</th>
            </tr>
          </thead>

          <tbody>
            <?php $rank = 1; foreach ($data["list"] as $u): ?>
              <tr>
                <td><span class="badge badge-info p-2"><?= $rank++ ?></span></td>
                <td><?= $u["studentId"] ?></td>
                <td><b><?= $u["fullName"] ?></b></td>
                <td><?= $u["unionGroup"] ?></td>
                <td>
                  <span class="badge badge-success p-2">
                    <?= $u["mission_points"] ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>

        </table>
      </div>

    <?php endforeach; ?>
    <?php if (empty($data["list"])): ?>
      Không có dữ liệu.
    <?php endif; ?>

  </div>
</div>

<?php require './include/footer.php'; ?>

<script>
  $("#ranking_menu").addClass("menu-open");
</script>