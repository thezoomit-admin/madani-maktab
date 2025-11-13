<?php

namespace Tests\Unit\Services;

use App\Enums\Department;
use App\Models\HijriMonth;
use App\Models\User;
use App\Services\RegIdGeneratorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegIdGeneratorServiceTest extends TestCase
{
    private RegIdGeneratorService $service;
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        $this->service = new RegIdGeneratorService();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('hijri_months');

        parent::tearDown();
    }

    /** @test */
    public function it_generates_maktab_reg_id_when_none_exists(): void
    {
        $this->createActiveHijriMonth(1447);

        $regId = $this->service->generate(Department::Maktab, 1);

        $this->assertEquals('47101', $regId);
    }

    /** @test */
    public function it_generates_maktab_reg_id_with_incremented_sequence(): void
    {
        $this->createActiveHijriMonth(1447);
        User::create([
            'reg_id' => '47105',
        ]);

        $regId = $this->service->generate(Department::Maktab, 1);

        $this->assertEquals('47106', $regId);
    }

    /** @test */
    public function it_generates_kitab_reg_id_when_none_exists(): void
    {
        $this->createActiveHijriMonth(1447);

        $regId = $this->service->generate(Department::Kitab, 1);

        $this->assertEquals('047101', $regId);
    }

    /** @test */
    public function it_generates_kitab_reg_id_with_incremented_sequence(): void
    {
        $this->createActiveHijriMonth(1447);
        User::create([
            'reg_id' => '047107',
        ]);

        $regId = $this->service->generate(Department::Kitab, 1);

        $this->assertEquals('047108', $regId);
    }

    /** @test */
    public function it_throws_exception_when_active_month_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('কোন অ্যাকটিভ হিজরি মাস নেই।');

        $this->service->generate(Department::Maktab, 1);
    }

    private function setUpDatabase(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('hijri_months');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('reg_id')->nullable();
            $table->string('name')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hijri_months', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    private function createActiveHijriMonth(int $year): HijriMonth
    {
        return HijriMonth::create([
            'year' => $year,
            'month' => 1,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);
    }
}

