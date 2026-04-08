<?php
require './include/header.php';
if ($user_info['adminLevel'] < 10) {
  echo "<script>location.href='/'</script>";
  exit();
}

$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = trim($_GET['search'] ?? "");
$limit  = 10;

$endpoint = "/members?page=$page&limit=$limit&search=" . urlencode($search);
$response = call_api("GET", $endpoint);

// 4. Kiểm tra và gán dữ liệu
if ($response && isset($response['members'])) {
    $members    = $response['members'];
    $totalRows  = $response['pagination']['totalRows'];
    $totalPages = $response['pagination']['totalPages'];
} else {
    $members    = [];
    $totalRows  = 0;
    $totalPages = 0;
    echo "<div class='alert alert-danger'>Không thể lấy danh sách đoàn viên từ API!</div>";
}
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Danh sách Đoàn viên (Tổng số: <?= $totalRows ?>)</h3>
  </div>

  <div class="card-body">
    <form class="form-inline mb-3" method="GET">
      <input
        type="text"
        name="search"
        value="<?= htmlspecialchars($search) ?>"
        class="form-control mr-2 w-25"
        placeholder="Tìm theo Mã Đoàn viên hoặc Tên"
      />
      <button class="btn btn-primary">Search</button>
    </form>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Mã ĐV</th>
          <th>Họ tên</th>
          <th>Chi đoàn</th>
          <th>Chức vụ</th>
          <th width="140px">Chỉnh sửa</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($members as $m): ?>
          <tr>
            <td><?= $m['studentId'] ?></td>
            <td><?= $m['fullName'] ?></td>
            <td><?= $m['unionGroup'] ?></td>
            <td><?= $m['position'] ?></td>

            <td align="center">
              <button class="btn btn-sm btn-primary"
                data-toggle="modal"
                data-target="#editMemberModal"
                onclick='fillEditForm(<?= json_encode($m) ?>)'>
                <i class="fa fa-edit"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- PAGINATION -->
    <div class="mt-3">
      <ul class="pagination justify-content-center"> <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
            <a class="page-link" href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">«</a>
        </li>

        <?php
        // Cấu hình số lượng trang hiển thị xung quanh trang hiện tại
        $delta = 2; // Ví dụ: đang ở trang 10 thì hiện 8,9,10,11,12
        
        // Luôn hiển thị trang 1
        $range = array(1);

        // Tính toán khoảng giữa cần hiển thị
        for ($i = max(2, $page - $delta); $i <= min($totalPages - 1, $page + $delta); $i++) {
            $range[] = $i;
        }

        // Luôn hiển thị trang cuối (nếu tổng trang > 1)
        if ($totalPages > 1) {
            $range[] = $totalPages;
        }

        // Logic hiển thị dấu "..."
        $prev = 0;
        foreach ($range as $i): 
            // Nếu khoảng cách giữa 2 trang trong danh sách hiển thị lớn hơn 1, in ra dấu "..."
            if ($prev > 0 && $i - $prev > 1): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; 
            
            $prev = $i; // Cập nhật trang trước đó
            ?>

            <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endforeach; ?>

        <li class="page-item <?= ($page >= $totalPages ? 'disabled' : '') ?>">
            <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search) ?>">»</a>
        </li>

      </ul>
    </div>

  </div>
</div>

<div class="card">
  <div class="card-body">
    <a href="/import_user" class="btn btn-success m-2"><i class="fa fa-file-excel mx-2"></i>Import từ Excel</a>
    <button class="btn btn-info m-2" data-toggle="modal" data-target="#createDemoModal">
        <i class="fa fa-user-plus mx-2"></i>Thêm tài khoản Demo
    </button>
  </div>
</div>

<div class="modal fade" id="createDemoModal">
  <div class="modal-dialog">
    <form id="createDemoForm" class="modal-content">

      <div class="modal-header bg-info text-white">
        <h4 class="modal-title">Tạo tài khoản Demo</h4>
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body">
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i> Mật khẩu mặc định sẽ là: <b>123456</b>
        </div>

        <div class="form-group">
          <label>Mã Đoàn viên (Student ID) <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="studentId" placeholder="VD: 00000001" required>
        </div>

        <div class="form-group">
          <label>Họ tên <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="fullName" placeholder="VD: Đề Văn Mô" required>
        </div>

        <div class="form-group">
          <label>Chi đoàn</label>
          <input type="text" class="form-control" name="unionGroup" placeholder="VD: 12A1">
        </div>

        <div class="form-group">
          <label>Chức vụ</label>
          <select class="form-control" name="position">
              <option value="Đoàn viên">Đoàn viên</option>
              <option value="Bí thư">Bí thư</option>
              <option value="Lớp trưởng">Lớp trưởng</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
        <button type="submit" class="btn btn-info">Tạo tài khoản</button>
      </div>

    </form>
  </div>
</div>

<div class="card">
  <div class="card-body d-flex">
    <button class="btn btn-danger m-2" onclick="deleteAllMembers()">Xóa hết tất cả đoàn viên</button></br>
    <button class="btn btn-danger m-2" onclick="resetPoints()">Reset điểm</button>
  </div>
</div>


<?php require './include/footer.php'; ?>
<!-- Modal sửa member -->
<div class="modal fade" id="editMemberModal">
  <div class="modal-dialog">
    <form id="editMemberForm" class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Sửa thông tin đoàn viên</h4>
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body">

        <div class="form-group">
          <label>Mã Đoàn viên <small class="text-muted">(Không thể thay đổi)</small></label>
          <input type="text" class="form-control" id="display-id" readonly 
                 style="background-color: #e9ecef; cursor: not-allowed;" name="studentId">
        </div>

        <div class="form-group">
          <label>Họ tên</label>
          <input type="text" class="form-control" id="edit-name" name="fullName" required>
        </div>

        <div class="form-group">
          <label>Chi đoàn</label>
          <input type="text" class="form-control" id="edit-group" name="unionGroup">
        </div>

        <div class="form-group">
          <label>Chức vụ</label>
          <input type="text" class="form-control" id="edit-position" name="position">
        </div>

        <hr>
        
        <label class="text-muted small">Công cụ quản trị:</label>
        <div class="row">
            <div class="col-6">
                <button type="button" onclick="resetPassword()" class="btn btn-warning btn-block">
                  <i class="fa fa-key"></i> Reset Pass
                </button>
            </div>
            <div class="col-6">
                <button type="button" onclick="deleteSingleMember()" class="btn btn-danger btn-block">
                  <i class="fa fa-trash"></i> Xóa User
                </button>
            </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
        <button class="btn btn-primary">Lưu thay đổi</button>
      </div>

    </form>
  </div>
</div>  


<script>
var modal_id;
function fillEditForm(m) {
  modal_id = m.studentId;
  $("#display-id").val(m.studentId);
  $("#edit-name").val(m.fullName);
  $("#edit-group").val(m.unionGroup);
  $("#edit-position").val(m.position);
}

$("#editMemberForm").submit(function(e) {
  e.preventDefault();
  $.ajax({
    url: "./process/process.php",
    type: "POST",
    data: $(this).serialize() + "&action=update_member",
    dataType: "json",
    success: function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        $("#editMemberModal").modal('hide');
        setTimeout(() => location.reload(), 1000);
      } else {
        toastr.error(res.message);
      }
    },
    error: function() {
        toastr.error("Lỗi kết nối server!");
    }
  });
});

function resetPassword() {
    let id = modal_id;
    if (!id) { toastr.error("Không tìm thấy ID!"); return; }

    if (!confirm("Reset mật khẩu về '123456'?")) return;

    $.ajax({
        url: "./process/process.php",
        type: "POST",
        data: { action: "reset_password", id: id },
        success: function(res) {
            // Giả sử server trả về JSON, parse nếu cần
            if(typeof res === 'string') res = JSON.parse(res);
            
            if (res.status === "success") toastr.success(res.message);
            else toastr.error(res.message);
        }
    });
}

function deleteSingleMember() {
    let id = $("#display-id").val();
    let name = $("#edit-name").val();

    if (!id) { toastr.error("Lỗi ID!"); return; }

    if (!confirm("CẢNH BÁO: Xóa vĩnh viễn thành viên: " + name + " (" + id + ")?")) return;

    $.ajax({
        url: "./process/process.php",
        type: "POST",
        data: { action: "delete_single_member", id: id },
        dataType: "json",
        success: function(res) {
            if (res.status === "success") {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(res.message);
            }
        },
        error: function() { toastr.error("Lỗi server!"); }
    });
}

function deleteAllMembers() {
    if (!confirm("Bạn có chắc muốn xóa hết tất cả đoàn viên? Hành động này không thể hoàn tác!")) {
        return;
    }

    $.ajax({
        url: "./process/process.php",
        type: "POST",
        data: {
            action: "delete_all_members",
            confirm: "yes"
        },
        dataType: "json",
        success: function(res) {
            if (res.status === "success") {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error("Lỗi server!");
        }
    });
}

function resetPoints() {
    if (!confirm("Bạn có chắc muốn reset điểm của tất cả đoàn viên?")) {
        return;
    }

    $.ajax({
        url: "./process/process.php",
        type: "POST",
        data: {
            action: "reset_points"
        },
        dataType: "json",
        success: function(res) {
            if (res.status === "success") {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error("Lỗi server!");
        }
    });
}

$("#createDemoForm").submit(function(e) {
  e.preventDefault();

  $.ajax({
    url: "./process/process.php",
    type: "POST",
    data: $(this).serialize() + "&action=create_demo_account",
    dataType: "json",
    success: function(res) {
      if (res.status === "success") {
        toastr.success(res.message);
        $("#createDemoModal").modal('hide');
        $("#createDemoForm")[0].reset(); // Xóa trắng form
        setTimeout(() => location.reload(), 1000);
      } else {
        toastr.error(res.message);
      }
    },
    error: function() {
      toastr.error("Lỗi kết nối server!");
    }
  });
});
</script>
