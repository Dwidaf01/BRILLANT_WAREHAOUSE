<?php
require 'function.php';
require 'cek.php';

// Membuat koneksi
$conn = mysqli_connect("localhost", "root", "", "stokbarang");

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Variabel untuk menyimpan hasil query
$results = [];
$totalQuantity = 0; // Variabel untuk menyimpan total jumlah barang

// Cek jika form telah disubmit
if (isset($_POST['periode'])) {
    $periode = $_POST['periode'];
    
    // Menggunakan tanggal saat ini jika tidak ada tanggal yang dipilih
    $tanggal = date('Y-m-d');

    // Menentukan rentang tanggal berdasarkan periode
    if ($periode === 'hari') {
        $startDate = date('Y-m-d', strtotime($tanggal));
        $endDate = date('Y-m-d', strtotime($tanggal));
    } elseif ($periode === 'minggu') {
        $startDate = date('Y-m-d', strtotime($tanggal . ' -' . (date('N', strtotime($tanggal)) - 1) . ' days'));
        $endDate = date('Y-m-d', strtotime($tanggal . ' + ' . (7 - date('N', strtotime($tanggal))) . ' days'));
    } else { // Per bulan
        $startDate = date('Y-m-01', strtotime($tanggal));
        $endDate = date('Y-m-t', strtotime($tanggal));
    }

    // Mengambil data penjualan berdasarkan rentang tanggal
    $query = "SELECT stock.namabarang, keluar.tanggal, SUM(keluar.qty) as total_qty 
              FROM keluar 
              JOIN stock ON keluar.idbarang = stock.idbarang
              WHERE keluar.tanggal BETWEEN '$startDate' AND '$endDate' 
              GROUP BY keluar.idbarang, keluar.tanggal 
              ORDER BY keluar.tanggal DESC";

    $result = mysqli_query($conn, $query);

    // Periksa kesalahan
    if (!$result) {
        die("Kueri Gagal: " . mysqli_error($conn));
    }

    // Simpan hasil query dan hitung total
    while ($row = mysqli_fetch_assoc($result)) {
        $results[] = $row;
        $totalQuantity += $row['total_qty']; // Menambahkan ke total
    }
} else {
    // Jika tidak ada filter, ambil semua data
    $query = "SELECT stock.namabarang, keluar.tanggal, SUM(keluar.qty) as total_qty 
              FROM keluar 
              JOIN stock ON keluar.idbarang = stock.idbarang
              GROUP BY keluar.idbarang, keluar.tanggal 
              ORDER BY keluar.tanggal DESC";

    $result = mysqli_query($conn, $query);

    // Periksa kesalahan
    if (!$result) {
        die("Kueri Gagal: " . mysqli_error($conn));
    }

    // Simpan hasil query dan hitung total
    while ($row = mysqli_fetch_assoc($result)) {
        $results[] = $row;
        $totalQuantity += $row['total_qty']; // Menambahkan ke total
    }
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
    <title>Laporan Penjualan - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        /* CSS untuk menyembunyikan bagian luar tabel saat dicetak */
        @media print {
            body * {
                visibility: hidden;
            }
            #printableArea, #printableArea * {
                visibility: visible;
            }
            #printableArea {
                position: absolute;
                left: 0;
                top: 0;
                right: 0;
                bottom: 0;
                margin: 0;
                padding: 0;
            }
        }
    </style>
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
                        <a class="nav-link" href="jual.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            LAPORAN PENJUALAN
                        </a>
                        <a class="nav-link" href="on.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            0N PROGRES
                        </a>
                        <a class="nav-link" href="logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                            LOG OUT
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
                    <h1 class="mt-4">Laporan Penjualan</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Laporan Penjualan</li>
                    </ol>

                    <!-- Form untuk filter laporan -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-filter"></i> Pilih Periode
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="periode" class="form-label">Pilih Periode:</label>
                                    <select id="periode" name="periode" class="form-select">
                                        <option value="">-- Pilih Periode --</option>
                                        <option value="hari">Per Hari</option>
                                        <option value="minggu">Per Minggu</option>
                                        <option value="bulan">Per Bulan</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Tampilkan</button>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel laporan penjualan -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table"></i> Data Penjualan
                            <button class="btn btn-success float-end" onclick="printTable()">Print</button>
                        </div>
                        <div class="card-body" id="printableArea">
                            <table id="datatablesSimple" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($results)) {
                                        foreach ($results as $row) {
                                            echo "<tr>
                                                    <td>{$row['namabarang']}</td>
                                                    <td>{$row['tanggal']}</td>
                                                    <td>{$row['total_qty']}</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3'>Tidak ada data untuk ditampilkan.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div class="mt-3">
                                <strong>Total Barang Terjual: </strong><?php echo $totalQuantity; ?> pcs
                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
    <script>
        function printTable() {
            window.print();
        }
    </script>
</body>
</html>

<?php
// Menutup koneksi
mysqli_close($conn);
?>
