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

![b3](https://user-images.githubusercontent.com/2249323/129550653-da68f689-325b-4551-9cae-3132c4d93eb5.PNG)

4. Lakukan konfigurasi dengan mengisi _token_ dan beberapa opsi yang akan diaktifkan

![b4](https://user-images.githubusercontent.com/2249323/129550724-664b813f-e2d7-440d-8fbb-1c531eae1f06.PNG)

6. klik sambungkan

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
        
![b5](https://user-images.githubusercontent.com/2249323/129551148-3603837b-a39a-4d80-ad1b-f1279a847cde.PNG)
![b6](https://user-images.githubusercontent.com/2249323/129551344-0d7a6a00-9d5e-4cf3-aa8a-10e1ebd10e40.PNG)

3. Untuk menjadi akun terdaftar, pengguna harus melakukan aktivasi dengan mengikuti instruksi yang tersedia.
4. Opsi yang tersedia untuk akun terdaftar adalah :
    - OPAC
        - Adalah tautan laman utama SLiMS
    - Bantuan
        - Menampilkan ringkasan menu yang dapat digunakan  
        
     ![b7](https://user-images.githubusercontent.com/2249323/129551681-e5003e28-e39e-4070-b6c4-7f285de97846.PNG)  
        
    - Keanggotaan
        - Menampilkan detil data anggota        
        
    ![b8](https://user-images.githubusercontent.com/2249323/129551722-c0168152-4573-4b06-875b-27eda58470d4.PNG)
        
    - Pinjaman
        - Menampilkan daftar pinjaman terkini
        
    ![b9](https://user-images.githubusercontent.com/2249323/129552077-ceec19df-edfd-4464-b249-3e1eafeca5a3.PNG)
        
    - Perpanjangan
        - Digunakan untuk melakukan transaksi perpanjangan masa pinjam secara mandiri, dengan catatan :
          - Status keanggotaan masih aktif atau tidak ditangguhkan
          - Status koleksi belum jatuh tempo
          - Aturan perpanjangan mandiri mengikuti aturan pinjaman di sistem utama SLiMS
          - Hanya bisa dilakukan satu kali pada hari yang sama atau tidak setelah proses transaksi pinjam
          
     ![b11](https://user-images.githubusercontent.com/2249323/129552525-79cbfd94-facb-424f-ae66-74f5a2896dcb.PNG)
    
    - Denda
        - Menampilkan jumlah denda terkini

     ![b10](https://user-images.githubusercontent.com/2249323/129552657-4c584634-4757-4fdc-a3eb-ecaef226fc20.PNG)

5. Untuk bot menjadi interaktif, dapat menambahkan beberapa pertanyaan dan jawaban yang sudah disiapkan di menu **_sistem_** submenu _**Bot Auto Response**_. Untuk jawaban acak dipisahkan dengan karakter koma.

![b12](https://user-images.githubusercontent.com/2249323/129552857-761c9f86-d1f6-4d26-8245-2b6b504ed58d.PNG)

7. Untuk mengelola akun telegram terdaftar, menggunakan menu Keanggotaan sub menu Telegram Account. Pada menu ini, dari laman utama dapat mengirimkan pesan secara langsung ke masing-masing pengguna.

![b13](https://user-images.githubusercontent.com/2249323/129553076-c27e1d9e-72cb-42b2-823c-b3bb6328743c.PNG)

### Catatan :
1. Bahasa antarmuka bot telegram menyesuaikan dengan pengaturan bahasa di sistem

Plugins ini bersifat gratis, disebarluaskan secara bebas atau dimodifikasi sesuai dengan kebutuhan tanpa syarat apapun

atau dukungan pengembangan dengan donasi ke kami :

Rekening BRI an. _HERU SUBEKTI_
norek `6553-01-019162-53-5`


