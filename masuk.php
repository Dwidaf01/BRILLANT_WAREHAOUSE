<?php
require 'function.php';
require 'cek.php';

// Initialize variables
$currentBarcode = '';

// Handle barcode scan
if (isset($_POST['scanBarcode'])) {
    $currentBarcode = $_POST['barcode'];
    // Fetch the item based on the scanned barcode
    $itemQuery = mysqli_query($conn, "SELECT * FROM stock WHERE namabarang = '$currentBarcode'");
    
    if ($itemData = mysqli_fetch_array($itemQuery)) {
        $currentBarcode = $itemData['namabarang'];
        $idbarang = $itemData['idbarang'];
        
        // Check if a manual quantity was provided; if not, default to 1
        $manualQty = !empty($_POST['manualQty']) ? (int)$_POST['manualQty'] : 1;

        // Check if this item already exists in 'masuk'
        $existingItemQuery = mysqli_query($conn, "SELECT * FROM masuk WHERE idbarang = $idbarang");
        
        if (mysqli_num_rows($existingItemQuery) > 0) {
            // If it exists, update the quantity
            $existingItemData = mysqli_fetch_array($existingItemQuery);
            $currentQty = $existingItemData['qty'] + $manualQty; // Add manual quantity
            
            // Update the 'masuk' table
            mysqli_query($conn, "UPDATE masuk SET qty = $currentQty WHERE idmasuk = " . $existingItemData['idmasuk']);
        } else {
            // If it doesn't exist, insert a new record with the specified manual quantity
            mysqli_query($conn, "INSERT INTO masuk (idbarang, qty, keterangan, tanggal) VALUES ($idbarang, $manualQty, '', NOW())");
        }
        
        // Increase stock by the specified quantity
        mysqli_query($conn, "UPDATE stock SET stock = stock + $manualQty WHERE idbarang = $idbarang");
    } else {
        echo "Item not found in stock.";
    }
}

// Handle delete action
if (isset($_POST['hapusbarangmasuk'])) {
    $idk = $_POST['idk'];

    // Fetch the quantity before deletion
    $query = mysqli_query($conn, "SELECT * FROM masuk WHERE idmasuk = '$idk'");
    $data = mysqli_fetch_array($query);
    
    if ($data) {
        $qtyToDelete = $data['qty'];
        $idbarang = $data['idbarang'];

        // Delete the entry from 'masuk'
        mysqli_query($conn, "DELETE FROM masuk WHERE idmasuk = '$idk'");

        // Decrease stock by the quantity of the deleted entry
        mysqli_query($conn, "UPDATE stock SET stock = stock - $qtyToDelete WHERE idbarang = $idbarang");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>BARANG MASUK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-fluid {
            margin-top: 20px;
        }
        .card {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php"> WAREHOUSE</a>
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
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
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
                    <h1 class="mt-4">BARANG MASUK</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>

                    <div class="card mb-4">
                        <div class="card-header">
                            <form method="post" id="barcodeForm">
                                <input type="text" name="barcode" id="barcode" placeholder="Scan Barcode" class="form-control" required autofocus />
                                <input type="number" name="manualQty" id="manualQty" placeholder="Manual Quantity (optional)" class="form-control mt-2" min="1" />
                                <button type="submit" name="scanBarcode" class="btn btn-primary mt-2">Scan</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM masuk m, stock s WHERE s.idbarang = m.idbarang");
                                    while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
                                        $idk = $data['idmasuk'];
                                        $tanggal = $data['tanggal'];
                                        $namabarang = $data['namabarang'];
                                        $qty = $data['qty'];
                                    ?>
                                    <tr>
                                        <td><?= $tanggal; ?></td>
                                        <td><?= $namabarang; ?></td>
                                        <td><?= $qty; ?></td>
                                        <td>
                                            <form method="post" action="masuk.php">
                                                <input type="hidden" name="idk" value="<?= $idk; ?>">
                                                <button type="submit" class="btn btn-danger" name="hapusbarangmasuk">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; 2024</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
