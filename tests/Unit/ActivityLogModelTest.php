<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Unit Test untuk Model ActivityLog
 *
 * Mencakup:
 * - Accessor: getActionLabelAttribute(), getActionColorAttribute(), getActionIconAttribute()
 * - Static helper: log(), logActivity()
 * - Relasi: user()
 */
class ActivityLogModelTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Action Constants
    // =========================================================================

    #[Test]
    public function action_constants_have_expected_values(): void
    {
        $this->assertEquals('create',   ActivityLog::ACTION_CREATE);
        $this->assertEquals('update',   ActivityLog::ACTION_UPDATE);
        $this->assertEquals('delete',   ActivityLog::ACTION_DELETE);
        $this->assertEquals('import',   ActivityLog::ACTION_IMPORT);
        $this->assertEquals('export',   ActivityLog::ACTION_EXPORT);
        $this->assertEquals('validate', ActivityLog::ACTION_VALIDATE);
        $this->assertEquals('login',    ActivityLog::ACTION_LOGIN);
        $this->assertEquals('logout',   ActivityLog::ACTION_LOGOUT);
        $this->assertEquals('db_clean', ActivityLog::ACTION_DB_CLEAN);
    }

    // =========================================================================
    // Accessor: getActionLabelAttribute()
    // =========================================================================

    #[Test]
    public function action_label_returns_correct_label_for_each_action(): void
    {
        $cases = [
            ActivityLog::ACTION_CREATE   => 'Tambah Data',
            ActivityLog::ACTION_UPDATE   => 'Edit Data',
            ActivityLog::ACTION_DELETE   => 'Hapus Data (Soft Delete)',
            ActivityLog::ACTION_IMPORT   => 'Import Excel',
            ActivityLog::ACTION_EXPORT   => 'Export Data',
            ActivityLog::ACTION_VALIDATE => 'Validasi',
            ActivityLog::ACTION_LOGIN    => 'Login',
            ActivityLog::ACTION_LOGOUT   => 'Logout',
            ActivityLog::ACTION_DB_CLEAN => 'Bersihkan DB',
        ];

        foreach ($cases as $action => $expectedLabel) {
            $log = new ActivityLog(['action' => $action]);
            $this->assertEquals(
                $expectedLabel,
                $log->action_label,
                "Label salah untuk action: {$action}"
            );
        }
    }

    #[Test]
    public function action_label_returns_ucfirst_for_unknown_action(): void
    {
        $log = new ActivityLog(['action' => 'custom_action']);
        $this->assertEquals('Custom_action', $log->action_label);
    }

    // =========================================================================
    // Accessor: getActionColorAttribute()
    // =========================================================================

    #[Test]
    public function action_color_returns_correct_color_for_each_action(): void
    {
        $cases = [
            ActivityLog::ACTION_CREATE   => 'success',
            ActivityLog::ACTION_UPDATE   => 'info',
            ActivityLog::ACTION_DELETE   => 'warning',
            ActivityLog::ACTION_IMPORT   => 'primary',
            ActivityLog::ACTION_EXPORT   => 'gray',
            ActivityLog::ACTION_VALIDATE => 'success',
            ActivityLog::ACTION_LOGIN    => 'success',
            ActivityLog::ACTION_LOGOUT   => 'gray',
            ActivityLog::ACTION_DB_CLEAN => 'warning',
        ];

        foreach ($cases as $action => $expectedColor) {
            $log = new ActivityLog(['action' => $action]);
            $this->assertEquals(
                $expectedColor,
                $log->action_color,
                "Color salah untuk action: {$action}"
            );
        }
    }

    #[Test]
    public function action_color_returns_gray_for_unknown_action(): void
    {
        $log = new ActivityLog(['action' => 'unknown']);
        $this->assertEquals('gray', $log->action_color);
    }

    // =========================================================================
    // Accessor: getActionIconAttribute()
    // =========================================================================

    #[Test]
    public function action_icon_returns_correct_icon_for_each_action(): void
    {
        $cases = [
            ActivityLog::ACTION_CREATE   => 'heroicon-o-plus-circle',
            ActivityLog::ACTION_UPDATE   => 'heroicon-o-pencil-square',
            ActivityLog::ACTION_DELETE   => 'heroicon-o-trash',
            ActivityLog::ACTION_IMPORT   => 'heroicon-o-arrow-up-tray',
            ActivityLog::ACTION_EXPORT   => 'heroicon-o-arrow-down-tray',
            ActivityLog::ACTION_VALIDATE => 'heroicon-o-check-circle',
            ActivityLog::ACTION_LOGIN    => 'heroicon-o-arrow-right-on-rectangle',
            ActivityLog::ACTION_LOGOUT   => 'heroicon-o-arrow-left-on-rectangle',
            ActivityLog::ACTION_DB_CLEAN => 'heroicon-o-circle-stack',
        ];

        foreach ($cases as $action => $expectedIcon) {
            $log = new ActivityLog(['action' => $action]);
            $this->assertEquals(
                $expectedIcon,
                $log->action_icon,
                "Icon salah untuk action: {$action}"
            );
        }
    }

    #[Test]
    public function action_icon_returns_information_circle_for_unknown_action(): void
    {
        $log = new ActivityLog(['action' => 'something_else']);
        $this->assertEquals('heroicon-o-information-circle', $log->action_icon);
    }

    // =========================================================================
    // Static helper: log()
    // =========================================================================

    #[Test]
    public function log_method_creates_record_in_database(): void
    {
        $this->assertDatabaseCount('activity_logs', 0);

        ActivityLog::log(ActivityLog::ACTION_IMPORT, null, 'Import berhasil', ['count' => 50]);

        $this->assertDatabaseCount('activity_logs', 1);
        $this->assertDatabaseHas('activity_logs', [
            'action'      => 'import',
            'description' => 'Import berhasil',
        ]);
    }

    #[Test]
    public function log_method_stores_model_type_and_model_id(): void
    {
        $user = User::factory()->create();

        ActivityLog::log(ActivityLog::ACTION_UPDATE, $user, 'Update user');

        $this->assertDatabaseHas('activity_logs', [
            'action'     => 'update',
            'model_type' => 'User',
            'model_id'   => (string) $user->id,
        ]);
    }

    #[Test]
    public function log_method_returns_activity_log_instance(): void
    {
        $result = ActivityLog::log(ActivityLog::ACTION_EXPORT, null, 'Export CSV');

        $this->assertInstanceOf(ActivityLog::class, $result);
        $this->assertEquals('export', $result->action);
    }

    #[Test]
    public function log_activity_is_alias_for_log_method(): void
    {
        ActivityLog::logActivity(ActivityLog::ACTION_LOGIN, null, 'User login');

        $this->assertDatabaseHas('activity_logs', [
            'action'      => 'login',
            'description' => 'User login',
        ]);
    }

    #[Test]
    public function log_method_stores_properties_as_array(): void
    {
        $properties = ['before' => ['status' => 'Review'], 'after' => ['status' => 'Valid']];

        $log = ActivityLog::log(ActivityLog::ACTION_VALIDATE, null, 'Validasi data', $properties);

        $this->assertIsArray($log->properties);
        $this->assertEquals($properties, $log->properties);
    }

    #[Test]
    public function log_method_accepts_null_model_and_properties(): void
    {
        $log = ActivityLog::log(ActivityLog::ACTION_LOGIN);

        $this->assertNull($log->model_type);
        $this->assertNull($log->model_id);
        $this->assertNull($log->properties);
    }

    // =========================================================================
    // Relasi: user()
    // =========================================================================

    #[Test]
    public function activity_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $log  = ActivityLog::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    #[Test]
    public function activity_log_user_can_be_null(): void
    {
        $log = ActivityLog::factory()->create(['user_id' => null]);

        $this->assertNull($log->user);
    }
}
