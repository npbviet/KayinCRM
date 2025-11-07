#!/bin/bash

# Script để đồng bộ CSS icon giữa resources và Admin package
# Sử dụng: ./sync-css.sh

SOURCE_CSS="resources/fonts/icomoon-new/style.css"
TARGET_CSS="packages/Webkul/Admin/src/Resources/assets/fonts/icomoon-new/style.css"

# Kiểm tra file nguồn có tồn tại không
if [ ! -f "$SOURCE_CSS" ]; then
    echo "❌ Không tìm thấy file: $SOURCE_CSS"
    exit 1
fi

# Đọc phần icon classes từ file nguồn (từ dòng có .icon- đến hết file)
ICON_CLASSES=$(sed -n '/^\.icon-/,$p' "$SOURCE_CSS")

# Đọc phần @font-face và [class^="icon-"] từ file đích (giữ nguyên)
FONT_FACE=$(sed -n '/^@font-face/,/^\[class\^="icon-"\]/p' "$TARGET_CSS" | head -n -1)
ICON_SELECTOR=$(grep -A 10 '^\[class\^="icon-"\]' "$TARGET_CSS" | head -n 12)

# Tạo file CSS mới cho Admin package
cat > "$TARGET_CSS" << EOF
$FONT_FACE

$ICON_SELECTOR

$ICON_CLASSES
EOF

echo "✅ Đã đồng bộ CSS icon từ $SOURCE_CSS sang $TARGET_CSS"
echo "📝 Bạn cần rebuild assets để thấy thay đổi:"
echo "   npm run build"
echo "   cd packages/Webkul/Admin && npm run build"

