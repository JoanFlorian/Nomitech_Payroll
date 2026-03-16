<?php

namespace Tests\Feature;

use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use App\Models\CesantiasWithdrawal;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Services\Benefits\BenefitPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CesantiasServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BenefitPaymentService $service;
    protected int $companyId;
    protected string $employeeDoc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BenefitPaymentService();

        // Create test company
        $empresa = Empresa::create([
            'nit' => '900123456',
            'razon_social' => 'Test Company SAS',
            'doc_representante' => '999999',
            'id_ciudad' => 1,
            'direccion' => 'Calle 1 #2-3',
            'correo' => 'test@test.com',
            'telefono' => '1234567',
        ]);
        $this->companyId = $empresa->id_empresa;

        // Create test employee
        $this->employeeDoc = '12345678';
        Usuario::create([
            'doc' => $this->employeeDoc,
            'id_tipo_doc' => 1,
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'correo' => 'juan@test.com',
            'contrasena' => bcrypt('password'),
            'fondo_cesantias' => 'Porvenir',
        ]);

        // Create initial cesantías balance of $1,000,000
        BenefitBalance::create([
            'employee_id' => $this->employeeDoc,
            'tenant_id' => $this->companyId,
            'prima_balance' => 0,
            'cesantias_balance' => 1000000,
            'intereses_balance' => 0,
            'vacaciones_balance' => 0,
        ]);

        // Create corresponding ledger entry for initial balance
        BenefitLedger::create([
            'tenant_id' => $this->companyId,
            'employee_id' => $this->employeeDoc,
            'benefit_type' => BenefitLedger::TYPE_CESANTIAS,
            'movement_type' => BenefitLedger::MOVEMENT_INITIAL,
            'amount' => 1000000,
            'source' => BenefitLedger::SOURCE_MIGRATION,
            'reference' => 'Test initial balance',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    //  1. Company withdrawal reduces balance
    // ──────────────────────────────────────────────────────────────────

    public function test_company_withdrawal_reduces_cesantias_balance(): void
    {
        $withdrawal = $this->service->withdrawFromCompany(
            $this->employeeDoc,
            300000,
            'housing',
            $this->companyId
        );

        // Withdrawal record created
        $this->assertInstanceOf(CesantiasWithdrawal::class, $withdrawal);
        $this->assertEquals('company', $withdrawal->payment_origin);
        $this->assertEquals('approved', $withdrawal->status);
        $this->assertEquals(300000, (float) $withdrawal->amount);

        // Balance reduced
        $balance = BenefitBalance::where('employee_id', $this->employeeDoc)
            ->where('tenant_id', $this->companyId)
            ->first();
        $this->assertEquals(700000, (float) $balance->cesantias_balance);

        // Ledger entry created with negative amount
        $ledgerEntry = BenefitLedger::where('employee_id', $this->employeeDoc)
            ->where('movement_type', BenefitLedger::MOVEMENT_WITHDRAWAL)
            ->latest('id')
            ->first();
        $this->assertNotNull($ledgerEntry);
        $this->assertEquals(-300000, (float) $ledgerEntry->amount);
        $this->assertEquals('employee', $ledgerEntry->destination);
    }

    // ──────────────────────────────────────────────────────────────────
    //  2. Withdrawal exceeding balance fails
    // ──────────────────────────────────────────────────────────────────

    public function test_withdrawal_exceeding_balance_throws_exception(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('supera el saldo');

        $this->service->withdrawFromCompany(
            $this->employeeDoc,
            2000000, // exceeds 1,000,000 balance
            'education',
            $this->companyId
        );
    }

    // ──────────────────────────────────────────────────────────────────
    //  3. Fund authorization generates certificate without balance change
    // ──────────────────────────────────────────────────────────────────

    public function test_fund_authorization_generates_certificate_without_balance_change(): void
    {
        $withdrawal = $this->service->authorizeFundWithdrawal(
            $this->employeeDoc,
            500000,
            'housing',
            $this->companyId
        );

        // Withdrawal record created with fund origin
        $this->assertInstanceOf(CesantiasWithdrawal::class, $withdrawal);
        $this->assertEquals('fund', $withdrawal->payment_origin);
        $this->assertEquals('approved', $withdrawal->status);
        $this->assertNotNull($withdrawal->certificate_path);

        // Balance NOT changed (fund handles actual payment)
        $balance = BenefitBalance::where('employee_id', $this->employeeDoc)
            ->where('tenant_id', $this->companyId)
            ->first();
        $this->assertEquals(1000000, (float) $balance->cesantias_balance);

        // Authorization ledger entry created with amount = 0
        $ledgerEntry = BenefitLedger::where('employee_id', $this->employeeDoc)
            ->where('movement_type', BenefitLedger::MOVEMENT_AUTHORIZATION)
            ->latest('id')
            ->first();
        $this->assertNotNull($ledgerEntry);
        $this->assertEquals(0, (float) $ledgerEntry->amount);
        $this->assertEquals('fund', $ledgerEntry->destination);
    }

    // ──────────────────────────────────────────────────────────────────
    //  4. Annual deposit creates ledger payments for all positive balances
    // ──────────────────────────────────────────────────────────────────

    public function test_annual_deposit_creates_ledger_payments(): void
    {
        $batchesData = $this->service->generarConsignacionAnual($this->companyId, 2025);

        // Should have one batch (one fund: Porvenir)
        $this->assertCount(1, $batchesData);
        $this->assertEquals('Porvenir', $batchesData[0]['fund']);
        $this->assertEquals(1, $batchesData[0]['count']);
        $this->assertEquals(1000000, $batchesData[0]['total_amount']);

        // Balance zeroed out
        $balance = BenefitBalance::where('employee_id', $this->employeeDoc)
            ->where('tenant_id', $this->companyId)
            ->first();
        $this->assertEquals(0, (float) $balance->cesantias_balance);

        // Payment ledger entry for fund consignment
        $ledgerEntry = BenefitLedger::where('employee_id', $this->employeeDoc)
            ->where('movement_type', BenefitLedger::MOVEMENT_PAYMENT)
            ->where('destination', BenefitLedger::DESTINATION_FUND)
            ->latest('id')
            ->first();
        $this->assertNotNull($ledgerEntry);
        $this->assertEquals(-1000000, (float) $ledgerEntry->amount);
        $this->assertNotNull($ledgerEntry->batch_id);
    }

    // ──────────────────────────────────────────────────────────────────
    //  5. Ledger history remains intact (immutability)
    // ──────────────────────────────────────────────────────────────────

    public function test_ledger_entries_are_never_deleted(): void
    {
        // Perform several operations
        $this->service->withdrawFromCompany($this->employeeDoc, 100000, 'housing', $this->companyId);
        $this->service->withdrawFromCompany($this->employeeDoc, 200000, 'education', $this->companyId);

        // Count all ledger entries (initial + 2 withdrawals)
        $totalEntries = BenefitLedger::where('employee_id', $this->employeeDoc)
            ->where('tenant_id', $this->companyId)
            ->count();
        $this->assertEquals(3, $totalEntries);

        // Verify balance is arithmetically correct from ledger
        $ledgerSum = (float) BenefitLedger::where('employee_id', $this->employeeDoc)
            ->where('tenant_id', $this->companyId)
            ->where('benefit_type', BenefitLedger::TYPE_CESANTIAS)
            ->sum('amount');

        $balance = BenefitBalance::where('employee_id', $this->employeeDoc)
            ->where('tenant_id', $this->companyId)
            ->first();

        // Ledger sum should match balance
        $this->assertEquals($ledgerSum, (float) $balance->cesantias_balance);
        // 1,000,000 - 100,000 - 200,000 = 700,000
        $this->assertEquals(700000, (float) $balance->cesantias_balance);
    }
}
