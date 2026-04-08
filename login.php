<?php
  session_start();
  if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
      header("Location: /");
      exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập | Nhật ký đoàn viên</title>
  <!-- favicon -->
  <link rel="icon" href="/assets/image/favicon.ico" type="image/x-icon">

  <link rel="stylesheet" href="plugins/toastr/toastr.min.css">
  <script src="plugins/jquery/jquery.min.js"></script>
  <script src="plugins/toastr/toastr.min.js"></script>

  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">

<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <img src="/assets/image/logo.png" width="80" height="80">
      <h3 class="mt-3"><b>Nhật ký đoàn viên</b></h3>
    </div>

    <div class="card-body">
      <p class="login-box-msg">Đăng nhập</p>

      <form id="loginForm">
        <input type="hidden" name="action" value="login">

        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
          <div class="input-group-append">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
          </div>
        </div>

        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
          <div class="input-group-append">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
      </form>
    </div>
  </div>
</div>


<script>
$("#loginForm").on("submit", function(e) {
    e.preventDefault();

    $.ajax({
        url: "/process/process.php",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",

        success: function(res) {
            if (res.status === "success") {
                toastr.success(res.message);
                setTimeout(() => {
                    window.location.href = "/";
                }, 1200);
            } else {
                toastr.error(res.message);
            }
        },
        error: function(err) {
            console.log(err);
            toastr.error("Không thể kết nối server!");
        }
    });
});
</script>

</body>
</html>
