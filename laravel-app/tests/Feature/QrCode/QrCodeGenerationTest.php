<?php

namespace Tests\Feature\QrCode;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QrCodeGenerationTest extends TestCase
{
    public function test_can_generate_qr_code_with_default_options()
    {
        $response = $this->postJson('/api/qr-codes/generate', [
            'content' => 'https://example.com'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'qr_code',
                         'format',
                         'size',
                         'cached',
                         'generation_time_ms'
                     ]
                 ]);
    }

    public function test_can_generate_qr_code_with_custom_options()
    {
        $response = $this->postJson('/api/qr-codes/generate', [
            'content' => 'Hello World',
            'options' => [
                'size' => 400,
                'dot_style' => 'rounded',
                'color' => '#ff0000',
                'background' => '#ffffff',
                'file_type' => 'png',
                'output_type' => 'base64',
                'error_correction' => 'H'
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonPath('data.format', 'png')
                 ->assertJsonPath('data.size', '400x400');
    }

    public function test_validation_fails_for_empty_content()
    {
        $response = $this->postJson('/api/qr-codes/generate', [
            'content' => ''
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'errors'
                 ]);
    }

    public function test_validation_fails_for_invalid_size()
    {
        $response = $this->postJson('/api/qr-codes/generate', [
            'content' => 'test',
            'options' => [
                'size' => 5000 // Too large
            ]
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                 ]);
    }

    public function test_validation_fails_for_invalid_file_type()
    {
        $response = $this->postJson('/api/qr-codes/generate', [
            'content' => 'test',
            'options' => [
                'file_type' => 'gif' // Not supported
            ]
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                 ]);
    }

    public function test_can_get_qr_code_stats()
    {
        $response = $this->getJson('/api/qr-codes/stats');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'supported_formats',
                         'supported_output_types',
                         'max_content_length',
                         'size_limits',
                         'error_correction_levels',
                         'dot_styles'
                     ]
                 ]);
    }

    public function test_health_check_endpoint()
    {
        $response = $this->getJson('/api/qr-codes/health');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'status' => 'healthy'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'status',
                     'timestamp',
                     'version'
                 ]);
    }
}