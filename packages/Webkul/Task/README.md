# Task Module - Giải thích mục đích các file

## 📁 Cấu trúc Module Task

### 1. **Backend (Core Module) - `packages/Webkul/Task/src/`**

#### **Model Layer**

**`Models/Task.php`**
- **Mục đích**: Eloquent Model đại diện cho bảng `tasks` trong database
- **Chức năng**:
  - Định nghĩa các cột có thể fill (`fillable`): title, description, status, priority, due_date, etc.
  - Định nghĩa type casting (`casts`): chuyển `due_date` thành date, `completed_at` thành datetime
  - Định nghĩa relationships:
    - `user()`: Task thuộc về User nào (owner)
    - `assignedUser()`: Task được giao cho User nào
    - `activities()`: Các activities liên quan đến task
  - Sử dụng traits: `CustomAttribute` (hỗ trợ custom attributes), `LogsActivity` (ghi log activity)

**`Models/TaskProxy.php`**
- **Mục đích**: Proxy class cho Task model (theo pattern Concord)
- **Chức năng**:
  - Cho phép Laravel resolve model thông qua interface `TaskContract`
  - Hỗ trợ dependency injection và loose coupling
  - Cho phép override model class nếu cần

**`Contracts/Task.php`**
- **Mục đích**: Interface contract định nghĩa contract cho Task model
- **Chức năng**:
  - Đảm bảo type safety khi sử dụng Task
  - Cho phép dependency injection dễ dàng
  - Tuân thủ pattern Repository/Contract

---

#### **Repository Layer**

**`Repositories/TaskRepository.php`**
- **Mục đích**: Repository pattern - xử lý tất cả database operations cho Task
- **Chức năng**:
  - `fieldSearchable`: Các field có thể search/filter
  - `model()`: Trả về model class (Task::class)
  - `create()`: Tạo task mới + lưu custom attributes
  - `update()`: Cập nhật task + cập nhật custom attributes
  - Xử lý integration với Attribute system (custom attributes)

---

#### **Service Providers**

**`Providers/ModuleServiceProvider.php`**
- **Mục đích**: Đăng ký module với Concord framework
- **Chức năng**:
  - Extend `BaseModuleServiceProvider` (tự động load migrations, views, routes)
  - Đăng ký models trong `$models` array để Concord quản lý
  - Tự động load migrations từ `Database/Migrations/`
  - **Quan trọng**: Phải được đăng ký trong `config/concord.php`

**`Providers/TaskServiceProvider.php`**
- **Mục đích**: Service provider chính cho Task module
- **Chức năng**:
  - Đăng ký breadcrumbs cho routes: `tasks`, `tasks.create`, `tasks.edit`
  - Load migrations (nếu cần - nhưng ModuleServiceProvider đã làm rồi)
  - Đăng ký các service/helper khác nếu cần
  - **Quan trọng**: Phải được đăng ký trong `config/app.php`

---

#### **Database**

**`Database/Migrations/2025_11_06_040307_create_tasks_table.php`**
- **Mục đích**: Migration tạo bảng `tasks`
- **Cấu trúc bảng**:
  - `id`: Primary key
  - `title`: Tiêu đề task
  - `description`: Mô tả task
  - `status`: Trạng thái (pending, in_progress, completed, cancelled)
  - `priority`: Độ ưu tiên (low, medium, high, urgent)
  - `due_date`: Ngày hết hạn
  - `completed_at`: Ngày hoàn thành
  - `user_id`: Owner (FK → users)
  - `assigned_to`: Người được giao (FK → users, nullable)
  - `timestamps`: created_at, updated_at

---

### 2. **Admin Panel - `packages/Webkul/Admin/src/`**

#### **Controller Layer**

**`Http/Controllers/Task/TaskController.php`**
- **Mục đích**: Controller xử lý HTTP requests cho Task management
- **Các methods**:
  - `index()`: Hiển thị danh sách tasks (trả về view hoặc JSON cho DataGrid)
  - `create()`: Hiển thị form tạo task mới
  - `store()`: Lưu task mới (POST)
  - `edit()`: Hiển thị form sửa task
  - `update()`: Cập nhật task (PUT)
  - `destroy()`: Xóa task (DELETE)
  - `massDestroy()`: Xóa nhiều tasks (POST)
- **Chức năng**:
  - Validate request thông qua `AttributeForm`
  - Dispatch events: `task.create.before`, `task.create.after`, etc.
  - Xử lý AJAX vs normal requests
  - Flash messages cho success/error

---

#### **Routes**

**`Routes/Admin/tasks-routes.php`**
- **Mục đích**: Định nghĩa tất cả routes cho Task module
- **Routes**:
  - `GET /admin/tasks` → `index()` → `admin.tasks.index`
  - `GET /admin/tasks/create` → `create()` → `admin.tasks.create`
  - `POST /admin/tasks/create` → `store()` → `admin.tasks.store`
  - `GET /admin/tasks/edit/{id}` → `edit()` → `admin.tasks.edit`
  - `PUT /admin/tasks/edit/{id}` → `update()` → `admin.tasks.update`
  - `DELETE /admin/tasks/{id}` → `destroy()` → `admin.tasks.delete`
  - `POST /admin/tasks/mass-destroy` → `massDestroy()` → `admin.tasks.mass_delete`
- **Quan trọng**: Phải được require trong `Routes/Admin/web.php`

---

#### **DataGrid**

**`DataGrids/Task/TaskDataGrid.php`**
- **Mục đích**: Định nghĩa cấu trúc DataGrid (bảng dữ liệu) cho danh sách tasks
- **Chức năng**:
  - `prepareQueryBuilder()`: Build SQL query với joins, filters
  - `prepareColumns()`: Định nghĩa các cột hiển thị (title, status, priority, owner, due_date, etc.)
  - `prepareActions()`: Định nghĩa actions (edit, delete buttons)
  - `prepareMassActions()`: Định nghĩa mass actions (xóa nhiều)
  - Hỗ trợ filtering, sorting, pagination

---

#### **Views (Blade Templates)**

**`Resources/views/tasks/index.blade.php`**
- **Mục đích**: View hiển thị danh sách tasks
- **Chức năng**:
  - Hiển thị breadcrumbs
  - Nút "Create Task"
  - DataGrid shimmer (loading state)
  - Vue component sẽ render DataGrid

**`Resources/views/tasks/create.blade.php`**
- **Mục đích**: Form tạo task mới
- **Chức năng**:
  - Form fields: title, description, status, priority, due_date
  - Validation errors display
  - Submit button

**`Resources/views/tasks/edit.blade.php`**
- **Mục đích**: Form sửa task
- **Chức năng**:
  - Tương tự create nhưng pre-fill data từ `$task`
  - Method PUT để update

---

#### **Translation**

**`Resources/lang/en/app.php`** (section `tasks`)
- **Mục đích**: Chứa tất cả text translation cho Task module
- **Các keys**:
  - `tasks.index.*`: Translation cho trang index
  - `tasks.create.*`: Translation cho trang create
  - `tasks.edit.*`: Translation cho trang edit
  - `tasks.status.*`: Translation cho các status
  - `tasks.priority.*`: Translation cho các priority

---

### 3. **Configuration Files**

#### **`composer.json`** (autoload section)
```json
"Webkul\\Task\\": "packages/Webkul/Task/src"
```
- **Mục đích**: Đăng ký namespace cho autoloading
- **Chức năng**: Cho phép PHP tự động load classes từ namespace `Webkul\Task\`

#### **`config/app.php`** (providers section)
```php
Webkul\Task\Providers\TaskServiceProvider::class,
```
- **Mục đích**: Đăng ký TaskServiceProvider với Laravel
- **Chức năng**: Laravel sẽ load service provider này khi khởi động

#### **`config/concord.php`** (modules section)
```php
\Webkul\Task\Providers\ModuleServiceProvider::class,
```
- **Mục đích**: Đăng ký Task module với Concord framework
- **Chức năng**:
  - Concord sẽ tự động load models, migrations, views
  - Đăng ký Task model với proxy system
  - Quan trọng nhất: Giải quyết lỗi "must be an instance of Model"

#### **`packages/Webkul/Admin/src/Config/menu.php`**
```php
[
    'key' => 'tasks',
    'name' => 'admin::app.layouts.tasks',
    'route' => 'admin.tasks.index',
    'sort' => 10,
    'icon-class' => 'icon-task',
],
```
- **Mục đích**: Đăng ký menu item "Tasks" vào sidebar
- **Chức năng**: Hiển thị menu "Tasks" trên sidebar với icon và route

---

## 🔄 Luồng hoạt động (Flow)

### **Khi user truy cập `/admin/tasks`:**

1. **Route** (`tasks-routes.php`) → `GET /admin/tasks` → `TaskController@index`
2. **Controller** (`TaskController.php`) → `index()` method
3. **Nếu AJAX request** → Trả về DataGrid JSON
4. **Nếu normal request** → Trả về view `tasks/index.blade.php`
5. **View** → Load DataGrid component → Gọi AJAX để lấy data
6. **Repository** (`TaskRepository.php`) → Query database
7. **Model** (`Task.php`) → Eloquent ORM → Trả về data
8. **DataGrid** → Render table với data

### **Khi user tạo task mới:**

1. **Route** → `GET /admin/tasks/create` → `TaskController@create`
2. **View** → `tasks/create.blade.php` → Hiển thị form
3. **User submit** → `POST /admin/tasks/create` → `TaskController@store`
4. **Controller** → Validate → Gọi `TaskRepository->create()`
5. **Repository** → Tạo Task + lưu custom attributes
6. **Model** → Insert vào database
7. **Response** → Redirect về index với success message

---

## 📝 Tóm tắt mục đích từng file

| File | Mục đích | Vị trí trong kiến trúc |
|------|----------|------------------------|
| `Task.php` | Model - Đại diện bảng tasks | Data Layer |
| `TaskProxy.php` | Proxy cho Model | Dependency Injection |
| `Task.php` (Contract) | Interface contract | Contract Layer |
| `TaskRepository.php` | Xử lý database operations | Data Access Layer |
| `ModuleServiceProvider.php` | Đăng ký với Concord | Module Registration |
| `TaskServiceProvider.php` | Đăng ký breadcrumbs, services | Service Registration |
| `TaskController.php` | Xử lý HTTP requests | Controller Layer |
| `tasks-routes.php` | Định nghĩa routes | Routing Layer |
| `TaskDataGrid.php` | Cấu trúc DataGrid | Presentation Layer |
| `index.blade.php` | View danh sách | View Layer |
| `create.blade.php` | Form tạo mới | View Layer |
| `edit.blade.php` | Form sửa | View Layer |
| `create_tasks_table.php` | Migration tạo bảng | Database Schema |

---

## ⚠️ Lưu ý quan trọng

1. **ModuleServiceProvider** phải có trong `config/concord.php` → Để Concord quản lý models
2. **TaskServiceProvider** phải có trong `config/app.php` → Để Laravel load service provider
3. **Autoload** trong `composer.json` → Để PHP tự động load classes
4. **Routes** phải được require trong `Routes/Admin/web.php` → Để routes được đăng ký
5. **Menu** phải có trong `menu.php` → Để hiển thị trên sidebar

---

## 🎯 Kiến trúc tổng quan

```
User Request
    ↓
Routes (tasks-routes.php)
    ↓
Controller (TaskController.php)
    ↓
Repository (TaskRepository.php)
    ↓
Model (Task.php)
    ↓
Database (tasks table)
```

**Và ngược lại khi render view:**
```
Database → Model → Repository → Controller → View → User
```

---

## ✅ Checklist khi tạo module mới

- [x] Model với fillable, casts, relationships
- [x] Contract interface
- [x] Proxy class
- [x] Repository với CRUD methods
- [x] ModuleServiceProvider (đăng ký trong concord.php)
- [x] ServiceProvider (đăng ký trong app.php)
- [x] Migration
- [x] Controller với CRUD methods
- [x] Routes (require trong web.php)
- [x] DataGrid (nếu cần)
- [x] Views (index, create, edit)
- [x] Translation
- [x] Menu config
- [x] Autoload trong composer.json
