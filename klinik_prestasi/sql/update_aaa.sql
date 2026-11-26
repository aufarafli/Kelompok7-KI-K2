-- ==========================================
-- Dokumentasi Perubahan Database Klinik Prestasi (AAA & Peer Review)
-- ==========================================

-- 1. Penambahan kolom tanggal_pengajuan pada tabel peer_review
ALTER TABLE public.peer_review 
ADD COLUMN IF NOT EXISTS tanggal_pengajuan DATE DEFAULT CURRENT_DATE;

UPDATE public.peer_review 
SET tanggal_pengajuan = CURRENT_DATE 
WHERE tanggal_pengajuan IS NULL;

-- 2. Pembuatan tabel audit_log untuk pencatatan aktivitas (Accounting)
CREATE TABLE IF NOT EXISTS public.audit_log (
    id_log BIGSERIAL PRIMARY KEY,
    id_user VARCHAR(20),
    nama_user VARCHAR(120),
    role_user VARCHAR(20),
    aksi VARCHAR(60) NOT NULL,
    modul VARCHAR(40) NOT NULL,
    target_id VARCHAR(40),
    keterangan TEXT,
    ip_address VARCHAR(45),
    waktu_kejadian TIMESTAMP WITH TIME ZONE DEFAULT (NOW() AT TIME ZONE 'Asia/Jakarta')
);

CREATE INDEX IF NOT EXISTS idx_audit_log_waktu ON public.audit_log (waktu_kejadian DESC);
CREATE INDEX IF NOT EXISTS idx_audit_log_user ON public.audit_log (id_user);