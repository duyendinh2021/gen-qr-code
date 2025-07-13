<?php

namespace Tests\Feature\QrCode;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class QrCodeStorageTest extends TestCase
{
    public function test_can_generate_qr_code_with_storage_output_type()
    {
        // Create the public disk for testing
        Storage::fake('public');
        
        $response = $this->postJson('/api/qr-codes/generate', [
            'content' => 'https://example.com/test-storage',
            'options' => [
                'output_type' => 'storage',
                'file_type' => 'png'
            ]
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

        // Verify the response contains a storage URL
        $data = $response->json('data');
        $this->assertStringContains('/storage/qr-codes/', $data['qr_code']);
    }

    public function test_storage_directory_structure_is_correct()
    {
        $storagePath = storage_path('app/public/qr-codes');
        $publicSymlink = public_path('storage');
        
        // Verify storage directory exists
        $this->assertTrue(is_dir($storagePath), 'QR codes storage directory should exist');
        
        // Verify public symlink exists
        $this->assertTrue(is_link($publicSymlink), 'Public storage symlink should exist');
        
        // Verify symlink points to correct location
        $this->assertEquals(
            realpath(storage_path('app/public')),
            realpath($publicSymlink),
            'Symlink should point to storage/app/public'
        );
    }

    public function test_qr_code_file_is_accessible_via_symlink()
    {
        // Create a test file in storage
        $filename = 'test-' . time() . '.png';
        $storagePath = storage_path('app/public/qr-codes/' . $filename);
        $testContent = 'Test QR Code Data';
        
        // Ensure directory exists
        $directory = dirname($storagePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($storagePath, $testContent);
        
        // Verify file exists in storage
        $this->assertTrue(file_exists($storagePath), 'File should exist in storage');
        
        // Verify file is accessible via public symlink
        $publicPath = public_path('storage/qr-codes/' . $filename);
        $this->assertTrue(file_exists($publicPath), 'File should be accessible via public symlink');
        
        // Verify content is the same
        $this->assertEquals($testContent, file_get_contents($publicPath), 'Content should be identical');
        
        // Clean up
        unlink($storagePath);
    }

    public function test_supported_output_types_include_storage()
    {
        $response = $this->getJson('/api/qr-codes/stats');
        
        $response->assertStatus(200)
                 ->assertJsonPath('data.supported_output_types.0', 'storage')
                 ->assertJsonPath('data.supported_output_types.1', 'base64')
                 ->assertJsonPath('data.supported_output_types.2', 'stream');
    }
}