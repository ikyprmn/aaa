document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const totalPriceSpan = document.getElementById('totalPrice');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const dummyQrImage = document.getElementById('dummyQrImage');
    const qrMessage = document.getElementById('qrMessage');
    const paymentDoneBtn = document.getElementById('paymentDoneBtn'); // Dapatkan tombol baru

    function calculateTotalPrice() {
        let total = 0;
        quantityInputs.forEach(input => {
            const menuId = input.dataset.menuId || input.id.split('-')[1];
            const parentDiv = input.closest('.order-item');
            const price = parseFloat(parentDiv.dataset.price);
            const quantity = parseInt(input.value);

            if (!isNaN(price) && !isNaN(quantity) && quantity > 0) {
                total += price * quantity;
            }
        });
        totalPriceSpan.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', calculateTotalPrice);
        input.addEventListener('change', calculateTotalPrice);
    });

    placeOrderBtn.addEventListener('click', function() {
        const selectedItems = [];
        let canProceed = true;

        quantityInputs.forEach(input => {
            const menuId = input.id.split('-')[1];
            const quantity = parseInt(input.value);
            const currentStokElement = input.nextElementSibling;
            const currentStok = parseInt(currentStokElement.textContent.split(': ')[1]);

            if (quantity > 0) {
                if (quantity > currentStok) {
                    alert(`Maaf, stok untuk item ini tidak mencukupi. Stok tersedia: ${currentStok}`);
                    canProceed = false;
                }
                selectedItems.push({
                    menu_id: menuId,
                    quantity: quantity
                });
            }
        });

        if (!canProceed) {
            return;
        }

        if (selectedItems.length === 0) {
            alert('Mohon pilih setidaknya satu menu untuk dipesan.');
            return;
        }

        // Kirim data ke server (misalnya process_order.php)
        fetch('order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ items: selectedItems })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pesanan berhasil ditempatkan! Silakan lakukan pembayaran menggunakan QR Code.');
                dummyQrImage.style.display = 'block'; // Tampilkan QR
                qrMessage.style.display = 'none'; // Sembunyikan pesan
                paymentDoneBtn.style.display = 'block'; // Tampilkan tombol "Selesai Membayar"

                // Update stok di UI
                data.updated_stok.forEach(item => {
                    const stokElement = document.getElementById(`stok-${item.menu_id}`);
                    if (stokElement) {
                        stokElement.textContent = item.new_stok;
                    }
                    const inputElement = document.getElementById(`qty-${item.menu_id}`);
                    if (inputElement) {
                        inputElement.setAttribute('max', item.new_stok);
                        inputElement.value = 0; // Reset quantity to 0 after purchase
                        const currentStokSpan = inputElement.nextElementSibling;
                        if (currentStokSpan) {
                           currentStokSpan.textContent = `Stok: ${item.new_stok}`;
                        }
                    }
                });
                calculateTotalPrice(); // Reset total price after order
            } else {
                alert('Terjadi kesalahan saat memproses pesanan: ' + data.message);
                dummyQrImage.style.display = 'none';
                qrMessage.style.display = 'block';
                qrMessage.textContent = 'Gagal memproses pesanan. Coba lagi.';
                paymentDoneBtn.style.display = 'none'; // Sembunyikan tombol jika gagal
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
            dummyQrImage.style.display = 'none';
            qrMessage.style.display = 'block';
            qrMessage.textContent = 'Terjadi kesalahan koneksi.';
            paymentDoneBtn.style.display = 'none'; // Sembunyikan tombol jika error
        });
    });

    // Event listener untuk tombol "Selesai Membayar"
    paymentDoneBtn.addEventListener('click', function() {
        alert('Terima kasih! Pembayaran Anda akan diverifikasi. Silakan tunggu konfirmasi dari pihak kantin.');
        // Di sini Anda bisa menambahkan logika lebih lanjut, misalnya:
        // 1. Mengirim notifikasi ke sistem admin/penjual bahwa pesanan telah dibayar.
        // 2. Mengosongkan kembali keranjang belanja (jika ada).
        // 3. Menyembunyikan QR dan tombol "Selesai Membayar".
        dummyQrImage.style.display = 'none';
        paymentDoneBtn.style.display = 'none';
        qrMessage.style.display = 'block';
        qrMessage.textContent = 'Pilih menu dan klik \'Pesan Sekarang\' untuk melihat QR.';
        // Anda juga bisa mereset form pesanan jika diinginkan
        document.getElementById('orderForm').reset();
        calculateTotalPrice(); // Set total harga kembali ke 0
    });
});