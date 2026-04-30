# Setup Database MySQL Laragon

## Langkah 1: Buat Database

### Opsi A: Via HeidiSQL (Recommended)
1. Buka **Laragon**
2. Klik **Menu** > **MySQL** > **HeidiSQL**
3. Di HeidiSQL:
   - Klik kanan pada connection (biasanya "Unnamed")
   - Pilih **"Create new"** > **"Database"**
   - Nama database: `mcc_rekap`
   - Collation: `utf8mb4_unicode_ci`
   - Klik **OK**

### Opsi B: Via phpMyAdmin
1. Buka **Laragon**
2. Klik **Menu** > **MySQL** > **phpMyAdmin**
3. Di phpMyAdmin:
   - Klik tab **"Databases"**
   - Database name: `mcc_rekap`
   - Collation: `utf8mb4_unicode_ci`
   - Klik **Create**

### Opsi C: Via Terminal Laragon
1. Buka **Laragon**
2. Klik **Menu** > **Terminal**
3. Jalankan:
   ```bash
   mysql -u root -e "CREATE DATABASE mcc_rekap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

## Langkah 2: Verifikasi Database

Di HeidiSQL atau phpMyAdmin, pastikan database `mcc_rekap` sudah ada.

## Langkah 3: Jalankan Migration

Buka terminal di folder `mcc-rekap-backend`, lalu jalankan:

```bash
php artisan migrate:fresh --seed
```

Ini akan:
- Drop semua tables (jika ada)
- Create tables: users, events, master_lokasi, master_kategori, organizers, activity_logs
- Seed data:
  - User admin (email: admin@mcc.com, password: password)
  - Master Bidang (7 items)
  - Master Jenis Kegiatan (18 items)
  - Master Lokasi (14 items)

## Troubleshooting

### Error: "Unknown database 'mcc_rekap'"
→ Database belum dibuat. Ikuti Langkah 1.

### Error: "Access denied for user 'root'@'localhost'"
→ Password MySQL salah. Update `.env`:
```env
DB_PASSWORD=your_mysql_password
```

### Error: "SQLSTATE[HY000] [2002] No connection could be made"
→ MySQL belum running. Start MySQL di Laragon:
1. Buka Laragon
2. Klik **Start All**

### Warning: "Deprecated: Constant PDO::MYSQL_ATTR_SSL_CA"
→ Ini hanya warning dari PHP 8.5, tidak masalah. Migration tetap jalan.

## Setelah Migration Berhasil

Anda akan melihat output seperti ini:

```
Dropped all tables successfully.
Migration table created successfully.
Migrating: 0001_01_01_000000_create_users_table
Migrated:  0001_01_01_000000_create_users_table (50.23ms)
Migrating: 0001_01_01_000001_create_cache_table
Migrated:  0001_01_01_000001_create_cache_table (30.45ms)
...
Seeding: Database\Seeders\MasterDataSeeder
Seeded:  Database\Seeders\MasterDataSeeder (10.23ms)
Database seeding completed successfully.
```

## Verifikasi Data

Cek di HeidiSQL/phpMyAdmin:

**Table `users`:**
- 1 row: admin@mcc.com

**Table `master_kategori`:**
- 25 rows (7 bidang + 18 jenis_kegiatan)

**Table `master_lokasi`:**
- 14 rows

**Table `events`:**
- 0 rows (kosong, siap untuk input)

---

**Next:** Setelah migration berhasil, lanjut install Filament dan build resources.
