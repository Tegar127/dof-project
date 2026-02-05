# Laporan Pengembangan Fitur Perjanjian Kerja Sama

## Uraian Aktivitas
Saya telah berhasil mengimplementasikan fitur pembuatan dokumen baru tipe Perjanjian Kerja Sama (PKS) yang dilengkapi dengan sistem tanda tangan paraf dinamis berurutan dari kiri ke kanan secara otomatis. Aktivitas ini mencakup pembaruan skema database, pembuatan controller, pengaturan routing, hingga pengembangan UI/UX pada bagian editor dan generator dokumen agar sesuai dengan standar template resmi PT ASABRI (Persero).

## Pembelajaran yang Diperoleh
Saya memperoleh pemahaman mendalam mengenai teknik sinkronisasi data real-time antara input dinamis di sidebar dengan pratinjau dokumen berbasis tabel HTML yang kompleks. Selain itu, saya mempelajari cara menerapkan logika urutan tanda tangan (sequential signing) untuk memastikan validitas proses paraf, serta mengoptimalkan penggunaan library html2pdf.js agar hasil ekspor PDF tetap rapi dan profesional di berbagai perangkat.

## Kendala yang Dialami
Kendala utama yang dihadapi adalah menjaga konsistensi tata letak tabel paraf yang memiliki jumlah kolom fleksibel saat dirender ke dalam format PDF, terutama dalam menangani asinkronitas pemuatan gambar tanda tangan. Selain itu, sinkronisasi state antara Alpine.js di halaman editor dengan Vanilla JS di halaman generator memerlukan ketelitian ekstra agar fungsionalitas penyorotan sel (hover highlight) berjalan dengan lancar tanpa bug visual.