<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $tanggal = $this->faker->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d');

        $bulanMap = [
            1 => 'Januari', 2 => 'Februari',  3 => 'Maret',
            4 => 'April',   5 => 'Mei',        6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',    9 => 'September',
            10 => 'Oktober',11 => 'November',  12 => 'Desember',
        ];
        $bulan = $bulanMap[(int) date('n', strtotime($tanggal))];

        $org    = strtoupper($this->faker->company());
        $judul  = strtoupper($this->faker->sentence(4));
        $lokasi = $this->faker->randomElement([
            'Studio Musik', 'Studio Foto', 'Co-Working Space 1',
            'Ruang Meeting', 'Amphitheater 1', 'Food Lab',
        ]);
        $lantai = $this->faker->randomElement(['2', '3', '4', '5']);

        $keyFull  = "{$org}||{$judul}||{$tanggal}||" . strtoupper($lokasi) . "||{$lantai}";
        $keyShort = "{$org}||{$judul}||{$tanggal}";

        return [
            'nama_organisasi' => $this->faker->company(),
            'bidang'          => $this->faker->randomElement([
                'Komunitas', 'Bisnis', 'Akademisi', 'Pemerintah', 'Media',
            ]),
            'jenis_kegiatan' => $this->faker->randomElement([
                'Meeting', 'Workshop', 'Pelatihan', 'Pameran', 'Kompetisi', 'Sosialisasi',
            ]),
            'sub_sektor'     => $this->faker->optional()->word(),
            'komersial'      => $this->faker->randomElement(['Komersial', 'Non Komersial']),
            'program_sdgs'   => $this->faker->optional()->randomElement([
                'SDGS 4', 'SDGS 8', 'SDGS 4; SDGS 5', null,
            ]),
            'judul_kegiatan' => $this->faker->sentence(4),
            'tanggal'        => $tanggal,
            'bulan'          => $bulan,
            'lantai'         => $lantai,
            'lokasi'         => $lokasi,
            'jumlah_peserta' => $this->faker->numberBetween(5, 500),
            'email'          => $this->faker->optional()->safeEmail(),
            'no_telepon'     => $this->faker->optional()->phoneNumber(),
            'pic'            => $this->faker->optional()->name(),
            'status_data'    => 'Valid',
            'catatan_review' => null,
            'key_full'       => $keyFull,
            'key_short'      => $keyShort,
            'is_primary_row' => true,
        ];
    }

    /** State: event dengan status Duplikat */
    public function duplikat(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_data'    => 'Duplikat',
            'catatan_review' => 'Duplikat FULL',
            'is_primary_row' => false,
        ]);
    }

    /** State: event dengan status Review (multi-ruangan) */
    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_data'    => 'Review',
            'catatan_review' => 'Duplikat SHORT (Multi Ruangan)',
        ]);
    }

    /** State: event dengan is_primary_row = true */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary_row' => true,
        ]);
    }

    /** State: event dengan is_primary_row = false */
    public function nonPrimary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary_row' => false,
        ]);
    }
}
