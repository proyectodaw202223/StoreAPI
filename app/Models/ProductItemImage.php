<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use App\Exceptions\RestrictedDeletionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Exception;

class ProductItemImage extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createImage(Request $request): ProductItemImage {
        $requestData = $request->all();

        // Image is stored at <app-root-directory>/storage/app/public/images/
        $imagePath = $request->file('image')->store('images', 'public');
        
        // For the url to work properly a symlink from /storage/app/public 
        // to /public/storage must exist, to accomplis this the command
        // 'php artisan storage:link' must be run onece in the server.
        $url = asset('storage/'.$imagePath);

        $image = ProductItemImage::create([
            'itemId' => $requestData['itemId'],
            'imagePath' => $imagePath,
            'url' => $url
        ]);
        
        return $image;
    }

    public static function deleteImage(ProductItemImage $image): void {
        try {
            $relativeImagePath = $image->imagePath;
            $fullImagePath = storage_path('app/public/').$relativeImagePath;

            $image->delete();

            if (self::isImageInUse($relativeImagePath))
                return;
            
            if (file_exists($fullImagePath))
                unlink($fullImagePath);
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
        }
    }
    
    public static function findAllImages(): array {
        $images = DB::table('product_item_images')->get();

        return ProductItemImage::hydrate($images->toArray())->all();
    }

    public static function findByIdOrFail(int $id): ProductItemImage {
        $image = DB::table('product_item_images')->find($id);

        if (!$image)
            throw new NotFoundException();
        
        $image = ProductItemImage::hydrate([$image])[0];
        return $image;
    }

    public static function findImagesByItemId(int $itemId): array {
        $images = DB::table('product_item_images')
            ->where('itemId', '=', $itemId)
            ->get();

        return ProductItemImage::hydrate($images->toArray())->all();
    }

    public static function findImagesByImagePath(string $imagePath): array {
        $images = DB::table('product_item_images')
            ->where('imagePath', '=', $imagePath)
            ->get();

        return ProductItemImage::hydrate($images->toArray())->all();
    }

    public static function isImageInUse(string $imagePath): bool {
        $images = self::findImagesByImagePath($imagePath);

        return (count($images) > 0);
    } 
}
