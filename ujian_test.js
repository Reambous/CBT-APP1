import http from 'k6/http';
import { check, sleep } from 'k6';

// KONFIGURASI BEBAN
export let options = {
    stages: [
        { duration: '30s', target: 50 }, // Naik bertahap ke 50 VUs
        { duration: '1m', target: 50 },  // Tahan di 50 VUs
        { duration: '30s', target: 0 },   
    ],
};

// Ganti bagian ini:
const BASE_URL = 'http://cbt-app.test'; // <--- Pakai domain lokal Laragon 

export default function () {
    // 1. Tentukan ID User secara acak (Sesuaikan rentang ID yang ada di DB-mu)
    let randomUserId = Math.floor(Math.random() * 500) + 1;
    
    // 2. Akses Jalur VIP (Bypass Login)
    let loginRes = http.get(`${BASE_URL}/k6-bypass/${randomUserId}`);
    
    check(loginRes, {
        '✅ Bypass Sukses': (r) => r.status === 200,
    });

    // 3. Akses Halaman Target (History)
    let historyRes = http.get(`${BASE_URL}/user/history`);

    check(historyRes, {
        '✅ Halaman History Terbuka': (r) => r.status === 200 && !r.url.includes('login'),
    });

    // Robot membaca data selama 2 detik
    sleep(2);
}