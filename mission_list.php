<?php
require './include/header.php';
if ($user_info['adminLevel'] < 10) {
  echo "<script>location.href='/'</script>";
  exit();
}


$response = call_api("GET", "/missions");

if ($response && isset($response['missions'])) {
    $missions = $response['missions'];
} else {
    $missions = [];
    echo "<div class='alert alert-danger'>Không thể kết nối API hoặc không có dữ liệu nhiệm vụ.</div>";
}
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Quản lý nhiệm vụ</h3>
  </div>

  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th style="width: 50px">ID</th>
          <th>Nhiệm vụ</th>
          <th style="width: 100px">Status</th>
          <th style="width: 90px">Joined</th>
          <th style="width: 160px">Hành động</th>
        </tr>
      </thead>
      <tbody>

        <?php foreach ($missions as $m): ?>
        <tr>
          <td><?= $m['id'] ?></td>
          <td><?= htmlspecialchars($m['missionName']) ?></td>

          <td>
            <?php if ($m['status'] === 'open'): ?>
              <span class="badge badge-success">Open</span>
            <?php elseif ($m['status'] === 'close'): ?>
              <span class="badge badge-danger">Close</span>
            <?php else: ?>
              <span class="badge badge-warning">Unknown</span>
            <?php endif; ?>
          </td>

          <td><?= intval($m['joined']) ?></td>

          <td>
            <button class="btn btn-sm btn-primary"
              data-toggle="modal"
              data-target="#editMissionModal"
              onclick='fillMissionForm(<?= json_encode($m) ?>)'>
              <i class="fa fa-edit"></i>
            </button>

            <button class="btn btn-sm btn-warning"
              onclick="resetMission(<?= $m['id'] ?>)">
              <i class="fa fa-redo"></i> Reset
            </button>
          </td>
        </tr>
        <?php endforeach; ?>

      </tbody>
    </table>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editMissionModal">
  <div class="modal-dialog">
    <form id="editMissionForm" class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Sửa nhiệm vụ</h4>
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="mission-id">

        <div class="form-group">
          <label>Nhiệm vụ</label>
          <input type="text" class="form-control" name="missionName" id="mission-name">
        </div>

        <div class="form-group">
          <label>Status</label>
          <select class="form-control" name="status" id="mission-status">
            <option value="open">Open</option>
            <option value="close">Close</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" type="button" onclick="saveMission()">Lưu thay đổi</button>

      </div>
    </form>
  </div>
</div>
<?php require './include/footer.php'; ?>
<script>
// Fill modal form
function fillMissionForm(m) {
  $("#mission-id").val(m.id);
  $("#mission-name").val(m.missionName);
  $("#mission-status").val(m.status);
}

function saveMission() {

  $.post("./process/process.php",
    $("#editMissionForm").serialize() + "&action=edit_mission",
    function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 900);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}


// Reset mission submissions
function resetMission(id) {
  if (!confirm("Reset sẽ xóa toàn bộ dữ liệu của nhiệm vụ. Tiếp tục?")) return;

  $.post("./process/process.php", { action: "reset_mission", id }, function(res) {
    if (res.status === "success") {
      toastr.success(res.message);
      setTimeout(() => location.reload(), 800);
    } else {
      toastr.error(res.message);
    }
  }, "json");
}
$('#mission_menu').addClass('menu-open');
</script>


