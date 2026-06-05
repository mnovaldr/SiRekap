<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Feature Test untuk ActivityLog (Database Layer)
 */
class ActivityLogFeatureTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // ActivityLog::log() — penyimpanan ke database
    // =========================================================================

    #[Test]
    public function log_method_persists_record_to_database(): void
    {
        $this->assertDatabaseCount('activity_logs', 0);

        ActivityLog::log(ActivityLog::ACTION_IMPORT, null, 'Import 100 data');

        $this->assertDatabaseCount('activity_logs', 1);
        $this->assertDatabaseHas('activity_logs', [
            'action'      => 'import',
            'description' => 'Import 100 data',
        ]);
    }

    #[Test]
    public function log_method_saves_correct_action_value(): void
    {
        ActivityLog::log(ActivityLog::ACTION_DELETE, null, 'Hapus event');
        $this->assertDatabaseHas('activity_logs', ['action' => 'delete']);
    }

    #[Test]
    public function log_method_saves_model_type_and_id_when_model_provided(): void
    {
        $event = Event::factory()->create();

        ActivityLog::log(ActivityLog::ACTION_UPDATE, $event, 'Update event');

        $this->assertDatabaseHas('activity_logs', [
            'action'     => 'update',
            'model_type' => 'Event',
            'model_id'   => (string) $event->id,
        ]);
    }

    #[Test]
    public function log_method_saves_null_model_type_when_no_model(): void
    {
        ActivityLog::log(ActivityLog::ACTION_EXPORT, null, 'Export CSV');

        $log = ActivityLog::first();
        $this->assertNull($log->model_type);
        $this->assertNull($log->model_id);
    }

    #[Test]
    public function log_method_saves_properties_as_json(): void
    {
        $properties = ['file' => 'data_2024.xlsx', 'count' => 150, 'errors' => 0];

        ActivityLog::log(ActivityLog::ACTION_IMPORT, null, 'Import selesai', $properties);

        $log = ActivityLog::first();
        $this->assertIsArray($log->properties);
        $this->assertEquals('data_2024.xlsx', $log->properties['file']);
        $this->assertEquals(150, $log->properties['count']);
    }

    #[Test]
    public function log_method_saves_null_properties_when_not_provided(): void
    {
        ActivityLog::log(ActivityLog::ACTION_LOGIN);

        $log = ActivityLog::first();
        $this->assertNull($log->properties);
    }

    // =========================================================================
    // User ID dari authenticated user
    // =========================================================================

    #[Test]
    public function log_method_captures_authenticated_user_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        ActivityLog::log(ActivityLog::ACTION_CREATE, null, 'Tambah data');

        $this->assertDatabaseHas('activity_logs', [
            'action'  => 'create',
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function log_method_saves_null_user_id_when_unauthenticated(): void
    {
        ActivityLog::log(ActivityLog::ACTION_IMPORT, null, 'Import tanpa user');

        $log = ActivityLog::first();
        $this->assertNull($log->user_id);
    }

    // =========================================================================
    // Semua action constants tersimpan di DB
    // =========================================================================

    #[Test]
    public function all_action_constants_can_be_saved_to_database(): void
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

        foreach ($actions as $action) {
            ActivityLog::log($action, null, "Test action: {$action}");
        }

        $this->assertDatabaseCount('activity_logs', count($actions));

        foreach ($actions as $action) {
            $this->assertDatabaseHas('activity_logs', ['action' => $action]);
        }
    }

    // =========================================================================
    // Relasi dengan User
    // =========================================================================

    #[Test]
    public function activity_log_relation_to_user_works_via_database(): void
    {
        $user = User::factory()->create(['name' => 'Admin Test']);
        $log  = ActivityLog::factory()->create(['user_id' => $user->id]);

        $loadedLog = ActivityLog::with('user')->find($log->id);

        $this->assertNotNull($loadedLog->user);
        $this->assertEquals('Admin Test', $loadedLog->user->name);
        $this->assertEquals($user->id, $loadedLog->user->id);
    }

    #[Test]
    public function activity_log_with_null_user_id_has_null_user_relation(): void
    {
        $log = ActivityLog::factory()->create(['user_id' => null]);
        $this->assertNull($log->user);
    }

    #[Test]
    public function deleting_user_sets_activity_log_user_id_to_null(): void
    {
        $user = User::factory()->create();
        $log  = ActivityLog::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $log->refresh();
        $this->assertNull($log->user_id);
    }

    // =========================================================================
    // Factory States
    // =========================================================================

    #[Test]
    public function activity_log_factory_creates_valid_record(): void
    {
        $log = ActivityLog::factory()->create();

        $this->assertDatabaseCount('activity_logs', 1);
        $this->assertNotNull($log->action);
        $this->assertNotNull($log->id);
    }

    #[Test]
    public function activity_log_factory_import_state_creates_import_log(): void
    {
        $log = ActivityLog::factory()->import()->create();

        $this->assertEquals('import', $log->action);
        $this->assertNotNull($log->properties);
        $this->assertArrayHasKey('count', $log->properties);
    }

    #[Test]
    public function activity_log_factory_with_user_state_attaches_user(): void
    {
        $log = ActivityLog::factory()->withUser()->create();

        $this->assertNotNull($log->user_id);
        $this->assertNotNull($log->user);
    }

    // =========================================================================
    // Query ActivityLog
    // =========================================================================

    #[Test]
    public function activity_logs_can_be_filtered_by_action(): void
    {
        ActivityLog::factory()->create(['action' => 'import']);
        ActivityLog::factory()->create(['action' => 'export']);
        ActivityLog::factory()->create(['action' => 'import']);

        $imports = ActivityLog::where('action', 'import')->get();

        $this->assertCount(2, $imports);
    }

    #[Test]
    public function multiple_logs_can_be_created_for_same_model(): void
    {
        $event   = Event::factory()->create();
        $modelId = (string) $event->id;

        ActivityLog::log(ActivityLog::ACTION_CREATE, $event, 'Create event test');
        ActivityLog::log(ActivityLog::ACTION_UPDATE, $event, 'Update event test 1');
        ActivityLog::log(ActivityLog::ACTION_UPDATE, $event, 'Update event test 2');

        $eventLogs = ActivityLog::where('model_type', 'Event')
            ->where('model_id', $modelId)
            ->whereIn('description', ['Create event test', 'Update event test 1', 'Update event test 2'])
            ->get();

        $this->assertCount(3, $eventLogs);
    }


    #[Test]
    public function activity_log_logactivity_alias_persists_to_database(): void
    {
        ActivityLog::logActivity(ActivityLog::ACTION_VALIDATE, null, 'Validasi manual');

        $this->assertDatabaseHas('activity_logs', [
            'action'      => 'validate',
            'description' => 'Validasi manual',
        ]);
    }
}
