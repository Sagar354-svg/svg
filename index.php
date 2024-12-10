<?php
session_start();

// Simulating a simple user validation, replace this with actual DB authentication
$valid_username = "admin";
$valid_password = "123";

// Check if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // If logged in, show the attendance system
    $show_login = false;
} else {
    // Handle the login form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($username === $valid_username && $password === $valid_password) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $show_login = false;
        } else {
            $error_message = "Invalid username or password.";
            $show_login = true;
        }
    } else {
        $show_login = true;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Attendance System</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');

        * {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.15) 0%, rgba(0, 0, 0, 0.15) 100%), radial-gradient(at top center, rgba(255, 255, 255, 0.40) 0%, rgba(0, 0, 0, 0.40) 120%) #989898;
            background-blend-mode: multiply, multiply;
            background-attachment: fixed;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-form {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 10px;
            box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 91.5vh;
        }

        .attendance-container {
            height: 90%;
            width: 90%;
            border-radius: 20px;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .attendance-container>div {
            box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
            border-radius: 10px;
            padding: 30px;
        }

        .attendance-container>div:last-child {
            width: 64%;
            margin-left: auto;
        }
    </style>
</head>

<body>
    <?php if ($show_login) : ?>
        <!-- Login Page -->
        <div class="login-container">
            <div class="login-form">
                <h2>Login</h2>
                <?php if (isset($error_message)) : ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter Username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                    </div>
                    <button type="submit" class="btn btn-dark btn-block">Login</button>
                </form>
            </div>
        </div>
    <?php else : ?>
        <!-- QR Code Attendance System Page -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand ml-4" href="#">QR Code Attendance System</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="./index.php">Home <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="./masterlist.php">List of Students</a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item mr-3">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="main">
            <div class="attendance-container row">
                <div class="qr-container col-4">
                    <div class="scanner-con">
                        <h5 class="text-center">Scan your QR Code here for your attendance</h5>
                        <video id="interactive" class="viewport" width="100%"></video>
                    </div>

                    <div class="qr-detected-container" style="display: none;">
                        <form action="./endpoint/add-attendance.php" method="POST">
                            <h4 class="text-center">Student QR Detected!</h4>
                            <input type="hidden" id="detected-qr-code" name="qr_code">
                            <button type="submit" class="btn btn-dark form-control">Submit Attendance</button>
                        </form>
                    </div>
                </div>

                <div class="attendance-list col-8">
                    <h4>List of Present Students</h4>
                    <div class="table-container table-responsive">
                        <table class="table text-center table-sm" id="attendanceTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">SI_NO</th>
                                    <th scope="col">USN</th>
                                    <th scope="col">Course & Section</th>
                                    <th scope="col">Time In</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include('./conn/conn.php');

                                $stmt = $conn->prepare("SELECT * FROM tbl_attendance LEFT JOIN tbl_student ON tbl_student.tbl_student_id = tbl_attendance.tbl_student_id");
                                $stmt->execute();

                                $result = $stmt->fetchAll();

                                foreach ($result as $row) {
                                    $attendanceID = $row["tbl_attendance_id"];
                                    $studentName = $row["student_name"];
                                    $studentCourse = $row["course_section"];
                                    $timeIn = $row["time_in"];
                                ?>

                                    <tr>
                                        <th scope="row"><?= $attendanceID ?></th>
                                        <td><?= $studentName ?></td>
                                        <td><?= $studentCourse ?></td>
                                        <td><?= $timeIn ?></td>
                                        <td>
                                            <div class="action-button">
                                                <button class="btn btn-danger delete-button" onclick="deleteAttendance(<?= $attendanceID ?>)">X</button>
                                            </div>
                                        </td>
                                    </tr>

                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- instascan JS -->
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>

    <script>
        let scanner;

        function startScanner() {
            scanner = new Instascan.Scanner({ video: document.getElementById('interactive') });

            scanner.addListener('scan', function (content) {
                $("#detected-qr-code").val(content);
                $(".qr-detected-container").show();
            });

            Instascan.Camera.getCameras().then(function (cameras) {
                if (cameras.length > 0) {
                    scanner.start(cameras[0]);
                } else {
                    alert("No cameras found.");
                }
            }).catch(function (e) {
                console.error(e);
            });
        }

        startScanner();
    </script>
</body>

</html>
