<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Makanan Kantin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="#about-kantin">About Kantin</a></li>
                <li><a href="#cafetaria-list">Cafetaria List</a></li>
                <li><a href="#how-to-buy">How To Buy</a></li>
                <li><a href="#contact-us">Contact Us</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="about-kantin" class="content-section">
            <h2>About Kantin</h2>
            <div class="about-content">
                <div class="media-container">
                    <img src="images/canteen.png" alt="Gambar Kantin" class="kantin-image">
                    <div class="video-responsive">
                    <iframe class="kantin-video"
                        src="https://www.youtube.com/embed/JfOCkNr0aqA?si=B61wxlScwDN_cAiD"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
                </div>
                <img src="images/logo.png" alt="Logo Kantin" class="kantin-logo">
                <p>Selamat datang di pusat kuliner sekolah kami! Kantin sekolah menyediakan beragam pilihan makanan dan minuman yang lezat, higienis, dan terjangkau untuk seluruh warga sekolah. Kami berkomitmen untuk menyajikan hidangan berkualitas yang memenuhi standar gizi, memastikan Anda tetap berenergi sepanjang hari.</p>
            </div>
        </section>

        <hr>

        <section id="cafetaria-list" class="content-section">
            <h2>Cafetaria List</h2>
            <div class="cafetaria-container">
                <?php
                $sql_kantin = "SELECT * FROM kantin";
                $result_kantin = $conn->query($sql_kantin);

                if ($result_kantin->num_rows > 0) {
                    while($row_kantin = $result_kantin->fetch_assoc()) {
                        echo "<div class='kantin-item'>";
                        echo "<h3>" . $row_kantin["nama_kantin"] . "</h3>";
                        if (!empty($row_kantin["foto_kantin"])) {
                            echo "<img src='images/" . $row_kantin["foto_kantin"] . "' alt='" . $row_kantin["nama_kantin"] . "' class='kantin-photo'>";
                        }
                        echo "<h4>Daftar Menu:</h4>";
                        echo "<div class='menu-list'>";

                        $id_kantin = $row_kantin["id"];
                        $sql_menu = "SELECT * FROM menu WHERE id_kantin = $id_kantin";
                        $result_menu = $conn->query($sql_menu);

                        if ($result_menu->num_rows > 0) {
                            while($row_menu = $result_menu->fetch_assoc()) {
                                echo "<div class='menu-item'>";
                                echo "<img src='images/" . $row_menu["foto_menu"] . "' alt='" . $row_menu["nama_menu"] . "' class='menu-photo'>";
                                echo "<p><strong>" . $row_menu["nama_menu"] . "</strong></p>";
                                echo "<p>Harga: Rp " . number_format($row_menu["harga"], 0, ',', '.') . "</p>";
                                echo "<p>Stok: <span id='stok-" . $row_menu["id"] . "'>" . $row_menu["stok"] . "</span></p>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>Belum ada menu untuk kantin ini.</p>";
                        }
                        echo "</div>"; // .menu-list
                        echo "</div>"; // .kantin-item
                    }
                } else {
                    echo "<p>Belum ada kantin yang terdaftar.</p>";
                }
                ?>
            </div>
        </section>

        <hr>

        <section id="how-to-buy" class="content-section">
            <h2>How To Buy</h2>
            <div class="how-to-buy-content">
                <div class="menu-selection">
                    <h3>Pilih Menu Anda:</h3>
                    <form id="orderForm">
                        <div id="menu-options-container">
                            <?php
                            // Mengambil semua menu dari semua kantin
                            $sql_all_menu = "SELECT m.id, m.nama_menu, m.harga, m.stok, k.nama_kantin
                                             FROM menu m JOIN kantin k ON m.id_kantin = k.id";
                            $result_all_menu = $conn->query($sql_all_menu);

                            if ($result_all_menu->num_rows > 0) {
                                while($row_all_menu = $result_all_menu->fetch_assoc()) {
                                    echo "<div class='order-item' data-menu-id='" . $row_all_menu["id"] . "' data-price='" . $row_all_menu["harga"] . "' data-stok='" . $row_all_menu["stok"] . "'>";
                                    echo "<p>" . $row_all_menu["nama_kantin"] . " - " . $row_all_menu["nama_menu"] . " (Rp " . number_format($row_all_menu["harga"], 0, ',', '.') . ")</p>";
                                    echo "<label for='qty-" . $row_all_menu["id"] . "'>Qty:</label>";
                                    echo "<input type='number' id='qty-" . $row_all_menu["id"] . "' class='quantity-input' min='0' value='0' max='" . $row_all_menu["stok"] . "'>";
                                    echo "<span class='current-stok'>Stok: " . $row_all_menu["stok"] . "</span>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p>Tidak ada menu yang tersedia untuk pembelian.</p>";
                            }
                            ?>
                        </div>
                        <p class="total-price-display">Total Harga: <span id="totalPrice">Rp 0</span></p>
                        <button type="button" id="placeOrderBtn">Pesan Sekarang</button>
                    </form>
                </div>

                <div class="qr-display">
                    <h3>QR Pembayaran Anda:</h3>
                    <div id="qrCodeContainer">
                        <img src="qr/dummy.png" alt="Dummy QR Code" id="dummyQrImage" style="display:none;">
                        <p id="qrMessage">Pilih menu dan klik 'Pesan Sekarang' untuk melihat QR.</p>
                        <button type="button" id="paymentDoneBtn" style="display:none; margin-top: 15px;">Selesai Membayar</button>
                        </div>
                </div>
            </div>
        </section>

        <hr>

        <section id="contact-us" class="content-section">
            <h2>Contact Us</h2>
            <form action="contact.php" method="POST" class="contact-form">
                <label for="name">Nama:</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="message">Pesan/Kritik:</label>
                <textarea id="message" name="message" rows="5" required></textarea>

                <button type="submit">Kirim Pesan</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> RIZKY PERMANA XI 13</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
<?php $conn->close(); ?>