<?php

namespace Tests\Feature;

use App\Models\ContentItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_content_item_with_an_uploaded_image(): void
    {
        Storage::fake('public');
        $this->seed();

        $item = ContentItem::where('type', 'product')->firstOrFail();

        $imagePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'machine.png';
        file_put_contents($imagePath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));

        $this->withSession(['admin_authenticated' => true])->put(route('admin.items.update', $item), [
            'type' => 'product',
            'title' => 'Updated Dispenser',
            'subtitle' => 'Fresh Label',
            'description' => 'Updated product description.',
            'url' => '',
            'sort_order' => 1,
            'is_published' => '1',
            'image' => new UploadedFile($imagePath, 'machine.png', 'image/png', null, true),
        ])->assertRedirect(route('admin.items.edit', $item));

        $item->refresh();

        $this->assertSame('Updated Dispenser', $item->title);
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);
    }
}
