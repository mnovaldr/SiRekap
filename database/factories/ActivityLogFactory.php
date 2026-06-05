<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        $actions = [
            ActivityLog::ACTION_CREATE,
            ActivityLog::ACTION_UPDATE,
            ActivityLog::ACTION_DELETE,
            ActivityLog::ACTION_IMPORT,
            ActivityLog::ACTION_EXPORT,
            ActivityLog::ACTION_VALIDATE,
            ActivityLog::ACTION_LOGIN,
            ActivityLog::ACTION_LOGOUT,
            ActivityLog::ACTION_DB_CLEAN,
        ];

        return [
            'action'      => $this->faker->randomElement($actions),
            'model_type'  => $this->faker->optional()->randomElement(['Event', 'User', 'Organizer']),
            'model_id'    => $this->faker->optional()->uuid(),
            'user_id'     => null,
            'ip_address'  => $this->faker->ipv4(),
            'user_agent'  => $this->faker->userAgent(),
            'description' => $this->faker->optional()->sentence(),
            'properties'  => $this->faker->optional()->randomElement([
                ['before' => ['status' => 'Review'], 'after' => ['status' => 'Valid']],
                ['count' => 150, 'file' => 'data_2024.xlsx'],
                null,
            ]),
        ];
    }

    /** State: log dengan action 'import' */
    public function import(): static
    {
        return $this->state(fn (array $attributes) => [
            'action'      => ActivityLog::ACTION_IMPORT,
            'description' => 'Import Excel berhasil',
            'properties'  => ['count' => 100, 'file' => 'data_test.xlsx'],
        ]);
    }

    /** State: log dengan action 'create' */
    public function create_(): static
    {
        return $this->state(fn (array $attributes) => [
            'action'      => ActivityLog::ACTION_CREATE,
            'model_type'  => 'Event',
            'description' => 'Tambah event baru',
        ]);
    }

    /** State: log dengan user yang terotentikasi */
    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }
}
