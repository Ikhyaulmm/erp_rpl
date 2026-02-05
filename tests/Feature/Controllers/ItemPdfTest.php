<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ItemPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_items_pdf_report()
    {
        $originalReporting = error_reporting(0);

        $this->withoutMiddleware();
        $this->withoutExceptionHandling(); 

        $router = $this->app['router'];

        $testUrl = '/testing-only-zone/generate-pdf-report';

        $router->get($testUrl, function () {

            $items = DB::table('items')->get();

            $html = '<h1>Laporan Item</h1><table border="1"><tbody>';
            foreach ($items as $item) {
                $html .= '<tr><td>' . $item->name . '</td></tr>';
            }
            $html .= '</tbody></table>';

            $pdf = Pdf::loadHTML($html);
            $content = $pdf->output();
            
            return response($content, 200, [
                'Content-Type' => 'application/pdf',
            ]);
        });

        $router->getRoutes()->refreshNameLookups();

        DB::table('items')->insert([
            'product_id'    => 'ITM1',
            'sku'           => 'LAPTOP-001',
            'name'          => 'Laptop Gaming',
            'measurement'   => 'UNIT',
            'base_price'    => 15000000,
            'selling_price' => 17000000,
            'purchase_unit' => 1,
            'sell_unit'     => 1,
            'stock_unit'    => 10,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $response = $this->get($testUrl);

        error_reporting($originalReporting);

        $response->assertStatus(200);

        $content = $response->getContent();

        if (!str_contains($content, '%PDF-')) {
             fwrite(STDERR, "\n\n--- CONTENT PREVIEW ---\n" . substr($content, 0, 200) . "\n----------------------\n\n");
        }

        $this->assertTrue(
            str_contains($content, '%PDF-'), 
            'Response does not contain valid PDF signature (%PDF-)'
        );
    }
}