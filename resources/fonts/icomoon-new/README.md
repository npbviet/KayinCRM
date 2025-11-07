# Hướng dẫn thêm icon mới vào icomoon-new

## Các bước thêm icon mới:

### Bước 1: Mở IcoMoon App

1. Truy cập: https://icomoon.io/app
2. Click **"Import Icons"** (hoặc **"Load"** → **"Load Project"**)
3. Chọn file `selection.json` trong thư mục này để import bộ icon hiện tại

### Bước 2: Thêm icon mới

1. Chọn icon từ thư viện IcoMoon hoặc upload SVG của bạn
2. Icon sẽ được thêm vào selection (danh sách bên phải)
3. Đặt tên cho icon (ví dụ: `my-new-icon`)

### Bước 3: Export font

1. Click **"Generate Font"** (hoặc **"Generate SVG & More"**)
2. Đặt tên icon (nếu chưa đặt)
3. Click **"Download"** để tải về

### Bước 4: Cập nhật font files

1. Giải nén file vừa tải
2. Copy các file font vào thư mục `resources/fonts/icomoon-new/`:
    - `fonts/icomoon-new.eot` → `icomoon-new.eot`
    - `fonts/icomoon-new.svg` → `icomoon-new.svg`
    - `fonts/icomoon-new.ttf` → `icomoon-new.ttf`
    - `fonts/icomoon-new.woff` → `icomoon-new.woff`
3. Copy `selection.json` vào thư mục này (ghi đè file cũ)

### Bước 5: Cập nhật CSS

1. Mở file `style.css` trong package vừa tải
2. Tìm class của icon mới (ví dụ: `.icon-my-new-icon:before`)
3. Copy class đó và thêm vào 2 file CSS:
    - `resources/fonts/icomoon-new/style.css`
    - `packages/Webkul/Admin/src/Resources/assets/fonts/icomoon-new/style.css`

**Ví dụ:**

```css
.icon-my-new-icon:before {
    content: "\e900"; /* Unicode value từ IcoMoon */
}
```

### Bước 6: Rebuild assets

Chạy các lệnh sau để rebuild assets:

```bash
# Rebuild main assets
npm run build

# Rebuild Admin package assets
cd packages/Webkul/Admin
npm run build
```

### Bước 7: Clear cache và test

1. Hard refresh trình duyệt: `Ctrl+Shift+R` (Windows/Linux) hoặc `Cmd+Shift+R` (Mac)
2. Kiểm tra icon mới trong code:
    ```html
    <i class="icon-my-new-icon"></i>
    ```

## Lưu ý:

-   Luôn giữ 2 file CSS đồng bộ (resources và packages/Webkul/Admin)
-   Unicode value (`\e900`) phải khớp với giá trị trong font file
-   Sau khi thay đổi, luôn rebuild assets để thấy thay đổi
