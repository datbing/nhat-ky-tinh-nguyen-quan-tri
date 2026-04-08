<?php
  require './include/header.php';
  if ($user_info['adminLevel'] < 10) {
    echo "<script>location.href='/'</script>";
    exit();
  }
  

$page = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;

$response = call_api("GET", "/news?page=$page");

if ($response && isset($response['news'])) {
    $news       = $response['news'];
    $totalRows  = $response['pagination']['totalRows'];
    $totalPages = $response['pagination']['totalPages'];
} else {
    $news       = [];
    $totalRows  = 0;
    $totalPages = 0;
    echo "<div class='alert alert-danger'>Lỗi: Không thể lấy danh sách tin tức.</div>";
}
?>

<div class="card">

  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Quản lý tin tức (Bài đăng)</h3>
  </div>


  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th style="width: 60px">ID</th>
          <th>Nội dung</th>
          <th style="width: 150px">Tác giả</th>
          <th style="width: 140px">Ngày tạo</th>
          <th style="width: 90px">Ảnh</th>
          <th style="width: 100px">Hành động</th>
        </tr>
      </thead>

      <tbody>
      <?php foreach ($news as $n): ?>
        <tr>
          <td><?= $n["id"] ?></td>
          <td><?= nl2br(htmlspecialchars($n["content"])) ?></td>
          <td><?= htmlspecialchars($n["author"]["fullName"] ?? $n["user"]["fullName"] ?? "Ẩn danh") ?></td>
          <td><?= date("d/m/Y", strtotime($n["createdAt"])) ?></td>

          <td>
            <img src="<?= $n["imageUrl"] ?>"
                 onclick="showImage('<?= $n["imageUrl"] ?>')"
                 style="height:60px;width:60px;object-fit:cover;border-radius:5px;cursor:pointer;">
          </td>

          <td>
            <button class="btn btn-sm btn-danger"
                    onclick="deleteNews(<?= $n['id'] ?>)">
              <i class="fa fa-trash"></i>
            </button>
          </td>

        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- PAGINATION -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link"
               href="?page=<?= $i ?>">
              <?= $i ?>
            </a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <button class="btn btn-danger m-2" onclick="deleteAllNews()">Reset tất cả tin tức</button></br>
  </div>
</div>

<!-- ========================== -->
<!-- IMAGE PREVIEW MODAL -->
<!-- ========================== -->
<div class="modal fade" id="imagePreviewModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-2">
      <img id="preview-img" src="" style="width:100%;border-radius:6px;">
    </div>
  </div>
</div>

<script>
function showImage(url) {
  $("#preview-img").attr("src", url);
  $("#imagePreviewModal").modal("show");
}

function deleteNews(id) {
  if (!confirm("Xoá bài viết này?")) return;

  $.post(
    "./process/process.php",
    { action: "delete_news", id: id },
    function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}
function deleteAllNews() {
  if (!confirm("Xoá tất cả bài viết?")) return;

  $.post(
    "./process/process.php",
    { action: "delete_all_news" },
    function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 700);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}
</script>

<?php require './include/footer.php'; ?>
