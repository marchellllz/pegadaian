use sobatgadai;
SET GLOBAL event_scheduler = ON;
desc karyawan;
desc user_account;
desc produk;
desc nasabah_gadai;
desc gadai;
desc dfbunga;
desc aktivitas_kerja;
desc gadai_confirmed;
desc bayar;
desc log_bayar;
desc aktivitas_kerja;
ALTER TABLE gadai 
  ADD COLUMN denda_total float DEFAULT 0 AFTER denda;
 

show create table karyawan;
show tables;
show triggers;
show create table bayar;
alter table bayar add column cabang varchar(50) after no_gadai;
update bayar set cabang = 'majapahit';
CREATE TABLE gadai_confirmed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_gadai VARCHAR(100) NOT NULL,         -- Nomor gadai yang diverifikasi
    user_id varchar(20)NOT NULL,
    status_verifikasi ENUM('diterima','pending', 'ditolak') NOT NULL,
    catatan TEXT,                           -- Opsional: alasan/verifikasi tambahan
    tanggal_verifikasi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (no_gadai) REFERENCES gadai(no_gadai) ON DELETE CASCADE
);

CREATE TABLE log_bayar (
    no INT AUTO_INCREMENT PRIMARY KEY,
    aktivitas TEXT NOT NULL,
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    operated VARCHAR(50) NOT NULL
);

CREATE TABLE bayar (
    id_bayar VARCHAR(20) PRIMARY KEY,
    no_gadai VARCHAR(20),
    tanggal_bayar DATE NOT NULL,
    jumlah_bayar DECIMAL(15,2) NOT NULL,
    metode_bayar ENUM('cash','transfer') NOT NULL,
    bukti_bayar varchar(255)not null,
    keterangan varchar(10),
    status_bayar varchar(2),
    FOREIGN KEY (no_gadai) REFERENCES gadai(no_gadai)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);
alter table bayar modify column keterangan varchar(10);
show triggers;

update dfbunga set tarif = 2 where bunga ='denda';
create table aktivitas_kerja(
	no varchar(10),
    aktivitas varchar(100),
    tanggan datetime,
    operated varchar(30)
);
alter table aktivitas_kerja modify column no int primary key auto_increment;


update aktivitas_kerja set cabang = 'majapahit' where no between 1 and 72;
alter table logmasuk add column operated varchar(20);
select * from logmasuk;
select * from karyawan;
select * from user_account;
select * from dfbunga;
select * from aktivitas_kerja;
select * from gadai;
select * from nasabah_gadai;
select * from gadai_confirmed;
select * from bayar;
select * from log_bayar;
select sum(nilai),sum(bunga),sum(denda) as total_bayar from gadai where no_gadai='10/08/25/4';
select jumlah_bayar from bayar where no_gadai='10/08/25/4';

delete from bayar;
delete from log_bayar;
update nasabah_gadai set jml_transaksi = 0 where nomor_nasabah=1;

-- nanti upload di schema  password di hash 'adm123'--
insert into karyawan values('B999','marcel','085225158310','jalanjalan','laki-laki','2','supervisor','mataram');
insert into user_account values('B999','$2y$10$hOJH3Yn/q98aziIdlVm/QeTSit41Swqsk39xUEgnHrfSgw0piGwVq','supervisor','verified','mataram',now());
insert into dfbunga(no,bunga,tarif) values('3','bebas','0');
insert into dfbunga(no,bunga,tarif) values('2','denda','1');
insert into dfbunga(no,bunga,tarif) values('1','normal','5');

insert into dfbunga(no,bunga,tarif) values('4','admin','2');

INSERT INTO produk (kd_produk, jenis, deskripsi) VALUES
(1, 'GASUS', 'GADAI KHUSUS'),
(2, 'GATOR', 'GADAI KENDARAAN BERMOTOR'),
(3, 'GANIK', 'GADAI ELEKTRONIK'),
(4, 'GALIA', 'GADAI MULIA');


alter table log_jurnal add keterangan datetime after tanggal;
desc log_jurnal;
show create table saldo_akun;
ALTER TABLE saldo_akun 
MODIFY kode_akun VARCHAR(100);
desc saldo_akun;
 
alter table log_jurnal modify keterangan varchar(100);

select * from daftar_akun;
select * from log_jurnal;
select * from saldo_akun;

show create trigger after_insert_log_jurnal;
drop trigger after_insert_log_jurnal;
delete from log_jurnal;
delete from saldo_akun;



delimiter //
CREATE DEFINER=CURRENT_USER TRIGGER `after_insert_log_jurnal` AFTER INSERT ON `log_jurnal` FOR EACH ROW BEGIN
     DECLARE saldo_norm VARCHAR(6);
 
     -- Ambil jenis saldo normal untuk kode_akun dari daftar_akun
     SELECT saldo_normal INTO saldo_norm
     FROM daftar_akun
     WHERE kode_akun = NEW.kode_akun;
 
     -- Periksa jenis saldo normal dan update saldo yang sesuai
     IF saldo_norm = 'debit' THEN
         -- Jika saldo normal adalah 'debit', tambahkan debit dan set saldo kredit ke 0 jika ada kredit
         UPDATE saldo_akun
         SET debit = debit + COALESCE(NEW.debit, 0) - COALESCE(NEW.kredit, 0),
             kredit = 0
         WHERE kode_akun = NEW.kode_akun;
     ELSEIF saldo_norm = 'kredit' THEN
         -- Jika saldo normal adalah 'kredit', tambahkan kredit dan set saldo debit ke 0 jika ada debit
         UPDATE saldo_akun
         SET kredit = kredit + COALESCE(NEW.kredit, 0) - COALESCE(NEW.debit, 0),
             debit = 0
         WHERE kode_akun = NEW.kode_akun;
     END IF;
 END

//
delimiter ;




DELIMITER //

CREATE DEFINER=CURRENT_USER TRIGGER `after_insert_daftar_akun`
AFTER INSERT ON `daftar_akun`
FOR EACH ROW
BEGIN
    INSERT INTO `saldo_akun` (`kode_akun`, `debit`, `kredit`)
    VALUES (NEW.`kode_akun`, 0, 0);
END//

DELIMITER ;

DELIMITER //

CREATE DEFINER=CURRENT_USER TRIGGER `after_update_daftar_akun`
AFTER UPDATE ON `daftar_akun`
FOR EACH ROW
BEGIN
    UPDATE `saldo_akun`
    SET `kode_akun` = NEW.`kode_akun`
    WHERE `kode_akun` = OLD.`kode_akun`;
END//

DELIMITER ;




DELIMITER //

CREATE DEFINER=CURRENT_USER TRIGGER `after_delete_daftar_akun`
AFTER DELETE ON `daftar_akun`
FOR EACH ROW
BEGIN
    DELETE FROM `saldo_akun`
    WHERE `kode_akun` = OLD.`kode_akun`;
END//

DELIMITER ;



INSERT INTO `daftar_akun` VALUES ('1011','Kas Kecil','harta','Harta Lancar','debit'),('1012','Kas Bank','harta','Harta Lancar','debit'),('1111','Piutang Pinjaman Gadai','harta','Harta Lancar','debit'),('1112','Piutang Bunga Gadai','harta','Harta Lancar','debit'),('1121','Perlengkapan Kantor','harta','Harta Lancar','debit'),('1122','Persediaan Barang Lelang','harta','Harta Lancar','debit'),('1131','Uang Muka Sewa','harta','Harta Lancar','debit'),('1132','Uang Muka Iklan','harta','Harta Lancar','debit'),('1201','Tanah','harta','Harta Tetap','debit'),('1202','Bangunan','harta','Harta Tetap','debit'),('1203','Kendaraan','harta','Harta Tetap','debit'),('1204','Peralatan Kantor','harta','Harta Tetap','debit'),('1211','Akumulasi Penyusutan Bangunan','harta','Harta Tetap','kredit'),('1212','Akumulasi Penyusutan Kendaraan','harta','Harta Tetap','kredit'),('1213','Akumulasi Penyusutan Peralatan','harta','Harta Tetap','kredit'),('2101','Utang Usaha','utang','Utang Jangka Pendek','kredit'),('2102','Utang Bunga','utang','Utang Jangka Pendek','kredit'),('2103','Utang Pajak','utang','Utang Jangka Pendek','kredit'),('2111','Uang Kelebihan Lelang','utang','Utang Jangka Pendek','kredit'),('2201','Utang Bank','utang','Utang Jangka Panjang','kredit'),('2211','Pinjaman Obligasi','utang','Utang Jangka Panjang','kredit'),('3001','Modal Disetor','modal','Modal Pemilik','kredit'),('3002','Laba Ditahan','modal','Modal Operasional','kredit'),('3003','Cadangan Risiko Kredit','modal','Modal Lainnya','kredit'),('4001','Pendapatan Bunga Gadai','pendapatan','Pendapatan Operasional','kredit'),('4002','Pendapatan Admin','pendapatan','Pendapatan Operasional','kredit'),('4003','Pendapatan Lelang','pendapatan','Pendapatan Operasional','kredit'),('4004','Pendapatan Denda Keterlambatan','pendapatan','Pendapatan Operasional','kredit'),('5001','Beban Gaji','beban','Beban Operasional','debit'),('5002','Beban Listrik & Air','beban','Beban Operasional','debit'),('5003','Beban Operasional Lainnya','beban','Beban Operasional','debit'),('5111','Beban Kerugian Kredit Macet','beban','Beban Kerugian','debit'),('5211','Beban Penyusutan Bangunan','beban','Beban Penyusutan','debit'),('5212','Beban Penyusutan Kendaraan','beban','Beban Penyusutan','debit'),('5213','Beban Penyusutan Peralatan Kantor','beban','Beban Penyusutan','debit');
show triggers;