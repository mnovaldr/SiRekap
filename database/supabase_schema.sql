-- ============================================================
-- SiREKAP MCC — SAFE VERSION (NO GENERATED COLUMN)
-- ============================================================

-- EXTENSIONS
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- ============================================================
-- ENUM
-- ============================================================
CREATE TYPE bidang_enum AS ENUM (
  'Komunitas','Bisnis','Akademisi','Pemerintah',
  'Media','Perorangan','Institusi Finansial'
);

CREATE TYPE jenis_kegiatan_enum AS ENUM (
  'Pertunjukan','Meeting','Workshop','Pelatihan',
  'Talkshow/Podcast','Take Konten Foto & Video',
  'Produksi Musik','Pameran','Kunjungan','Sosialisasi',
  'Kompetisi','Produksi Film','Voice Over','Interview',
  'Penelitian','Produksi Olahan','Konsultasi','Lainnya'
);

CREATE TYPE komersial_enum AS ENUM ('Komersial','Non Komersial');
CREATE TYPE status_data_enum AS ENUM ('Valid','Review','Duplikat');

CREATE TYPE lokasi_enum AS ENUM (
  'Studio Musik','Studio Foto',
  'Co-Working Space 1','Co-Working Space 2',
  'Food Lab','Multi Purpose Area',
  'Amphitheater 1','Amphitheater 2',
  'Multifunction Room',
  'Open Public Space Barat','Open Public Space Utara',
  'Ruang Meeting','Ruang Fashion','KIDZONE'
);

CREATE TYPE lantai_enum AS ENUM ('2','3','4','5');

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE public.users (
  id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  nama TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'operator'
    CHECK (role IN ('super_admin','admin','operator','viewer')),
  aktif BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================
-- EVENTS (NO GENERATED COLUMN)
-- ============================================================
CREATE TABLE public.events (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

  nama_organisasi TEXT NOT NULL,
  bidang bidang_enum NOT NULL,
  jenis_kegiatan jenis_kegiatan_enum NOT NULL,
  sub_sektor TEXT,
  komersial komersial_enum DEFAULT 'Non Komersial',
  judul_kegiatan TEXT NOT NULL,
  tanggal DATE NOT NULL,

  -- hasil trigger
  bulan TEXT,
  key_full TEXT,
  key_short TEXT,

  lantai lantai_enum NOT NULL,
  lokasi lokasi_enum NOT NULL,
  jumlah_peserta INT CHECK (jumlah_peserta > 0),

  email TEXT,
  no_telepon TEXT,
  pic TEXT,

  status_data status_data_enum DEFAULT 'Valid',
  catatan_review TEXT,

  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================================
-- INDEX
-- ============================================================
CREATE INDEX idx_events_tanggal ON public.events(tanggal);
CREATE INDEX idx_events_bulan ON public.events(bulan);
CREATE INDEX idx_events_key_full ON public.events(key_full);
CREATE INDEX idx_events_key_short ON public.events(key_short);

-- ============================================================
-- FUNCTIONS
-- ============================================================

-- format tanggal aman
CREATE OR REPLACE FUNCTION fn_format_date(p_tgl DATE)
RETURNS TEXT LANGUAGE sql AS $$
  SELECT
    EXTRACT(YEAR FROM p_tgl)::INT || '-' ||
    LPAD(EXTRACT(MONTH FROM p_tgl)::INT::TEXT,2,'0') || '-' ||
    LPAD(EXTRACT(DAY FROM p_tgl)::INT::TEXT,2,'0')
$$;

-- key full
CREATE OR REPLACE FUNCTION fn_make_key_full(
  p_org TEXT,
  p_judul TEXT,
  p_tgl DATE,
  p_lokasi TEXT,
  p_lantai TEXT
)
RETURNS TEXT LANGUAGE sql AS $$
  SELECT
    UPPER(TRIM(p_org)) || '||' ||
    UPPER(TRIM(p_judul)) || '||' ||
    fn_format_date(p_tgl) || '||' ||
    UPPER(TRIM(p_lokasi)) || '||' ||
    UPPER(TRIM(p_lantai))
$$;

-- key short
CREATE OR REPLACE FUNCTION fn_make_key_short(
  p_org TEXT,
  p_judul TEXT,
  p_tgl DATE
)
RETURNS TEXT LANGUAGE sql AS $$
  SELECT
    UPPER(TRIM(p_org)) || '||' ||
    UPPER(TRIM(p_judul)) || '||' ||
    fn_format_date(p_tgl)
$$;

-- bulan indonesia
CREATE OR REPLACE FUNCTION fn_nama_bulan(p_tgl DATE)
RETURNS TEXT LANGUAGE sql AS $$
  SELECT CASE EXTRACT(MONTH FROM p_tgl)::INT
    WHEN 1 THEN 'Januari'
    WHEN 2 THEN 'Februari'
    WHEN 3 THEN 'Maret'
    WHEN 4 THEN 'April'
    WHEN 5 THEN 'Mei'
    WHEN 6 THEN 'Juni'
    WHEN 7 THEN 'Juli'
    WHEN 8 THEN 'Agustus'
    WHEN 9 THEN 'September'
    WHEN 10 THEN 'Oktober'
    WHEN 11 THEN 'November'
    WHEN 12 THEN 'Desember'
  END
$$;

-- ============================================================
-- TRIGGER: AUTO FIELD
-- ============================================================
CREATE OR REPLACE FUNCTION fn_set_derived_fields()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
  NEW.bulan := fn_nama_bulan(NEW.tanggal);

  NEW.key_full := fn_make_key_full(
    NEW.nama_organisasi,
    NEW.judul_kegiatan,
    NEW.tanggal,
    NEW.lokasi::TEXT,
    NEW.lantai::TEXT
  );

  NEW.key_short := fn_make_key_short(
    NEW.nama_organisasi,
    NEW.judul_kegiatan,
    NEW.tanggal
  );

  NEW.updated_at := NOW();

  RETURN NEW;
END;
$$;

CREATE TRIGGER trg_set_derived_fields
BEFORE INSERT OR UPDATE ON public.events
FOR EACH ROW
EXECUTE FUNCTION fn_set_derived_fields();

-- ============================================================
-- TRIGGER: DUPLIKAT DETECTION
-- ============================================================
CREATE OR REPLACE FUNCTION fn_cek_duplikat()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
  v_full INT;
  v_short INT;
BEGIN
  SELECT COUNT(*) INTO v_full
  FROM public.events
  WHERE key_full = NEW.key_full
    AND id <> NEW.id;

  SELECT COUNT(*) INTO v_short
  FROM public.events
  WHERE key_short = NEW.key_short
    AND id <> NEW.id;

  IF v_full > 0 THEN
    NEW.status_data := 'Duplikat';
    NEW.catatan_review := 'Duplicate FULL';
  ELSIF v_short > 0 THEN
    NEW.status_data := 'Review';
    NEW.catatan_review := 'Duplicate SHORT';
  END IF;

  RETURN NEW;
END;
$$;

CREATE TRIGGER trg_cek_duplikat
BEFORE INSERT ON public.events
FOR EACH ROW
EXECUTE FUNCTION fn_cek_duplikat();

-- ============================================================
-- OPTIONAL: HARD DUPLICATE PREVENTION
-- ============================================================
CREATE UNIQUE INDEX idx_unique_key_full_valid
ON public.events(key_full)
WHERE status_data = 'Valid';

-- ============================================================
-- RLS (ROW LEVEL SECURITY)
-- ============================================================
ALTER TABLE public.events ENABLE ROW LEVEL SECURITY;

-- Policy agar semua orang bisa baca data (untuk Dashboard)
CREATE POLICY "Enable read access for all users" 
ON public.events FOR SELECT 
USING (true);

-- Policy agar user (anon/auth) bisa insert data (untuk Import/Form)
CREATE POLICY "Enable insert access for all users" 
ON public.events FOR INSERT 
WITH CHECK (true);

-- Policy agar user bisa update/delete (opsional, sesuaikan kebutuhan)
CREATE POLICY "Enable update for all users" 
ON public.events FOR UPDATE 
USING (true);

-- ============================================================
-- TEST INSERT
-- ============================================================
INSERT INTO public.events (
  nama_organisasi, bidang, jenis_kegiatan,
  judul_kegiatan, tanggal, lantai, lokasi, jumlah_peserta
)
VALUES (
  'Test Komunitas','Komunitas','Workshop',
  'Belajar SQL','2024-01-01','3','Ruang Meeting',20
);
