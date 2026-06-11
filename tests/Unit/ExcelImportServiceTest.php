<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ExcelImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

/**
 * Unit Test untuk ExcelImportService
 */
class ExcelImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExcelImportService $service;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service    = new ExcelImportService();
        $this->reflection = new ReflectionClass(ExcelImportService::class);
    }

    private function callPrivate(string $method, mixed ...$args): mixed
    {
        $m = $this->reflection->getMethod($method);
        $m->setAccessible(true);
        return $m->invoke($this->service, ...$args);
    }

    // =========================================================================
    // parseDate()
    // =========================================================================

    #[Test]
    public function parse_date_returns_null_for_null_input(): void
    {
        $this->assertNull($this->service->parseDate(null));
    }

    #[Test]
    public function parse_date_returns_null_for_empty_string(): void
    {
        $this->assertNull($this->service->parseDate(''));
    }

    #[Test]
    public function parse_date_parses_iso_format(): void
    {
        $this->assertEquals('2024-06-15', $this->service->parseDate('2024-06-15'));
        $this->assertEquals('2024-06-15', $this->service->parseDate('2024-06-15 00:00:00'));
    }

    #[Test]
    public function parse_date_parses_indonesian_month_name_format(): void
    {
        $this->assertEquals('2024-01-01', $this->service->parseDate('01 Januari 2024'));
        $this->assertEquals('2024-02-14', $this->service->parseDate('14 Februari 2024'));
        $this->assertEquals('2024-08-17', $this->service->parseDate('17 Agustus 2024'));
        $this->assertEquals('2024-12-25', $this->service->parseDate('25 Desember 2024'));
    }

    #[Test]
    public function parse_date_strips_day_of_week_prefix(): void
    {
        $this->assertEquals('2024-02-08', $this->service->parseDate('Kamis, 08 Februari 2024'));
        $this->assertEquals('2024-03-04', $this->service->parseDate('Senin, 04 Maret 2024'));
        $this->assertEquals('2024-07-05', $this->service->parseDate('Jumat, 05 Juli 2024'));
    }

    #[Test]
    public function parse_date_parses_slash_format_dd_mm_yyyy(): void
    {
        $this->assertEquals('2024-01-05', $this->service->parseDate('05/01/2024'));
        $this->assertEquals('2024-12-31', $this->service->parseDate('31/12/2024'));
    }

    #[Test]
    public function parse_date_parses_excel_serial_number(): void
    {
        // Excel serial 45292 = 2024-01-01
        $result = $this->service->parseDate(45292);
        $this->assertNotNull($result);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
        $this->assertStringStartsWith('2024', $result);
    }

    #[Test]
    public function parse_date_handles_abbreviated_month_names(): void
    {
        $this->assertEquals('2024-01-10', $this->service->parseDate('10 Jan 2024'));
        $this->assertEquals('2024-09-20', $this->service->parseDate('20 Sep 2024'));
        $this->assertEquals('2024-12-01', $this->service->parseDate('01 Des 2024'));
    }

    #[Test]
    public function parse_date_strips_english_day_of_week_prefix(): void
    {
        $this->assertEquals('2025-02-12', $this->service->parseDate('Wednesday, 12 Februari 2025'));
        $this->assertEquals('2025-03-05', $this->service->parseDate('Thursday, 05 Maret 2025'));
        $this->assertEquals('2024-08-16', $this->service->parseDate('Friday, 16 Agustus 2024'));
    }

    #[Test]
    public function parse_date_returns_null_for_ambiguous_text_with_year(): void
    {
        // Bug regression: teks judul kegiatan yang mengandung tahun seharusnya TIDAK ter-parse sebagai tanggal
        $this->assertNull($this->service->parseDate('FIRST MEETING & WELCOMING PARTY PERIODE 2025-2026'));
        $this->assertNull($this->service->parseDate('bukan tanggal'));
        $this->assertNull($this->service->parseDate('Wednesday')); // nama hari saja tanpa tanggal
        $this->assertNull($this->service->parseDate('Kamis')); // nama hari Indonesia saja
    }

    #[Test]
    public function parse_date_returns_null_for_numeric_outside_serial_range(): void
    {
        // Angka biasa (bukan serial Excel 40000-70000) harus dikembalikan null
        $this->assertNull($this->service->parseDate(2025)); // tahun saja
        $this->assertNull($this->service->parseDate(100));
    }

    // =========================================================================
    // parseDateWithDominantYear()
    // =========================================================================

    #[Test]
    public function parse_date_with_dominant_year_returns_null_for_unparseable_input(): void
    {
        $this->assertNull($this->service->parseDateWithDominantYear('bukan tanggal', 2024));
        // Bug regression: teks yang mengandung tahun di tengah harus tetap null
        $this->assertNull($this->service->parseDateWithDominantYear('PERIODE 2025-2026', 2025));
    }

    #[Test]
    public function parse_date_with_dominant_year_does_not_replace_explicit_current_year(): void
    {
        $currentYear = (int) date('Y');
        $dominant    = $currentYear - 1;

        // Format "DD Bulan YYYY" yang eksplisit menyebut tahun sekarang di akhir → TIDAK dikoreksi
        $raw    = "01 Januari {$currentYear}";
        $parsed = $this->service->parseDateWithDominantYear($raw, $dominant);

        $this->assertNotNull($parsed);
        $this->assertStringContainsString((string) $currentYear, $parsed);
    }

    #[Test]
    public function parse_date_with_dominant_year_does_not_replace_explicit_iso_current_year(): void
    {
        $currentYear = (int) date('Y');
        $dominant    = 2024;

        if ($dominant === $currentYear) {
            $this->markTestSkipped('Test tidak relevan saat tahun sekarang adalah 2024');
        }

        // ISO date dengan tahun eksplisit sekarang → TIDAK dikoreksi
        $rawWithCurrentYear = "{$currentYear}-06-15";
        $parsed = $this->service->parseDateWithDominantYear($rawWithCurrentYear, $dominant);

        $this->assertStringContainsString((string) $currentYear, $parsed);
    }

    #[Test]
    public function parse_date_with_dominant_year_corrects_year_from_title_text_regression(): void
    {
        $currentYear = (int) date('Y');
        $dominant    = 2025;

        if ($dominant === $currentYear) {
            $this->markTestSkipped('Test tidak relevan saat tahun sekarang adalah 2025');
        }

        // Bug regression: string judul "PERIODE 2025-2026" seharusnya null, bukan dikira tanggal
        $result = $this->service->parseDateWithDominantYear('FIRST MEETING PERIODE 2025-2026', $dominant);
        $this->assertNull($result, 'Teks judul dengan tahun di tengah seharusnya dikembalikan null, bukan dibuat tanggal');
    }

    // =========================================================================
    // getBulanIndonesia() (versi service)
    // =========================================================================

    #[Test]
    public function service_get_bulan_indonesia_returns_correct_months(): void
    {
        $this->assertEquals('Januari',  $this->service->getBulanIndonesia('2024-01-15'));
        $this->assertEquals('Maret',    $this->service->getBulanIndonesia('2024-03-01'));
        $this->assertEquals('Desember', $this->service->getBulanIndonesia('2024-12-31'));
    }

    // =========================================================================
    // detectHeaderRow() (protected)
    // =========================================================================

    #[Test]
    public function detect_header_row_finds_single_row_header(): void
    {
        $sample = [
            0 => ['No', 'Nama Organisasi', 'Bidang', 'Jenis Kegiatan', 'Sub Sektor', 'Komersial', 'Judul Kegiatan', 'Tanggal', 'Lantai', 'Lokasi', 'Jumlah Peserta'],
            1 => ['1', 'Komunitas A', 'Bisnis', 'Workshop', 'Digital', 'Non Komersial', 'Workshop AI', '2024-01-15', '3', 'Studio', '100'],
        ];

        [$headerRow, $dataStart, $parentRow] = $this->callPrivate('detectHeaderRow', $sample);

        $this->assertEquals(0, $headerRow);
        $this->assertEquals(1, $dataStart);
        $this->assertNull($parentRow);
    }

    #[Test]
    public function detect_header_row_returns_null_when_header_not_found(): void
    {
        $sample = [
            0 => ['foo', 'bar', 'baz'],
            1 => ['1', '2', '3'],
        ];

        [$headerRow, $dataStart, $parentRow] = $this->callPrivate('detectHeaderRow', $sample);

        $this->assertNull($headerRow);
        $this->assertNull($dataStart);
        $this->assertNull($parentRow);
    }

    #[Test]
    public function detect_header_row_handles_header_not_in_first_row(): void
    {
        $sample = [
            0 => ['Judul Laporan 2024', '', '', '', '', '', '', '', '', '', ''],
            1 => ['No', 'Nama Organisasi', 'Bidang', 'Jenis Kegiatan', 'Sub Sektor', 'Komersial', 'Judul Kegiatan', 'Tanggal', 'Lantai', 'Lokasi', 'Jumlah Peserta'],
            2 => ['1', 'Komunitas A', 'Bisnis', 'Workshop', 'Digital', 'Komersial', 'Workshop PHP', '2024-03-05', '3', 'Studio', '50'],
        ];

        [$headerRow, $dataStart, $parentRow] = $this->callPrivate('detectHeaderRow', $sample);

        $this->assertEquals(1, $headerRow);
        $this->assertEquals(2, $dataStart);
    }

    // =========================================================================
    // mergeHeaderRows() (protected)
    // =========================================================================

    #[Test]
    public function merge_header_rows_uses_sub_header_over_parent(): void
    {
        $parent    = ['Kategori Pelaku', '', 'Judul Kegiatan', 'Tanggal'];
        $subHeader = ['Bidang', 'Jenis Kegiatan', 'Judul Kegiatan', 'Tanggal'];

        $result = $this->callPrivate('mergeHeaderRows', $parent, $subHeader);

        $this->assertEquals('Bidang', $result[0]);
        $this->assertEquals('Jenis Kegiatan', $result[1]);
    }

    #[Test]
    public function merge_header_rows_discards_group_labels(): void
    {
        $parent    = ['Kategori Pelaku', '', 'Informasi Kegiatan'];
        $subHeader = ['Bidang', 'Jenis Kegiatan', 'Judul Kegiatan'];

        $result = $this->callPrivate('mergeHeaderRows', $parent, $subHeader);

        $this->assertNotContains('Kategori Pelaku', $result);
        $this->assertNotContains('Informasi Kegiatan', $result);
    }

    // =========================================================================
    // resolveColumns() (protected)
    // =========================================================================

    #[Test]
    public function resolve_columns_maps_2024_format_correctly(): void
    {
        $row = [
            'Nama Organisasi'          => 'Komunitas Kreatif',
            'Bidang'                   => 'Bisnis',
            'Jenis Kegiatan'           => 'Workshop',
            'Sub Sektor'               => 'Digital',
            'Komersial/ Non Komersial' => 'Non Komersial',
            'Judul Kegiatan'           => 'Workshop Design',
            'Tanggal'                  => '2024-06-01',
            'Lantai'                   => '3',
            'Lokasi'                   => 'Studio Foto',
            'Jumlah Peserta'           => '150',
            'Email'                    => 'contact@kreatif.com',
            'No. Telf'                 => '081234567890',
            'PIC'                      => 'Budi',
        ];

        $result = $this->callPrivate('resolveColumns', $row);

        $this->assertEquals('Komunitas Kreatif', $result['nama_organisasi']);
        $this->assertEquals('Bisnis', $result['bidang']);
        $this->assertEquals('Workshop', $result['jenis_kegiatan']);
        $this->assertEquals('Digital', $result['sub_sektor']);
        $this->assertEquals('Non Komersial', $result['komersial']);
        $this->assertEquals('Workshop Design', $result['judul_kegiatan']);
        $this->assertEquals('2024-06-01', $result['tanggal_raw']);
        $this->assertEquals('3', $result['lantai']);
        $this->assertEquals('Studio Foto', $result['lokasi']);
        $this->assertEquals('150', $result['jumlah_peserta']);
        $this->assertEquals('contact@kreatif.com', $result['email']);
        $this->assertEquals('081234567890', $result['no_telepon']);
        $this->assertEquals('Budi', $result['pic']);
    }

    #[Test]
    public function resolve_columns_maps_2023_format_with_kegiatan_alias(): void
    {
        $row = [
            'Nama Organisasi' => 'Komunitas A',
            'Kegiatan'        => 'Meeting',
            'Bidang'          => 'Komunitas',
            'Judul Kegiatan'  => 'Rapat Bulanan',
            'Tanggal'         => '2023-03-01',
            'Lantai'          => '2',
            'Lokasi'          => 'Ruang Meeting',
            'Jumlah Peserta'  => '30',
        ];

        $result = $this->callPrivate('resolveColumns', $row);

        $this->assertEquals('Meeting', $result['jenis_kegiatan']);
        $this->assertNull($result['email']);
        $this->assertNull($result['no_telepon']);
        $this->assertNull($result['pic']);
        $this->assertNull($result['program_sdgs']);
    }

    #[Test]
    public function resolve_columns_maps_2025_format_with_program_sdgs(): void
    {
        $row = [
            'Nama Organisasi / Komunitas' => 'Startup Indonesia',
            'Bidang'                      => 'Bisnis',
            'Jenis Kegiatan'              => 'Pelatihan',
            'Sub Sektor'                  => 'Teknologi',
            'Komersial/Non Komersial'     => 'Komersial',
            'Program SDGS'                => 'SDGS 4; SDGS 8',
            'Judul Kegiatan'              => 'Bootcamp AI 2025',
            'Tanggal Kegiatan'            => '2025-04-10',
            'Lantai'                      => '4',
            'Lokasi / Ruangan'            => 'Co-Working Space 1',
            'Jumlah Peserta'              => '200',
            'PIC'                         => 'Ani',
            'No. Telp'                    => '087654321098',
            'Email'                       => 'info@startup.id',
        ];

        $result = $this->callPrivate('resolveColumns', $row);

        $this->assertEquals('Startup Indonesia', $result['nama_organisasi']);
        $this->assertEquals('SDGS 4; SDGS 8', $result['program_sdgs']);
        $this->assertEquals('2025-04-10', $result['tanggal_raw']);
        $this->assertEquals('Co-Working Space 1', $result['lokasi']);
    }

    // =========================================================================
    // cleanBidang() (private)
    // =========================================================================

    #[Test]
    public function clean_bidang_normalizes_known_values(): void
    {
        $this->assertEquals('Komunitas',           $this->callPrivate('cleanBidang', 'komunitas'));
        $this->assertEquals('Bisnis',              $this->callPrivate('cleanBidang', 'bisnis'));
        $this->assertEquals('Akademisi',           $this->callPrivate('cleanBidang', 'akademisi'));
        $this->assertEquals('Pemerintah',          $this->callPrivate('cleanBidang', 'pemerintah'));
        $this->assertEquals('Institusi Finansial', $this->callPrivate('cleanBidang', 'institusi finansial'));
        $this->assertEquals('Media',               $this->callPrivate('cleanBidang', 'media'));
        $this->assertEquals('Perorangan',          $this->callPrivate('cleanBidang', 'perorangan'));
    }

    #[Test]
    public function clean_bidang_returns_komunitas_for_empty_or_null(): void
    {
        $this->assertEquals('Komunitas', $this->callPrivate('cleanBidang', ''));
        $this->assertEquals('Komunitas', $this->callPrivate('cleanBidang', null));
        $this->assertEquals('Komunitas', $this->callPrivate('cleanBidang', '0'));
    }

    #[Test]
    public function clean_bidang_applies_ucwords_for_unknown_values(): void
    {
        $result = $this->callPrivate('cleanBidang', 'lembaga swadaya');
        $this->assertEquals('Lembaga Swadaya', $result);
    }

    // =========================================================================
    // cleanKegiatan() (private)
    // =========================================================================

    #[Test]
    public function clean_kegiatan_normalizes_known_kegiatan(): void
    {
        $this->assertEquals('Meeting',    $this->callPrivate('cleanKegiatan', 'meeting'));
        $this->assertEquals('Workshop',   $this->callPrivate('cleanKegiatan', 'workshop'));
        $this->assertEquals('Pelatihan',  $this->callPrivate('cleanKegiatan', 'pelatihan'));
        $this->assertEquals('Pameran',    $this->callPrivate('cleanKegiatan', 'pameran'));
        $this->assertEquals('Kompetisi',  $this->callPrivate('cleanKegiatan', 'kompetisi'));
        $this->assertEquals('Pertunjukan',$this->callPrivate('cleanKegiatan', 'pertunjukan'));
        $this->assertEquals('Pertunjukan',$this->callPrivate('cleanKegiatan', 'pertunjukkan')); // typo
    }

    #[Test]
    public function clean_kegiatan_returns_lainnya_for_unknown_value(): void
    {
        $this->assertEquals('Lainnya', $this->callPrivate('cleanKegiatan', 'gathering'));
        $this->assertEquals('Lainnya', $this->callPrivate('cleanKegiatan', null));
    }

    // =========================================================================
    // cleanKomersial() (private)
    // =========================================================================

    #[Test]
    public function clean_komersial_detects_non_komersial(): void
    {
        $this->assertEquals('Non Komersial', $this->callPrivate('cleanKomersial', 'Non Komersial'));
        $this->assertEquals('Non Komersial', $this->callPrivate('cleanKomersial', 'non'));
        $this->assertEquals('Non Komersial', $this->callPrivate('cleanKomersial', ''));
    }

    #[Test]
    public function clean_komersial_detects_komersial(): void
    {
        $this->assertEquals('Komersial', $this->callPrivate('cleanKomersial', 'Komersial'));
        $this->assertEquals('Komersial', $this->callPrivate('cleanKomersial', 'KOMERSIAL'));
        $this->assertEquals('Komersial', $this->callPrivate('cleanKomersial', 'komesial')); // typo
    }

    // =========================================================================
    // cleanSdgs() (private)
    // =========================================================================

    #[Test]
    public function clean_sdgs_returns_null_for_empty(): void
    {
        $this->assertNull($this->callPrivate('cleanSdgs', null));
        $this->assertNull($this->callPrivate('cleanSdgs', ''));
        $this->assertNull($this->callPrivate('cleanSdgs', '   '));
    }

    #[Test]
    public function clean_sdgs_normalizes_single_sdgs_number(): void
    {
        $this->assertEquals('SDGS 4',  $this->callPrivate('cleanSdgs', 'SDGS 4'));
        $this->assertEquals('SDGS 4',  $this->callPrivate('cleanSdgs', 'sdgs4'));
        $this->assertEquals('SDGS 4',  $this->callPrivate('cleanSdgs', 'SDGs 4'));
        $this->assertEquals('SDGS 8',  $this->callPrivate('cleanSdgs', '8'));
        $this->assertEquals('SDGS 10', $this->callPrivate('cleanSdgs', '10'));
    }

    #[Test]
    public function clean_sdgs_normalizes_multi_sdgs_separated_by_semicolon(): void
    {
        $result = $this->callPrivate('cleanSdgs', 'SDGS 4; SDGS 5; SDGS 18');
        $this->assertEquals('SDGS 4; SDGS 5; SDGS 18', $result);
    }

    #[Test]
    public function clean_sdgs_normalizes_multi_sdgs_separated_by_comma(): void
    {
        $result = $this->callPrivate('cleanSdgs', '4, 5, 10');
        $this->assertStringContainsString('SDGS 4', $result);
        $this->assertStringContainsString('SDGS 5', $result);
        $this->assertStringContainsString('SDGS 10', $result);
    }

    #[Test]
    public function clean_sdgs_result_does_not_exceed_255_characters(): void
    {
        $longInput = implode('; ', array_fill(0, 30, 'SDGS 4'));
        $result    = $this->callPrivate('cleanSdgs', $longInput);

        $this->assertNotNull($result);
        $this->assertLessThanOrEqual(255, mb_strlen($result));
    }

    // =========================================================================
    // cleanLokasi() (private)
    // =========================================================================

    #[Test]
    public function clean_lokasi_maps_known_locations(): void
    {
        $this->assertEquals('Studio Musik',       $this->callPrivate('cleanLokasi', 'Studio Musik'));
        $this->assertEquals('Studio Foto',        $this->callPrivate('cleanLokasi', 'Studio Foto'));
        $this->assertEquals('Co-Working Space 1', $this->callPrivate('cleanLokasi', 'Coworking Space 1'));
        $this->assertEquals('Co-Working Space 1', $this->callPrivate('cleanLokasi', 'Co-Working Space 1'));
        $this->assertEquals('Co-Working Space 2', $this->callPrivate('cleanLokasi', 'Co-Working Space 2'));
        $this->assertEquals('Food Lab',           $this->callPrivate('cleanLokasi', 'Food Lab'));
        $this->assertEquals('Food Lab',           $this->callPrivate('cleanLokasi', 'Foodlab'));
        $this->assertEquals('Amphitheater 1',     $this->callPrivate('cleanLokasi', 'Amphitheater 1'));
        $this->assertEquals('Ruang Meeting',      $this->callPrivate('cleanLokasi', 'Ruang Meeting'));
        $this->assertEquals('KIDZONE',            $this->callPrivate('cleanLokasi', 'Kidzone'));
    }

    #[Test]
    public function clean_lokasi_returns_ruang_meeting_for_unknown(): void
    {
        $this->assertEquals('Ruang Meeting', $this->callPrivate('cleanLokasi', 'Ruang Tidak Dikenal'));
        $this->assertEquals('Ruang Meeting', $this->callPrivate('cleanLokasi', ''));
    }

    #[Test]
    public function clean_lokasi_maps_open_public_space(): void
    {
        $this->assertEquals('Open Public Space Barat', $this->callPrivate('cleanLokasi', 'Open Public Space Barat'));
        $this->assertEquals('Open Public Space Utara', $this->callPrivate('cleanLokasi', 'Open Public Space Utara'));
    }

    // =========================================================================
    // cleanOrganisasi() (private)
    // =========================================================================

    #[Test]
    public function clean_organisasi_returns_unknown_for_empty(): void
    {
        $this->assertEquals('Unknown', $this->callPrivate('cleanOrganisasi', null));
        $this->assertEquals('Unknown', $this->callPrivate('cleanOrganisasi', ''));
    }

    #[Test]
    public function clean_organisasi_removes_common_prefixes(): void
    {
        $result = $this->callPrivate('cleanOrganisasi', 'PT Maju Bersama');
        $this->assertStringNotContainsString('Pt ', $result);
        $this->assertStringNotContainsString('PT ', $result);
    }

    #[Test]
    public function clean_organisasi_applies_title_case(): void
    {
        // cleanOrganisasi() menghapus prefix "komunitas" sebelum title case
        $result = $this->callPrivate('cleanOrganisasi', 'komunitas belajar bersama');
        // Prefix "komunitas" dihapus, sisanya menjadi title case
        $this->assertEquals('Belajar Bersama', $result);
    }

    #[Test]
    public function clean_organisasi_applies_title_case_for_non_prefix(): void
    {
        // String tanpa prefix standar → langsung title case
        $result = $this->callPrivate('cleanOrganisasi', 'indonesia maju bersama');
        $this->assertEquals('Indonesia Maju Bersama', $result);
    }

    // =========================================================================
    // cleanNullableString() (private)
    // =========================================================================

    #[Test]
    public function clean_nullable_string_returns_null_for_empty(): void
    {
        $this->assertNull($this->callPrivate('cleanNullableString', null, 255));
        $this->assertNull($this->callPrivate('cleanNullableString', '', 255));
        $this->assertNull($this->callPrivate('cleanNullableString', '   ', 255));
    }

    #[Test]
    public function clean_nullable_string_trims_and_truncates(): void
    {
        $this->assertEquals('test@mail.com', $this->callPrivate('cleanNullableString', '  test@mail.com  ', 255));

        $longStr = str_repeat('A', 300);
        $result  = $this->callPrivate('cleanNullableString', $longStr, 255);
        $this->assertEquals(255, mb_strlen($result));
    }

    // =========================================================================
    // splitMulti() (private)
    // =========================================================================

    #[Test]
    public function split_multi_splits_by_comma(): void
    {
        $result = $this->callPrivate('splitMulti', 'Studio Foto, Studio Musik');
        $this->assertCount(2, $result);
        $this->assertEquals('Studio Foto', $result[0]);
        $this->assertEquals('Studio Musik', $result[1]);
    }

    #[Test]
    public function split_multi_splits_by_ampersand(): void
    {
        $result = $this->callPrivate('splitMulti', 'Ruang A & Ruang B');
        $this->assertCount(2, $result);
    }

    #[Test]
    public function split_multi_splits_by_word_dan(): void
    {
        $result = $this->callPrivate('splitMulti', 'Lantai 2 dan Lantai 3');
        $this->assertCount(2, $result);
    }

    #[Test]
    public function split_multi_returns_single_element_for_no_separator(): void
    {
        $result = $this->callPrivate('splitMulti', 'Studio Musik');
        $this->assertCount(1, $result);
        $this->assertEquals('Studio Musik', $result[0]);
    }

    // =========================================================================
    // detectDuplicates()
    // =========================================================================

    #[Test]
    public function detect_duplicates_marks_full_duplicates_in_batch(): void
    {
        $data = [
            ['nama_organisasi' => 'ORG A', 'judul_kegiatan' => 'Event 1', 'tanggal' => '2024-01-01', 'lokasi' => 'Studio', 'lantai' => '2'],
            ['nama_organisasi' => 'ORG A', 'judul_kegiatan' => 'Event 1', 'tanggal' => '2024-01-01', 'lokasi' => 'Studio', 'lantai' => '2'],
        ];

        $result = $this->service->detectDuplicates($data);

        $duplikats = array_filter($result, fn($r) => $r['status_data'] === 'Duplikat');
        $this->assertNotEmpty($duplikats);
    }

    #[Test]
    public function detect_duplicates_marks_short_duplicates_as_review(): void
    {
        $data = [
            ['nama_organisasi' => 'ORG B', 'judul_kegiatan' => 'Event Multi Room', 'tanggal' => '2024-02-01', 'lokasi' => 'Studio A', 'lantai' => '2'],
            ['nama_organisasi' => 'ORG B', 'judul_kegiatan' => 'Event Multi Room', 'tanggal' => '2024-02-01', 'lokasi' => 'Studio B', 'lantai' => '3'],
        ];

        $result = $this->service->detectDuplicates($data);

        $reviews = array_filter($result, fn($r) => $r['status_data'] === 'Review');
        $this->assertNotEmpty($reviews);
    }

    #[Test]
    public function detect_duplicates_marks_unique_items_as_valid(): void
    {
        $data = [
            ['nama_organisasi' => 'ORG X', 'judul_kegiatan' => 'Event Unik', 'tanggal' => '2024-05-10', 'lokasi' => 'Ruang Meeting', 'lantai' => '2'],
        ];

        $result = $this->service->detectDuplicates($data);

        $this->assertEquals('Valid', $result[0]['status_data']);
        $this->assertNull($result[0]['catatan_review']);
    }

    #[Test]
    public function detect_duplicates_attaches_key_full_and_key_short(): void
    {
        $data = [
            ['nama_organisasi' => 'ORG Y', 'judul_kegiatan' => 'Workshop Y', 'tanggal' => '2024-07-01', 'lokasi' => 'Studio', 'lantai' => '3'],
        ];

        $result = $this->service->detectDuplicates($data);

        $this->assertArrayHasKey('key_full', $result[0]);
        $this->assertArrayHasKey('key_short', $result[0]);
        $this->assertStringContainsString('ORG Y', $result[0]['key_full']);
        $this->assertStringContainsString('ORG Y', $result[0]['key_short']);
    }

    // =========================================================================
    // standardize()
    // =========================================================================

    #[Test]
    public function standardize_converts_raw_row_to_db_schema(): void
    {
        $rawData = [[
            'Nama Organisasi'          => 'Komunitas Digital',
            'Bidang'                   => 'Komunitas',
            'Jenis Kegiatan'           => 'Workshop',
            'Sub Sektor'               => 'Digital',
            'Komersial/ Non Komersial' => 'Non Komersial',
            'Judul Kegiatan'           => 'Bootcamp PHP',
            'Tanggal'                  => '2024-06-15',
            'Lantai'                   => '3',
            'Lokasi'                   => 'Studio Musik',
            'Jumlah Peserta'           => '100',
            '_sheet'                   => 'Juni',
        ]];

        $result = $this->service->standardize($rawData);

        $this->assertNotEmpty($result);
        $row = $result[0];

        $this->assertArrayHasKey('nama_organisasi', $row);
        $this->assertArrayHasKey('tanggal', $row);
        $this->assertArrayHasKey('bulan', $row);
        $this->assertArrayHasKey('is_primary_row', $row);

        $this->assertEquals('Studio Musik', $row['lokasi']);
        $this->assertEquals('3', $row['lantai']);
        $this->assertEquals(100, $row['jumlah_peserta']);
        $this->assertEquals('Juni', $row['bulan']);
    }

    #[Test]
    public function standardize_splits_multi_lokasi_into_multiple_rows(): void
    {
        $rawData = [[
            'Nama Organisasi' => 'Org Multi Lokasi',
            'Bidang'          => 'Bisnis',
            'Jenis Kegiatan'  => 'Pameran',
            'Sub Sektor'      => null,
            'Komersial'       => 'Komersial',
            'Judul Kegiatan'  => 'Pameran Multi Room',
            'Tanggal'         => '2024-08-10',
            'Lantai'          => '2',
            'Lokasi'          => 'Studio Foto, Studio Musik',
            'Jumlah Peserta'  => '200',
            '_sheet'          => 'Agustus',
        ]];

        $result = $this->service->standardize($rawData);

        $this->assertCount(2, $result);
        $this->assertTrue($result[0]['is_primary_row']);
        $this->assertFalse($result[1]['is_primary_row']);
    }

    #[Test]
    public function standardize_sets_null_for_missing_optional_columns_like_2023(): void
    {
        $rawData = [[
            'Nama Organisasi' => 'Komunitas Lama',
            'Bidang'          => 'Komunitas',
            'Kegiatan'        => 'Meeting',
            'Sub Sektor'      => 'Musik',
            'Komersial'       => 'Non Komersial',
            'Judul Kegiatan'  => 'Rapat 2023',
            'Tanggal'         => '2023-04-01',
            'Lantai'          => '2',
            'Lokasi'          => 'Ruang Meeting',
            'Jumlah Peserta'  => '15',
            '_sheet'          => 'April',
        ]];

        $result = $this->service->standardize($rawData);

        $this->assertNotEmpty($result);
        $row = $result[0];

        $this->assertNull($row['email']);
        $this->assertNull($row['no_telepon']);
        $this->assertNull($row['pic']);
        $this->assertNull($row['program_sdgs']);
    }

    #[Test]
    public function standardize_handles_program_sdgs_for_2025_data(): void
    {
        $rawData = [[
            'Nama Organisasi / Komunitas' => 'Startup Keren',
            'Bidang'                      => 'Bisnis',
            'Jenis Kegiatan'              => 'Pelatihan',
            'Sub Sektor'                  => 'Teknologi',
            'Komersial/Non Komersial'     => 'Komersial',
            'Program SDGS'                => 'SDGS 4; SDGS 8',
            'Judul Kegiatan'              => 'AI Training 2025',
            'Tanggal Kegiatan'            => '2025-03-15',
            'Lantai'                      => '4',
            'Lokasi / Ruangan'            => 'Co-Working Space 1',
            'Jumlah Peserta'              => '50',
            'PIC'                         => 'Ani Rahayu',
            'No. Telp'                    => '082111222333',
            'Email'                       => 'ai@startup.id',
            '_sheet'                      => 'Maret',
        ]];

        $result = $this->service->standardize($rawData);

        $this->assertNotEmpty($result);
        $row = $result[0];

        $this->assertStringContainsString('SDGS', $row['program_sdgs']);
        $this->assertNotNull($row['email']);
        $this->assertNotNull($row['pic']);
    }
}
