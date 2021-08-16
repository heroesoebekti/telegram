### ****PETUNJUK****  
  
### Prasyarat :  
1. Digunakan untuk SLiMS versi 9.4.2 dan selanjutnya
2. SLiMS harus online dan mendukung protokol https atau  
localhost dengan tunneling _ngrok_ (https://ngrok.com/docs)  
3. Membuat akun bot telegram dengan mendaftar di https://t.me/botfather, kemudian catat untuk token (nama bot dan username, bersifat unik dan tidak mengikat)

![b1](https://user-images.githubusercontent.com/2249323/129546407-acedd0da-26ce-4e85-9c27-3f0d28b5b28f.PNG)
![b2](https://user-images.githubusercontent.com/2249323/129546502-7d8127d6-4bf1-4ed7-9f00-28efe26ef4ff.PNG)


### Instalasi :
1. Salin folder telegram ke ke folder _**plugins**_
2. Aktifkan plugins telegram melalui menu _**system - plugins**_
3. Lakukan konfigurasi dengan mengisi _token_ dan beberapa opsi yang akan diaktifkan
4. klik sambungkan

### Penggunaan :
1. Bot telegram tidak akan merespon sebelum ada interaksi dengan pengguna, karena itu pengguna harus melakukan obrolan terlebih dahulu dengan bot dengan memanggil username bot
2. Sebelum melakukan aktivasi, bot hanya dapat menerima perintah yang bersifat umum. Dalam hal ini sementara menu yang tersedia untuk akun publik adalah  :
    - OPAC
        - Tautan laman utama SLiMS
    - Aktivasi
        - Digunakan mendaftarkan akun telegram dengan sistem utama, dibutuhkan parameter nomor keanggotaan dan password pengguna untuk proses ini.
        - Satu akun telegram hanya dapat digunakan satu member
        - Proses hapus akun hanya dapat dilakukan di sistem utama SLiMS
    - Bantuan
        - Menampilkan ringkasan menu yang dapat digunakan
3. Untuk menjadi akun terdaftar, pengguna harus melakukan aktivasi dengan mengikuti instruksi yang tersedia.
4. Opsi yang tersedia untuk akun terdaftar adalah :
    - OPAC
        - Adalah tautan laman utama SLiMS
    - Bantuan
        - Menampilkan ringkasan menu yang dapat digunakan
    - Keanggotaan
        - Menampilkan detil data anggota
    - Pinjaman
        - Menampilkan daftar pinjaman terkini
    - Perpanjangan
        - Digunakan untuk melakukan transaksi perpanjangan masa pinjam secara mandiri, dengan catatan :
          a.  Status keanggotaan masih aktif atau tidak ditangguhkan
          b. Status koleksi belum jatuh tempo
          c. Aturan perpanjangan mandiri mengikuti aturan pinjaman di sistem utama SLiMS
    - Denda
        - Menampilkan jumlah denda terkini

5. Untuk bot menjadi interaktif, dapat menambahkan beberapa pertanyaan dan jawaban yang sudah disiapkan di menu **_sistem_** submenu _**Bot Auto Response**_. Untuk jawaban acak dipisahkan dengan karakter koma.
6. Untuk mengelola akun telegram terdaftar, menggunakan menu Keanggotaan sub menu Telegram Account. Pada menu ini, dari laman utama dapat mengirimkan pesan secara langsung ke masing-masing pengguna.

### Catatan :
1. Bahasa antarmuka bot telegram menyesuaikan dengan pengaturan bahasa di sistem
