self.addEventListener('push', function (event) {
    const data = event.data.json();
    console.log('✅ Push event received!', data);

    const title = data.title || 'Pemberitahuan Baru Merallin'; // Fallback title
    const options = {
        body: data.body || 'Ada pembaruan penting untuk Anda.', // Fallback body
        icon: data.icon || '/images/logo/auth-logo128.svg', // Icon aplikasi
        badge: data.badge || '/images/logo/auth-logo128.svg', // Badge Android
        image: data.image || undefined, // Gambar besar, misal thumbnail foto
        vibrate: data.vibrate || [200, 100, 200, 100, 200], // Pola getar
        dir: data.dir || 'auto',
        lang: data.lang || 'id-ID', // Sesuaikan dengan lokasi
        tag: data.tag || 'general-notification', // Tag untuk grouping/replace
        renotify: data.renotify || true, // Notifikasi dengan tag sama bisa muncul lagi
        data: {
            url: data.data.url,
            notification_id: data.data.notification_id,
            trip_id: data.data.trip_id,
            type: data.data.type,
        },
        actions: [
            {
                action: 'view_trip',
                title: data.action_title || 'Lihat Detail',
                icon: data.action_icon || undefined
            },
        ]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Tambahkan event listener untuk menangani klik pada notifikasi
self.addEventListener('notificationclick', function(event) {
    const clickedNotification = event.notification;
    const primaryAction = event.action;
    const notificationData = clickedNotification.data;

    clickedNotification.close();

    const urlToOpen = notificationData.url || '/dashboard';

    event.waitUntil(
        clients.openWindow(urlToOpen)
    );
});
