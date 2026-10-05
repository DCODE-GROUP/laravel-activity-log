<?php

namespace Dcodegroup\ActivityLog\Tests;

use Carbon\Carbon;
use Dcodegroup\ActivityLog\Support\ActivityLogValueFormatterRegistry;
use Dcodegroup\ActivityLog\Tests\Fixtures\CaseNameEnumValueFormatter;
use Dcodegroup\ActivityLog\Tests\Fixtures\Level;
use Dcodegroup\ActivityLog\Tests\Fixtures\Priority;
use Dcodegroup\ActivityLog\Tests\Fixtures\StatusWithLabel;
use Dcodegroup\ActivityLog\Tests\Fixtures\ValueFormattingModel;
use Dcodegroup\ActivityLog\Tests\Fixtures\Visibility;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use stdClass;

class ActivityLogValueFormattingTest extends TestCase
{
    public function test_format_value_keeps_its_original_signature_and_returns_a_string(): void
    {
        $method = new ReflectionMethod(ValueFormattingModel::class, 'formatValue');

        $this->assertTrue($method->isPrivate());
        $this->assertCount(1, $method->getParameters());
        $this->assertNull($method->getParameters()[0]->getType());
        $this->assertNull($method->getReturnType());
        $this->assertSame('Open ticket', $method->invoke(new ValueFormattingModel, StatusWithLabel::Open));
    }

    #[DataProvider('enumValues')]
    public function test_enums_format_as_label_value_or_name(mixed $value, string $expected): void
    {
        $this->assertSame($expected, $this->registry()->format($value));
    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function enumValues(): array
    {
        return [
            'backed enum with label' => [StatusWithLabel::Open, 'Open ticket'],
            'backed enum value' => [Priority::Low, 'low'],
            'int backed enum value' => [Level::One, '1'],
            'unit enum name' => [Visibility::Public, 'Public'],
        ];
    }

    public function test_changing_a_backed_enum_with_label_logs_the_label(): void
    {
        $model = $this->model(['status' => StatusWithLabel::Open]);
        $model->status = StatusWithLabel::Closed;

        $description = $model->getModelChanges();

        $this->assertStringContainsString('Open ticket', $description);
        $this->assertStringContainsString('Closed ticket', $description);
    }

    public function test_changing_a_backed_enum_without_label_logs_the_value(): void
    {
        $model = $this->model(['priority' => Priority::Low]);
        $model->priority = Priority::High;

        $description = $model->getModelChanges();

        $this->assertStringContainsString('low', $description);
        $this->assertStringContainsString('high', $description);
    }

    public function test_changing_a_unit_enum_logs_the_name(): void
    {
        $model = $this->model(['visibility' => Visibility::Private]);
        $model->visibility = Visibility::Public;

        $description = $model->getModelChanges();

        $this->assertStringContainsString('Private', $description);
        $this->assertStringContainsString('Public', $description);
    }

    public function test_dates_booleans_and_arrays_use_the_default_handlers(): void
    {
        $registry = $this->registry();

        $this->assertSame('2.01.2026', $registry->format(Carbon::parse('2026-01-02 00:00:00')));
        $this->assertSame('4 Mar 2026 16:05pm', $registry->format(Carbon::parse('2026-03-04 16:05:00')));
        $this->assertSame('Yes', $registry->format(true));
        $this->assertSame('No', $registry->format(false));
        $this->assertSame(
            'Open ticket|No|alpha|7',
            $registry->format([StatusWithLabel::Open, false, 'alpha', 7]),
        );

        $model = $this->model([
            'starts_at' => Carbon::parse('2026-01-02 00:00:00'),
            'active' => false,
            'tags' => ['red'],
        ]);
        $model->starts_at = Carbon::parse('2026-03-04 16:05:00');
        $model->active = true;
        $model->tags = ['red', 'blue'];

        $description = $model->getModelChanges();

        $this->assertStringContainsString('2.01.2026', $description);
        $this->assertStringContainsString('4 Mar 2026 16:05pm', $description);
        $this->assertStringContainsString('No', $description);
        $this->assertStringContainsString('Yes', $description);
        $this->assertStringContainsString('red|blue', $description);
    }

    public function test_fallback_keeps_strings_and_uses_string_converter_for_other_values(): void
    {
        $registry = $this->registry();

        $this->assertSame('plain', $registry->format('plain'));
        $this->assertSame('', $registry->format(null));
        $this->assertSame('20', $registry->format(20));
        $this->assertSame('\stdClass', $registry->format(new stdClass));
        $this->assertSame('\stdClass', $registry->format([new stdClass]));
    }

    public function test_enum_formatting_can_be_overridden_from_config(): void
    {
        config([
            'activity-log.value_formatters' => array_merge(
                [CaseNameEnumValueFormatter::class],
                config('activity-log.value_formatters'),
            ),
        ]);

        $registry = $this->registry();

        $this->assertSame('case:Open', $registry->format(StatusWithLabel::Open));
        $this->assertSame('4 Mar 2026 16:05pm', $registry->format(Carbon::parse('2026-03-04 16:05:00')));

        $model = $this->model(['status' => StatusWithLabel::Open]);
        $model->status = StatusWithLabel::Closed;

        $description = $model->getModelChanges();

        $this->assertStringContainsString('case:Open', $description);
        $this->assertStringContainsString('case:Closed', $description);
        $this->assertStringNotContainsString('Open ticket', $description);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function model(array $attributes): ValueFormattingModel
    {
        $model = new ValueFormattingModel;
        $model->forceFill($attributes);
        $model->syncOriginal();

        return $model;
    }

    private function registry(): ActivityLogValueFormatterRegistry
    {
        return $this->app->make(ActivityLogValueFormatterRegistry::class);
    }
}
