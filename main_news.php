<?php
require './include/header.php';
if ($user_info['adminLevel'] < 10) {
  echo "<script>location.href='/'</script>";
  exit();
}

$apiResponse = call_api("GET", "/digimap");

if ($apiResponse && isset($apiResponse['mainNews'])) {
    $mainNews = $apiResponse['mainNews'];
} else {
    $mainNews = [];
    echo "<div class='alert alert-warning'>Không thể kết nối API hoặc không có dữ liệu tin tức.</div>";
}
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><b>Quản lý Main News</b></h3>
  </div>

  <div class="card-body">

    <!-- ADD NEW ITEM -->
    <h5 class="mb-3"><b>Thêm bài mới</b></h5>

    <form id="addMainNewsForm" class="border p-3 rounded mb-4 bg-light">
      <div class="form-group">
        <label><b>Link bài</b></label>
        <input type="text" class="form-control" name="link" placeholder="https://..." required>
      </div>

      <div class="form-group">
        <label><b>Ảnh (URL)</b></label>
        <input type="text" class="form-control" name="image" placeholder="https://..." required>
      </div>

      <button type="button" class="btn btn-primary" onclick="addMainNews()">
        <i class="fa fa-plus"></i> Thêm bài
      </button>
    </form>

    <hr class="my-4">

    <!-- LIST MAIN NEWS -->
    <h5 class="mb-3"><b>Danh sách Main News</b></h5>

    <div class="main-news-grid">

      <?php foreach ($mainNews as $item): ?>
      <div class="news-card">

        <img src="<?= $item["image"] ?>">

        <div class="card-body-custom">

          <p class="label-title">ID: <?= $item["id"] ?></p>

          <small><b>Link:</b></small>
          <input type="text" class="form-control mb-2"
            id="link-<?= $item["id"] ?>"
            value="<?= htmlspecialchars($item["link"]) ?>">

          <small><b>Ảnh URL:</b></small>
          <input type="text" class="form-control mb-3"
            id="image-<?= $item["id"] ?>"
            value="<?= htmlspecialchars($item["image"]) ?>">

          <div class="d-flex justify-content-between">

            <button class="btn btn-success btn-sm"
              onclick="saveMainNews(<?= $item['id'] ?>)">
              <i class="fa fa-save"></i> Lưu
            </button>

            <button class="btn btn-danger btn-sm"
              onclick="deleteMainNews(<?= $item['id'] ?>)">
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
// ADD qua AJAX (không submit form)
function addMainNews(){
  $.post(
    "./process/process.php",
    $("#addMainNewsForm").serialize() + "&action=add_main_news",
    function(res){
      if(res.status === "success"){
        toastr.success(res.message);
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    },
    "json"
  );
}

// SAVE
function saveMainNews(id){
  const link = $("#link-"+id).val();
  const image = $("#image-"+id).val();

  $.post(
    "./process/process.php",
    { action: "edit_main_news", id, link, image },
    function(res){
      if(res.status === "success"){
        toastr.success(res.message || "Đã lưu!");
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    },
    "json"
  );
}

// DELETE
function deleteMainNews(id){
  if(!confirm("Bạn chắc chắn muốn xóa?")) return;

  $.post(
    "./process/process.php",
    { action: "delete_main_news", id },
    function(res){
      if(res.status === "success"){
        toastr.success("Đã xóa!");
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    },
    "json"
  );
}
</script>
<style>
.main-news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.news-card {
  border: 1px solid #dcdcdc;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0px 2px 6px rgba(0,0,0,0.08);
  transition: 0.2s;
}
.news-card:hover {
  transform: scale(1.02);
}
.news-card img {
  width: 100%;
  height: auto;
  object-fit: cover;
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
