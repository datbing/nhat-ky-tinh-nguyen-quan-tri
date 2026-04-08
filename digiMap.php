<?php
require './include/header.php';
if ($user_info['adminLevel'] < 10) {
  echo "<script>location.href='/'</script>";
  exit();
}

$response = call_api("GET", "/digimap");

// Kiểm tra dữ liệu trả về
if ($response && isset($response['digi'])) {
    $digi = $response['digi'];
} else {
    $digi = []; // Phòng trường hợp API lỗi thì mảng rỗng để không văng lỗi foreach
    echo "<div class='alert alert-warning'>Không thể lấy dữ liệu từ hệ thống API.</div>";
}
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><b>Quản lý địa điểm</b></h3>
  </div>

  <div class="card-body">

    <!-- ADD NEW PIN -->
    <h5 class="mb-3"><b>Thêm điểm check-in mới</b></h5>

    <form id="addDigiForm" class="border p-3 rounded mb-4 bg-light">

      <div class="form-group">
        <label><b>Tên điểm / Pin Name</b></label>
        <input type="text" class="form-control" name="pinName" placeholder="VD: Cổng trường" required>
      </div>

      <div class="form-group">
        <label><b>Link Check-in</b></label>
        <input type="text" class="form-control" name="pinLink" placeholder="https://..." required>
      </div>

      <button type="button" class="btn btn-primary" onclick="addDigi()">
        <i class="fa fa-plus"></i> Thêm địa điểm
      </button>
    </form>

    <hr class="my-4">

    <h5 class="mb-3"><b>Danh sách các địa điểm</b></h5>

    <div class="digi-grid">

      <?php foreach ($digi as $d): ?>
      <div class="digi-card">

        <div class="card-body-custom">

          <p class="label-title">ID: <?= $d["id"] ?></p>

          <small><b>Tên điểm</b></small>
          <input type="text" class="form-control mb-2"
            id="name-<?= $d["id"] ?>"
            value="<?= htmlspecialchars($d["pinName"]) ?>">

          <small><b>Link check-in</b></small>
          <input type="text" class="form-control mb-2"
            id="link-<?= $d["id"] ?>"
            value="<?= htmlspecialchars($d["pinLink"]) ?>">

          <p class="mt-2"><b>Đã tham gia:</b> <?= intval($d["joined"]) ?> lượt</p>

          <div class="d-flex justify-content-around gap-2 mt-3">

            <button class="btn btn-success btn-sm"
              onclick="saveDigi(<?= $d['id'] ?>)">
              <i class="fa fa-save"></i> Lưu
            </button>

            <button class="btn btn-warning btn-sm"
              onclick="resetDigi(<?= $d['id'] ?>)">
              <i class="fa fa-redo"></i> Reset
            </button>

            <button class="btn btn-danger btn-sm"
              onclick="deleteDigi(<?= $d['id'] ?>)">
              <i class="fa fa-trash"></i> Xóa
            </button>

          </div>

        </div>

      </div>
      <?php endforeach; ?>

    </div>

  </div>
</div>

<script>
// ADD Pin
function addDigi(){
  $.post(
    "./process/process.php",
    $("#addDigiForm").serialize() + "&action=add_digi",
    function(res){
      if(res.status === "success"){
        toastr.success(res.message);
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}

// SAVE Pin
function saveDigi(id){
  const pinName = $("#name-"+id).val();
  const pinLink = $("#link-"+id).val();

  $.post(
    "./process/process.php",
    { action: "edit_digi", id, pinName, pinLink },
    function(res){
      if(res.status === "success"){
        toastr.success(res.message);
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}

// RESET joined
function resetDigi(id){
  if(!confirm("Reset lượt tham gia điểm này?")) return;

  $.post(
    "./process/process.php",
    { action: "reset_digi", id },
    function(res){
      if(res.status === "success"){
        toastr.success("Đã reset lượt tham gia!");
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}

// DELETE Pin
function deleteDigi(id){
  if(!confirm("Bạn có chắc chắn muốn xóa điểm này?")) return;

  $.post(
    "./process/process.php",
    { action: "delete_digi", id },
    function(res){
      if(res.status === "success"){
        toastr.success("Đã xóa điểm!");
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}

</script>
<style>
.digi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.digi-card {
  border: 1px solid #dcdcdc;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0px 2px 6px rgba(0,0,0,0.08);
  transition: 0.25s;
}
.digi-card:hover {
  transform: scale(1.02);
}
.card-body-custom {
  padding: 15px;
}
.label-title {
  font-weight: bold;
  font-size: 15px;
}
</style>
<?php require './include/footer.php'; ?>
