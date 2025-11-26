<?php

namespace Webkul\Admin\Http\Controllers\Products;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage; // THÊM DÒNG NÀY
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;
use Webkul\Admin\DataGrids\Product\ProductDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\AttributeForm;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Resources\ProductResource;
use Webkul\Product\Repositories\ProductRepository;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository)
    {
        request()->request->add(['entity_type' => 'products']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(ProductDataGrid::class)->process();
        }

        return view('admin::products.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(AttributeForm $request)
    {
        Event::dispatch('product.create.before');
        \Log::info('Images uploaded:', ['count' => $request->hasFile('images') ? count($request->file('images')) : 0]);
        $data = $request->except('images'); // phần attribute
        $images = $request->file('images'); // lấy riêng ảnh
    
        $product = $this->productRepository->create($data);
    
        // LƯU ẢNH RIÊNG (bắt buộc phải tách ra như này)
        if ($images) {
            foreach ($images as $index => $imageFile) {
                $path = $imageFile->store('product', 'public');
    
                $product->images()->create([
                    'path'         => $path,
                    'is_thumbnail' => $index === 0,
                    'sort_order'   => $index,
                ]);
            }
        }

        Event::dispatch('product.create.after', $product);

        session()->flash('success', trans('admin::app.products.index.create-success'));

        return redirect()->route('admin.products.index');
    }

    /**
     * Show the form for viewing the specified resource.
     */
    public function view(int $id): View
    {
        // $product = $this->productRepository->findOrFail($id);
        $product = $this->productRepository->with(['images'])->findOrFail($id);

        return view('admin::products.view', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|JsonResponse
    {
        // $product = $this->productRepository->findOrFail($id);
        $product = $this->productRepository->with(['images'])->findOrFail($id);

        $product->uploaded_images = $product->images->map(function ($image) {
            return [
                'id'            => $image->id,
                'path'          => $image->path,
                'url'           => $image->getUrl(), // hoặc $image->url nếu bạn dùng accessor cũ
                'sort_order'    => $image->sort_order,
                'is_thumbnail'  => $image->is_thumbnail,
            ];
        })->toArray();

        $inventories = $product->inventories()
            ->with('location')
            ->get()
            ->map(function ($inventory) {
                return [
                    'id'                    => $inventory->id,
                    'name'                  => $inventory->location->name,
                    'warehouse_id'          => $inventory->warehouse_id,
                    'warehouse_location_id' => $inventory->warehouse_location_id,
                    'in_stock'              => $inventory->in_stock,
                    'allocated'             => $inventory->allocated,
                ];
            });

        return view('admin::products.edit', compact('product', 'inventories'));
    }

    /**
     * Update the specified resource in storage.
     * * ĐÃ CHỈNH SỬA LOGIC XỬ LÝ ẢNH TẠI ĐÂY
     */
    public function update(AttributeForm $request, int $id)
    {
        Event::dispatch('product.update.before', $id);

        $product = $this->productRepository->findOrFail($id);

        $data = $request->except('images'); // Lấy dữ liệu thuộc tính
        $newImages = $request->file('images'); // Lấy các file ảnh mới được upload
    
        // 1. Cập nhật các thuộc tính sản phẩm (Name, SKU, Description, etc.)
        $this->productRepository->update($data, $id);
        
        // Cần tải lại mối quan hệ 'images' để đảm bảo processDeletedImages hoạt động với dữ liệu hiện tại
        $product->load('images');
        
        // 2. Xử lý xóa ảnh: Xóa file vật lý và bản ghi database của ảnh cũ không được giữ lại
        $this->processDeletedImages($request, $product);

        // 3. Xử lý ảnh mới: Thêm ảnh mới vào database và storage
        if ($newImages) {
            // Sau khi xóa (Bước 2), cần tải lại một lần nữa để lấy các ảnh còn lại
            $product->load('images'); 
            
            // Lấy thứ tự sắp xếp (sort_order) lớn nhất hiện có để thêm ảnh mới tiếp nối
            $lastSortOrder = $product->images->max('sort_order') ?? -1;

            foreach ($newImages as $index => $imageFile) {
                $path = $imageFile->store('product', 'public');
    
                $product->images()->create([
                    'path'         => $path,
                    // Giữ is_thumbnail = false, reassignThumbnail sẽ lo việc sắp xếp
                    'is_thumbnail' => false, 
                    'sort_order'   => $lastSortOrder + $index + 1,
                ]);
            }
        }
        
        // 4. Cập nhật lại thumbnail sau khi thêm/xóa.
        // Cần tải lại lần cuối để đảm bảo reassignThumbnail sắp xếp lại đúng
        $product->load('images'); 
        $this->reassignThumbnail($product);

        Event::dispatch('product.update.after', $product);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.products.index.update-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.products.index.update-success'));

        return redirect()->route('admin.products.index');
    }

    /**
     * Helper function to handle image deletions sent by the x-admin::media.images component.
     * * ĐÃ CHỈNH SỬA LOGIC SO SÁNH PATHS
     */
    protected function processDeletedImages(AttributeForm $request, $product)
    {
        // $currentImagePaths: mảng chứa đường dẫn (path) của ảnh CŨ được giữ lại từ frontend.
        $currentImagePaths = $request->input('images') ?? []; 
        
        // Lấy danh sách path hiện có trong database cho sản phẩm này
        $oldImagePaths = $product->images->pluck('path')->toArray();
        
        // Tìm các đường dẫn ảnh cần xóa (có trong DB nhưng KHÔNG có trong request)
        $pathsToDelete = array_diff($oldImagePaths, $currentImagePaths);

        // Lọc qua các ảnh hiện tại của sản phẩm để tìm và xóa
        foreach ($product->images as $image) {
            if (in_array($image->path, $pathsToDelete)) {
                // Xóa file khỏi Storage
                Storage::disk('public')->delete($image->path);
                
                // Xóa bản ghi khỏi database
                $image->delete(); 
            }
        }
    }

    /**
     * Helper function to reassign the thumbnail status.
     */
    protected function reassignThumbnail($product)
    {
        // Lấy lại danh sách ảnh sau khi thêm/xóa, sắp xếp theo sort_order
        $images = $product->images()->orderBy('sort_order')->get();
        
        // Đảm bảo chỉ có một ảnh là thumbnail, thường là ảnh đầu tiên.
        if ($images->isNotEmpty()) {
            foreach ($images as $key => $image) {
                // Nếu là ảnh đầu tiên, đặt làm thumbnail
                $isThumbnail = $key === 0;

                // Chỉ cập nhật nếu trạng thái thay đổi để tránh query không cần thiết
                if ($image->is_thumbnail !== $isThumbnail) {
                    $image->is_thumbnail = $isThumbnail;
                    $image->save();
                }
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeInventories(int $id, ?int $warehouseId = null): JsonResponse
    {
        $this->validate(request(), [
            'inventories'                         => 'array',
            'inventories.*.warehouse_location_id' => 'required',
            'inventories.*.warehouse_id'          => 'required',
            'inventories.*.in_stock'              => 'required|integer|min:0',
            'inventories.*.allocated'             => 'required|integer|min:0',
        ]);

        $product = $this->productRepository->findOrFail($id);

        Event::dispatch('product.update.before', $id);

        $this->productRepository->saveInventories(request()->all(), $id, $warehouseId);

        Event::dispatch('product.update.after', $product);

        return new JsonResponse([
            'message' => trans('admin::app.products.index.update-success'),
        ], 200);
    }

    /**
     * Search product results
     */
    public function search(): JsonResource
    {
        $products = $this->productRepository
            ->pushCriteria(app(RequestCriteria::class))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * Returns product inventories grouped by warehouse.
     */
    public function warehouses(int $id): JsonResponse
    {
        $warehouses = $this->productRepository->getInventoriesGroupedByWarehouse($id);

        return response()->json(array_values($warehouses));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        try {
            Event::dispatch('settings.products.delete.before', $id);
            // XÓA ẢNH TRONG STORAGE + DATABASE
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path); // Đã sửa \Storage thành Storage
                $image->delete();
            }

            $product->delete($id);

            Event::dispatch('settings.products.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.products.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => trans('admin::app.products.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Mass Delete the specified resources.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('product.delete.before', $index);

            $this->productRepository->delete($index);

            Event::dispatch('product.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('admin::app.products.index.delete-success'),
        ]);
    }
}