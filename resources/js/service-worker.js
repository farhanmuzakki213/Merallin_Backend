self.addEventListener('push', function (event) {
    console.log('✅ Push event received!', event.data.json());

    const data = event.data.json();
    const title = data.title || 'Pemberitahuan Baru';
    const options = {
        body: data.body,
        icon: data.icon || '/favicon.png', // Icon default jika tidak ada
        badge: data.badge || '/badge.png', // Badge (khusus Android)
        image: data.image || undefined, // Gambar besar di notifikasi
        vibrate: [200, 100, 200], // Efek getar
        data: {
            url: data.data.url,
            notification_id: data.data.notification_id
        },
        actions: [
            {
                action: 'view_trip',
                title: 'Lihat Detail',
                icon: data.action_icon || undefined // Icon untuk tombol aksi
            },
            // Anda bisa tambahkan aksi lain jika diperlukan
        ]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Tambahkan event listener untuk menangani klik pada notifikasi
self.addEventListener('notificationclick', function(event) {
    const clickedNotification = event.notification;
    const primaryAction = event.action; // Ini akan menjadi 'view_trip' jika tombol aksi diklik

    clickedNotification.close();

    const urlToOpen = clickedNotification.data.url;

    // Menandai notifikasi sebagai dibaca di backend jika memungkinkan
    // Ini membutuhkan endpoint khusus, atau bisa ditangani saat user membuka URL
    // fetch('/api/mark-notification-read/' + clickedNotification.data.notification_id, { method: 'POST' });

    event.waitUntil(
        clients.openWindow(urlToOpen)
    );
});
