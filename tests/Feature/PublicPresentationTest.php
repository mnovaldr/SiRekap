<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\VisitToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPresentationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test access is denied without token or with invalid token.
     */
    public function test_access_denied_without_valid_token(): void
    {
        // 1. Tanpa token ke halaman presentasi
        $response = $this->get('/share/kunjungan');
        $response->assertStatus(403);

        // 2. Tanpa token ke API stats
        $response = $this->getJson('/api/public/stats');
        $response->assertStatus(403);

        // 3. Dengan token yang tidak terdaftar
        $response = $this->getJson('/api/public/stats?token=token-palsu-123');
        $response->assertStatus(403);
    }

    /**
     * Test access granted with valid token and response contains only percentage.
     */
    public function test_access_granted_with_valid_token_and_percentage_only(): void
    {
        // Buat token kunjungan valid
        $visitToken = VisitToken::create([
            'label' => 'Kunjungan Uji Coba',
            'token' => 'token-rahasia-uji-coba',
            'expires_at' => now()->addHour(),
            'is_active' => true,
        ]);

        // Buat event dummy
        Event::create([
            'nama_organisasi' => 'Komunitas Seni Malang',
            'bidang' => 'Komunitas',
            'jenis_kegiatan' => 'Pameran',
            'sub_sektor' => 'Seni Rupa',
            'judul_kegiatan' => 'Pameran Lukisan Kreatif',
            'tanggal' => now()->format('Y-m-d'),
            'lantai' => '3',
            'lokasi' => 'Studio Foto',
            'program_sdgs' => 'SDGS 8',
            'jumlah_peserta' => 10,
            'is_primary_row' => true,
            'status_data' => 'Valid',
        ]);

        // Akses halaman presentasi
        $response = $this->get("/share/kunjungan?token={$visitToken->token}");
        $response->assertStatus(200);
        $response->assertSee('Kunjungan Uji Coba');

        // Akses API stats
        $apiResponse = $this->getJson("/api/public/stats?token={$visitToken->token}");
        $apiResponse->assertStatus(200);

        // Verifikasi struktur JSON (hanya boleh ada persentase)
        $json = $apiResponse->json();
        $this->assertEquals('Kunjungan Uji Coba', $json['label']);
        $this->assertArrayHasKey('sdgs', $json);
        $this->assertArrayHasKey('ekraf', $json);
        $this->assertArrayHasKey('lokasi', $json);

        // Pastikan tidak bocor angka riil (seperti count total/jumlah event mentah)
        foreach ($json['sdgs'] as $sdg) {
            $this->assertArrayHasKey('program', $sdg);
            $this->assertArrayHasKey('percentage', $sdg);
            $this->assertArrayNotHasKey('total', $sdg); // Tidak boleh ada total angka riil
        }

        foreach ($json['ekraf'] as $ekraf) {
            $this->assertArrayHasKey('sub_sektor', $ekraf);
            $this->assertArrayHasKey('percentage', $ekraf);
            $this->assertArrayNotHasKey('total', $ekraf); // Tidak boleh ada total angka riil
        }
    }
}
