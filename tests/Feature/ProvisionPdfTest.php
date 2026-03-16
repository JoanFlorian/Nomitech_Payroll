<?php

namespace Tests\Feature;

use App\Models\BenefitLedger;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvisionPdfTest extends TestCase
{
    public function test_comprobante_pdf_route_returns_pdf_content()
    {
        // Simulate a logged in user with a company
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $empresa = Empresa::factory()->create();
        session(['empresa_id' => $empresa->id_empresa]);
        
        // Create a fake movement
        $movement = BenefitLedger::create([
            'tenant_id' => $empresa->id_empresa,
            'employee_id' => '123456',
            'benefit_type' => 'prima',
            'amount' => -100000,
            'movement_type' => 'payment',
            'reference' => 'Test payment',
            'status' => 'processed',
            'destination' => 'employee'
        ]);
        
        // Request PDF
        $response = $this->get(route('provisiones.comprobante', $movement->id) . '?format=pdf&mode=inline');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
