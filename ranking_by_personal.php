<?php
  require './include/header.php';
  
  $response = call_api("GET", "/rankings");
  if ($response && isset($response['rankPersonal'])) {
      $users = $response['rankPersonal'];
  } else {
      $users = [];
      echo "<div class='alert alert-danger'>Lỗi: Không thể tải bảng xếp hạng.</div>";
  }
?>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">👤 Xếp hạng cá nhân</h3>

    <a href="./process/exportExcel?type=personal" class="btn btn-success btn-sm">
      <i class="fa fa-file-excel"></i> Xuất Excel
    </a>
  </div>

  <div class="card-body">
    <table class="table table-bordered table-striped text-center">
      <thead class="bg-secondary text-white">
        <tr>
          <th>Rank</th>
          <th>MSSV</th>
          <th>Họ tên</th>
          <th>Chi đoàn</th>
          <th>Tổng điểm</th>
        </tr>
      </thead>

      <tbody>
        <?php $rank = 1; foreach ($users as $u): ?>
        <tr>
          <td><span class="badge badge-info p-2"><?= $rank++ ?></span></td>
          <td><?= $u["studentId"] ?></td>
          <td><b><?= htmlspecialchars($u["fullName"]) ?></b></td>
          <td><?= $u["unionGroup"] ?></td>
          <td><span class="badge badge-success p-2"><?= $u["points"] ?></span></td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($users)): ?>
        <tr>
          <td colspan="5" class="text-muted p-3">Không có dữ liệu.</td>
        </tr>
        <?php endif; ?>
      </tbody>

    </table>
  </div>
</div>

<?php require './include/footer.php'; ?>

<script>
  $("#ranking_menu").addClass("menu-open");
</script>