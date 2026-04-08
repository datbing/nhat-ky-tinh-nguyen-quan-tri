<?php
require './include/header.php';


$response = call_api("GET", "/missions");

if ($response && isset($response['pendingSubs'])) {
    $subs = $response['pendingSubs'];
} else {
    $subs = [];
    echo "<div class='alert alert-warning'>Không thể lấy danh sách bài nộp từ hệ thống API.</div>";
}
?>

<div class="card">
  <div class="card-header bg-primary text-white">
    <h3 class="card-title">
      <i class="fas fa-clipboard-check mr-2"></i>
      Duyệt bài nhiệm vụ
    </h3>
  </div>

  <div class="card-body">

    <?php if (count($subs) === 0): ?>
      <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> Không có bài nào cần duyệt.
      </div>
    <?php endif; ?>

    <div class="row">
      <?php foreach ($subs as $s): ?>
        
        <div class="col-12 col-md-6 col-lg-4 mb-4">
          <div class="card shadow-sm h-100 border-0">
            
            <div class="card-header bg-light text-center border-bottom-0 pb-0">
              <h5 class="font-weight-bold text-dark mb-2">Bài nộp #<?= $s["id"] ?></h5>
            </div>

            <div class="card-body d-flex flex-column">
              
              <div class="text-center mb-2">
                <span class="badge badge-info p-2 mr-1">
                    <i class="fas fa-user-graduate mr-1"></i> <?= $s["studentId"] ?>
                </span>
                <span class="badge badge-warning p-2">
                    <i class="fas fa-tasks mr-1"></i> Mission: <?= $s["missionId"] ?>
                </span>
              </div>

              <?php if (!empty($s["note"])): ?>
              <div class="bg-white p-2 rounded mb-3 text-muted small font-italic border text-justify">
                <?= nl2br(htmlspecialchars($s["note"])) ?>
              </div>
              <?php endif; ?>

              <div class="text-center mb-3">
                <img src="<?= $s["imageLink"] ?>" 
                     class="img-fluid rounded border shadow-sm"
                     style="width: 100%; height: 200px; object-fit: cover; cursor:pointer"
                     onclick="openImage('<?= $s['imageLink'] ?>')">
              </div>

              <div class="mt-auto"></div>

              <hr class="my-2">

              <div class="row no-gutters">
                <div class="col-6 pr-1">
                    <button class="btn btn-success btn-block font-weight-bold btn-sm py-2"
                      onclick='approveNormal(<?= json_encode($s["id"]) ?>)'>
                      <i class="fas fa-check"></i> Duyệt
                    </button>
                </div>
                
                <div class="col-6 pl-1">
                    <button class="btn btn-primary btn-block font-weight-bold btn-sm py-2"
                      onclick='approveNews(<?= json_encode($s["id"]) ?>)'>
                      <i class="fas fa-bullhorn"></i> Đăng tin
                    </button>
                </div>
              </div>

              <div class="mt-2">
                <button class="btn btn-outline-danger btn-block btn-sm" 
                        onclick='deleteSubmission(<?= json_encode($s["id"]) ?>)'>
                  <i class="fas fa-trash-alt mr-1"></i> Xóa bài nộp này
                </button>
              </div>

            </div>
          </div>
        </div>

      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- MODAL XEM ẢNH -->
<div class="modal fade" id="imageModal">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-center">
      <img id="modalImage" src="" class="img-fluid rounded">
    </div>
  </div>
</div>

<?php require './include/footer.php'; ?>
<script>
function openImage(src) {
  $("#modalImage").attr("src", src);
  $("#imageModal").modal("show");
}

// DUYỆT THƯỜNG
function approveNormal(id) {
  if (!confirm("Duyệt bài này?")) return;

  $.post(
    "./process/process.php",
    { action: "approve_submission_normal", id: id },
    function (res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 600);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}

// DUYỆT & ĐĂNG NEWS
function approveNews(id) {
  if (!confirm("Duyệt và ĐĂNG lên mục Tin tức?")) return;

  $.post(
    "./process/process.php",
    { action: "approve_submission_news", id: id },
    function (res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 600);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}
// XÓA BÀI NỘP
function deleteSubmission(id) {
  if (!confirm("Xóa bài nộp này? Hành động này không thể hoàn tác.")) return;

  $.post(
    "./process/process.php",
    { action: "delete_submission", id: id },
    function (res) {
      if (res.status === "success") {
        toastr.success(res.message);
        setTimeout(() => location.reload(), 600);
      } else {
        toastr.error(res.message);
      }
    }, "json"
  );
}
$('#mission_menu').addClass('menu-open');
</script>