<?php
require 'function.php';
require 'cek.php';

// Membuat koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "stokbarang");

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Logika untuk memindahkan data ke tabel 'on' ketika qty kurang dari 30
$queryStock = "SELECT idbarang, namabarang, stock FROM stock";
$resultStock = mysqli_query($conn, $queryStock);

if (!$resultStock) {
    die("Kueri Gagal: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($resultStock)) {
    $idbarang = $row['idbarang'];
    $namabarang = $row['namabarang'];
    $qty = $row['stock'];

    // Cek apakah barang sudah ada di tabel 'on'
    $queryCheck = "SELECT * FROM `on` WHERE namabarang = '$namabarang'";
    $resultCheck = mysqli_query($conn, $queryCheck);

    if ($qty < 30) {
        if (mysqli_num_rows($resultCheck) == 0) {
            // Jika tidak ada di tabel 'on', tambahkan
            $status = 'Kurang dari 30 pcs';
            $queryInsert = "INSERT INTO `on` (namabarang, status, qty) VALUES ('$namabarang', '$status', $qty)";
            mysqli_query($conn, $queryInsert);
        } else {
            // Jika sudah ada, perbarui jumlahnya
            $queryUpdate = "UPDATE `on` SET qty = $qty, status = 'Kurang dari 30 pcs' WHERE namabarang = '$namabarang'";
            mysqli_query($conn, $queryUpdate);
        }
    } else {
        // Jika qty lebih dari atau sama dengan 30, hapus dari tabel 'on'
        if (mysqli_num_rows($resultCheck) > 0) {
            $queryDelete = "DELETE FROM `on` WHERE namabarang = '$namabarang'";
            mysqli_query($conn, $queryDelete);
        }
    }
}

// Logika untuk menghapus data dari tabel 'on' jika data dihapus dari tabel 'stock'
$queryDeletedStock = "SELECT namabarang FROM `on` WHERE namabarang NOT IN (SELECT namabarang FROM stock)";
$resultDeletedStock = mysqli_query($conn, $queryDeletedStock);

if (!$resultDeletedStock) {
    die("Kueri Gagal: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($resultDeletedStock)) {
    $namabarang = $row['namabarang'];
    $queryDelete = "DELETE FROM `on` WHERE namabarang = '$namabarang'";
    mysqli_query($conn, $queryDelete);
}

// Mengambil data dari tabel 'on' untuk ditampilkan
$queryOn = "SELECT namabarang, status, qty FROM `on`";
$resultOn = mysqli_query($conn, $queryOn);

if (!$resultOn) {
    die("Kueri Gagal: " . mysqli_error($conn));
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Data Barang Kurang dari 30 pcs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php">WAREHOUSE</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">FITUR</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            STOK BARANG
                        </a>
                        <a class="nav-link" href="masuk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            BARANG MASUK
                        </a>
                        <a class="nav-link" href="keluar.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            BARANG KELUAR
                        </a>
                        <a class="nav-link" href="logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                            LAPORAN PENJUALAN
                        </a>
                        <a class="nav-link" href="on.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            0N PROGRES
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="medium">BRILLANT</div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Barang Kurang dari 30 pcs</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Barang Kurang dari 30 pcs</li>
                    </ol>

                    <!-- Tabel data barang kurang dari 30 pcs -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table"></i> Data Barang Kurang dari 30 pcs
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Status</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($resultOn)) {
                                        echo "<tr>
                                                <td>{$row['namabarang']}</td>
                                                <td>{$row['status']}</td>
                                                <td>{$row['qty']}</td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>

<?php
// Menutup koneksi
mysqli_close($conn);
?>
