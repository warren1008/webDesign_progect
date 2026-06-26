// AI 修改：集中管理前台互動，讓期末 Demo 不需要額外套件也有操作回饋
document.addEventListener('DOMContentLoaded', () => {
    let currentCurrency = localStorage.getItem('staffless-currency') || 'USD';
    let usdToTwdRate = 31.606;
    let rateMetadata = null;

    const formatMoney = (value) => {
        const usdValue = Number(value || 0);
        if (currentCurrency === 'TWD') {
            return `NT$${Math.round(usdValue * usdToTwdRate).toLocaleString('zh-TW')}`;
        }
        return `$${usdValue.toFixed(2)}`;
    };
    window.stafflessFormatMoney = formatMoney;

    // AI 修改：全站繁中 / English 即時切換，並記住使用者偏好
    const zhTranslations = {
        'Staffless Instant Noodle Store': '無人泡麵商店',
        'Grab → Scan → Pay → Go!': '拿取 → 掃碼 → 付款 → 取餐！',
        'Home': '首頁',
        'How It Works': '運作流程',
        'Kiosk Flow': '自助流程',
        'AI Recommendation': 'AI 推薦',
        'Menu': '菜單',
        'Dashboard': '點餐台',
        'Admin': '管理後台',
        'Logout': '登出',
        'Login': '登入',
        'Register': '註冊',
        'Store': '商店首頁',
        'Orders': '訂單',
        'Profile': '個人資料',
        'Welcome to the Future of Noodle Shopping': '歡迎來到未來的泡麵購物方式',
        'A 24/7 staffless instant noodle store where customers scan shelf codes, pay online, and pick up with a secure code.': '全天候 24/7 無人泡麵商店，掃描貨架代碼、線上付款，再以安全取餐碼完成取餐。',
        'Grab your favorite instant noodles from our shelves': '從貨架拿取喜愛的泡麵',
        'Enter the noodle code and quantity on our website': '在網站輸入商品代碼與數量',
        'Pay online with your credit/debit card': '使用信用卡或金融卡線上付款',
        'Show your pickup code and enjoy your noodles!': '出示取餐碼，享用你的泡麵！',
        'Start Shopping →': '開始點餐 →',
        'View Kiosk Flow': '查看自助流程',
        'STAFFLESS FLOW': '無人商店流程',
        'From shelf to pickup code in four steps': '從貨架到取餐碼，只要四個步驟',
        'Scan shelf QR': '掃描貨架 QR Code',
        'Each noodle slot has a code and QR tag so customers can identify products without staff.': '每個泡麵貨位都有商品代碼與 QR 標籤，不需店員也能辨識商品。',
        'Add by code': '輸入代碼加入',
        'The website checks noodle code, quantity, price and stock before adding to cart.': '網站會先檢查商品代碼、數量、價格與庫存，再加入購物車。',
        'Pay online': '線上付款',
        'Checkout verifies payment details and records transaction status for the admin panel.': '結帳頁驗證付款資料，並將交易狀態記錄至管理後台。',
        'Pickup unlock': '解鎖取餐',
        'After payment, the order receives a pickup code that represents the unmanned pickup counter.': '付款後產生專屬取餐碼，供取餐櫃核對與放行。',
        'SELF-SERVICE FLOW': '自助服務流程',
        'Self-service kiosk preview': '自助機台預覽',
        'Choose a noodle below and watch the kiosk preview update. The same selection flow continues through login, cart and checkout.': '選擇下方泡麵即可查看機台即時更新，並可接續登入、購物車與結帳流程。',
        'Waiting for QR scan...': '等待掃描 QR Code...',
        'No noodle selected': '尚未選擇泡麵',
        'Tap a scan button to load a shelf item.': '點擊掃描按鈕，載入貨架商品。',
        'Popular Instant Noodles': '熱門泡麵菜單',
        'Load Code': '載入代碼',
        'Order This': '立即點餐',
        'In Stock': '庫存',
        'LIVE STOCK': '即時庫存',
        'AI TASTE MATCH': 'AI 口味配對',
        'Find your ramen personality': '找出你的拉麵人格',
        'Choose three preferences and let the recommendation engine find your best match.': '選擇三項偏好，讓推薦引擎找出最適合你的口味。',
        'Flavor style': '風味類型',
        'Spice level': '辣度',
        'Current mood': '現在的心情',
        'Rich and creamy': '濃郁滑順',
        'Classic and balanced': '經典均衡',
        'Sour and aromatic': '酸香開胃',
        'Dry noodle texture': '乾拌口感',
        'Mild': '微辣',
        'Medium': '中辣',
        'Hot': '重辣',
        'Need comfort': '想要療癒',
        'Need a quick meal': '想快速吃飽',
        'Need energy': '需要補充能量',
        'Want an adventure': '想嘗試新風味',
        'Treat myself': '想犒賞自己',
        'Generate My Match': '產生我的推薦',
        'Taste recommendations do not upload personal data.': '口味推薦不會上傳個人資料。',
        'AI advisor is ready': 'AI 顧問已就緒',
        'Order Recommendation': '點選推薦餐點',
        'Create an Account': '建立帳號',
        'Login to Your Account': '登入你的帳號',
        'Username': '使用者名稱',
        'Email': '電子郵件',
        'Password': '密碼',
        'Confirm Password': '確認密碼',
        'Username or Email': '使用者名稱或電子郵件',
        'Password (min 6 characters)': '密碼（至少 6 個字元）',
        "Don't have an account?": '還沒有帳號嗎？',
        'Register here': '立即註冊',
        'Already have an account?': '已經有帳號嗎？',
        'Login here': '前往登入',
        'Back to store introduction': '返回商店介紹',
        'Quick Login Accounts:': '快速登入帳號：',
        'Fill Member Account': '帶入一般會員',
        'Fill Admin Account': '帶入管理員',
        'Ordering progress': '點餐進度',
        'Code': '代碼',
        'Code:': '代碼：',
        'Enter noodle code': '輸入泡麵代碼',
        'Cart': '購物車',
        'Confirm quantity': '確認數量',
        'Pay': '付款',
        'Secure payment': '安全付款',
        'Pickup': '取餐',
        'Show pickup code': '出示取餐碼',
        'Enter Noodle Code': '輸入泡麵代碼',
        'Scan the shelf QR code or tap a quick code to load an item.': '掃描貨架 QR Code，或點選快速代碼載入商品。',
        'Add to Cart': '加入購物車',
        'Scanner ready': '掃描器已就緒',
        'Tap a noodle code below to load it into the kiosk.': '點選下方泡麵代碼載入機台。',
        'Available Noodle Codes:': '可用泡麵代碼：',
        'Choose add-ons': '選擇加料',
        'Select up to 6 items. Prices are recalculated by the server.': '最多選 6 項，加料價格會由後端重新計算。',
        'Estimated add-on subtotal': '加料小計',
        'Your Cart': '你的購物車',
        'Your cart is empty.': '購物車目前是空的。',
        'Enter a noodle code above to start shopping!': '輸入上方泡麵代碼開始購物！',
        'Name': '名稱',
        'Price': '價格',
        'Qty': '數量',
        'Quantity': '數量',
        'Subtotal': '小計',
        'Total:': '總計：',
        'Clear All': '全部清除',
        'View Full Cart': '查看完整購物車',
        'Proceed to Checkout →': '前往結帳 →',
        'Your Shopping Cart': '你的購物車',
        'Continue Shopping': '繼續購物',
        'Shopping progress': '購物進度',
        'Your cart is empty': '購物車目前是空的',
        "Looks like you haven't added any noodles yet.": '你還沒有加入任何泡麵。',
        'Browse Noodles': '瀏覽泡麵',
        'Product': '商品',
        'Remove': '移除',
        'Update Cart': '更新購物車',
        'Clear Cart': '清空購物車',
        'Proceed to Checkout': '前往結帳',
        'Checkout': '結帳',
        'Back to Cart': '返回購物車',
        'Checkout progress': '結帳進度',
        'Order Summary': '訂單摘要',
        'Confirm your items before the self-service shelf unlocks the pickup code.': '請確認商品內容，付款完成後系統將產生取餐碼。',
        'Payment Method': '付款方式',
        'Card Number': '卡號',
        'Expiry Date': '有效期限',
        'CVV': '安全碼',
        'Self-service kiosk waiting for secure payment...': '自助機台正在等待安全付款...',
        'Cancel & Back to Cart': '取消並返回購物車',
        'Use Saved Payment Details': '帶入常用付款資料',
        'ORDER SUCCESSFUL!': '訂單完成！',
        'Thank you for your purchase!': '感謝你的購買！',
        'Order Number:': '訂單編號：',
        'Pickup Code:': '取餐碼：',
        'Total Paid:': '付款總額：',
        'Date:': '日期：',
        'How to pick up your noodles:': '如何領取泡麵：',
        'Payment verified': '付款已驗證',
        'Locker unlocking': '取餐櫃解鎖中',
        'Ready for pickup': '可以取餐',
        'Digital receipt ready': '電子收據已備妥',
        'Scan at the unmanned pickup locker': '請在無人取餐櫃掃描',
        'EMAIL CONFIRMATION': '電子郵件確認',
        'Order confirmation is prepared for': '訂單確認信已為此信箱備妥：',
        'A digital receipt is prepared for this order.': '本訂單已建立電子收據。',
        'Order More Noodles': '繼續點餐',
        'View My Orders': '查看我的訂單',
        'My Order History': '我的訂單紀錄',
        'No Orders Yet': '尚無訂單',
        "You haven't placed any orders. Start shopping now!": '你還沒有建立訂單，現在就開始購物吧！',
        'Start Shopping': '開始購物',
        'Items:': '商品：',
        'Total Amount:': '總金額：',
        'Payment Status:': '付款狀態：',
        'Order Status:': '訂單狀態：',
        'My Profile': '我的個人資料',
        'Profile Information': '個人資料',
        'Profile Avatar': '會員頭像',
        'Admin Avatar': '管理員頭像',
        'Choose Avatar': '選擇頭像',
        'Update Avatar': '更新頭像',
        'Avatar updated successfully!': '頭像更新成功！',
        'Failed to update avatar.': '頭像更新失敗。',
        'Use a square JPG, PNG, WEBP or GIF under 2MB.': '建議使用正方形圖片，JPG、PNG、WEBP 或 GIF，2MB 以內。',
        'Email Address': '電子郵件',
        'Account Type': '帳號類型',
        'Member Since': '加入日期',
        'Update Profile': '更新資料',
        'Change Password': '變更密碼',
        'Current Password': '目前密碼',
        'New Password': '新密碼',
        'Confirm New Password': '確認新密碼'
    };

    // AI 修改：補齊前台、新功能頁與管理後台的繁體中文介面
    Object.assign(zhTranslations, {
        'Ramen Lab': '拉麵實驗室',
        'Build Custom Ramen': '打造客製拉麵',
        'Forgot password?': '忘記密碼？',
        'Track Kitchen & Locker': '追蹤製作與取餐櫃',
        'Kitchen & Locker Status': '製作與取餐櫃狀態',
        'Smart Analytics': '智慧營運分析',
        'Analytics': '營運分析',
        'Products': '商品管理',
        'Users': '使用者管理',
        'Payments': '付款紀錄',
        'Payment': '付款',
        'Action': '操作',
        'Actions': '操作',
        'Customer': '顧客',
        'Amount': '金額',
        'Status': '狀態',
        'Transaction ID': '交易編號',
        'Order #': '訂單編號',
        'Order #:': '訂單編號：',
        'Registered': '註冊日期',
        'Role': '角色',
        'Current Admin': '目前管理員',
        'Administrator': '管理員',
        'Brand': '品牌',
        'Description': '商品說明',
        'Stock': '庫存',
        'Stock Quantity': '庫存數量',
        'Price ($)': '價格（美元）',
        'Noodle Code': '拉麵代碼',
        'Edit': '編輯',
        'Delete': '刪除',
        'Cancel': '取消',
        'Pending': '待處理',
        'Confirmed': '已確認',
        'Preparing': '製作中',
        'Ready': '可取餐',
        'Completed': '已完成',
        'Cancelled': '已取消',
        'STORE INTELLIGENCE': '智慧商店分析',
        'Paid Orders': '已付款訂單',
        'Revenue': '營業額',
        'Average Order': '平均訂單金額',
        'Automation Score': '自動化評分',
        'DEMAND SIGNAL': '需求趨勢',
        'Product Performance': '商品銷售表現',
        'Live database': '即時資料庫',
        'ORDER PIPELINE': '訂單流程',
        'Status Distribution': '訂單狀態分布',
        'AI NOTE': 'AI 建議',
        'Stock healthy': '庫存充足',
        'No order data yet.': '目前尚無訂單資料。',
        'Prioritize products with strong sales and less than two demand cycles of stock.': '請優先補充銷量較高，且庫存低於兩個需求週期的商品。',
        'Complete a few orders to activate sales and inventory insights.': '完成幾筆訂單後，即可啟用銷售與庫存分析。',
        'Payment Transaction Logs': '付款交易紀錄',
        'Manage Customer Orders': '管理顧客訂單',
        'Manage Noodle Products': '管理拉麵商品',
        'All Noodle Products': '所有拉麵商品',
        'Manage Users': '管理使用者',
        'Admin Dashboard': '管理後台',
        'Admin Profile': '管理員資料',
        'Admin Information': '管理員資訊',
        'Edit Available Products': '編輯販售商品',
        "User's Booking Details": '顧客訂單資料',
        'Admin Details': '管理員資料',
        'Payment Logs': '付款紀錄',
        'User Management': '使用者管理',
        'Total Orders': '訂單總數',
        'Total Sales': '總營業額',
        'Total Users': '使用者總數',
        'Noodle Products': '拉麵商品數',
        'Pending Orders': '待處理訂單',
        'Low Stock Items': '低庫存商品',
        'Manage noodle inventory, add new products, update prices and stock levels': '管理拉麵庫存、新增商品，並更新價格與庫存數量。',
        'View and update all customer orders, change order status': '查看並更新所有顧客訂單與訂單狀態。',
        'Change password or admin profile settings': '修改管理員密碼與個人資料設定。',
        'View all transaction records and payment history': '查看所有交易與付款歷程。',
        'Manage registered users, view or delete accounts': '管理已註冊使用者，並查看或刪除帳號。',
        'Review product demand, revenue, stock risk and automated restock suggestions.': '查看商品需求、營業額、庫存風險與自動補貨建議。',
        'Manage Products': '管理商品',
        'View Orders': '查看訂單',
        'Edit Profile': '編輯資料',
        'View Payments': '查看付款紀錄',
        'Manage Users': '管理使用者',
        'Open Analytics': '開啟營運分析',
        'Account Type': '帳號類型',
        'Admin Information': '管理員資訊',
        'Role:': '角色：',
        'Username:': '使用者名稱：',
        'Email:': '電子郵件：',
        'Member Since:': '加入日期：',
        'Username cannot be changed': '使用者名稱不可變更',
        'Minimum 6 characters': '至少 6 個字元',
        'Base noodle': '基底拉麵',
        'Broth': '湯底',
        'Noodle texture': '麵體口感',
        'Bowl size': '份量',
        'Toppings': '配料',
        'Product code': '商品代碼',
        'Flavor intensity': '風味強度',
        'Estimated prep': '預估製作時間',
        'LIVE FLAVOR RENDER': '即時風味預覽',
        'SMART RAMEN LAB': '智慧拉麵實驗室',
        'Choose and tune every layer': '選擇並調整每一層風味',
        'The price and bowl preview update instantly.': '價格與拉麵預覽會即時更新。',
        'Add Custom Bowl': '加入客製拉麵',
        'View cart': '查看購物車',
        'Quick Order': '快速點餐',
        'Classic Shoyu': '經典醬油湯底',
        'Creamy Tonkotsu': '濃郁豚骨湯底',
        'Roasted Miso': '炙燒味噌湯底',
        'Tom Yum Citrus': '酸辣冬蔭功湯底',
        'Regular Noodles': '一般麵體',
        'Thin Noodles': '細麵',
        'Thick Noodles': '粗麵',
        'Regular Bowl': '一般份量',
        'Large Bowl': '加大份量',
        'No Spice': '不辣',
        'Fire Challenge': '極辣挑戰',
        'Ajitama Egg': '溏心蛋',
        'Sweet Corn': '甜玉米',
        'Roasted Seaweed': '烤海苔',
        'Chashu': '叉燒',
        'Scallion': '青蔥',
        'AUTOMATED KITCHEN': '自動化廚房',
        'Live Order Status': '即時製作狀態',
        'Order': '點餐',
        'My Orders': '我的訂單',
        'SMART PREPARATION': '智慧製作',
        'Your ramen is being prepared automatically': '您的拉麵正在自動製作',
        'The machine is checking temperature and portion accuracy.': '機台正在確認溫度與份量。',
        'Heating water': '加熱注水',
        'Flavor assembly': '組合風味',
        'Locker ready': '取餐櫃就緒',
        'PICKUP LOCKER': '無人取餐櫃',
        'LOCKER': '取餐櫃',
        'LOCKED': '已上鎖',
        'UNLOCKED': '已解鎖',
        'Pickup code': '取餐碼',
        'The locker unlocks automatically when preparation is complete.': '製作完成後，取餐櫃將自動解鎖。',
        'ACCOUNT RECOVERY': '帳號救援',
        'Forgot your password?': '忘記密碼？',
        'Enter your registered email. The reset link expires in 30 minutes.': '輸入註冊信箱，重設連結將在 30 分鐘後失效。',
        'Email address': '電子郵件',
        'Prepare Reset Link': '產生重設連結',
        'Secure reset link': '安全重設連結',
        'Use this secure link to continue resetting your password.': '請使用此安全連結繼續重設密碼。',
        'Open Secure Reset Page': '開啟安全重設頁',
        'Back to login': '返回登入',
        'SECURE RESET': '安全重設',
        'Create a new password': '設定新密碼',
        'Resetting password for': '正在重設帳號',
        'Confirm password': '確認新密碼',
        'Use at least 8 characters with letters and numbers.': '至少使用 8 個字元，並包含英文字母與數字。',
        'Update Password': '更新密碼',
        'Request Another Link': '重新申請連結',
        'This reset link is invalid, expired, or already used.': '此重設連結無效、已過期或已使用。',
        'Order Number:': '訂單編號：',
        'Pickup Code:': '取餐碼：',
        'Total Paid:': '付款總額：',
        'Date:': '日期：',
        'Go to the pickup counter': '前往無人取餐櫃',
        'Enter your pickup code on the screen:': '在螢幕輸入取餐碼：',
        'Wait for your order number to appear': '等待畫面顯示您的訂單編號',
        'Take your bag of noodles and enjoy!': '取出您的拉麵並享用！',
        'Item removed from cart': '商品已從購物車移除。',
        'Cart cleared': '購物車已清空。',
        'Payment failed. Please try again or update your cart.': '付款失敗，請重試或更新購物車。',
        'Your cart is empty. Add noodles before checkout.': '購物車是空的，請先加入拉麵再結帳。',
        'Please fill in all payment details': '請填寫完整付款資料。',
        'Failed to create order. Please try again.': '建立訂單失敗，請稍後再試。',
        'Payment failed. Please check your card details.': '付款失敗，請檢查卡片資料。',
        'The form expired. Please refresh and try again.': '表單已過期，請重新整理後再試。',
        'The selected noodle is unavailable.': '選擇的拉麵目前無法供應。',
        'The selected quantity exceeds the available stock.': '選擇的數量超過目前庫存。',
        'Your custom ramen was added to the cart.': '客製拉麵已加入購物車。',
        'Invalid username/email or password': '使用者名稱、電子郵件或密碼錯誤。',
        'Registration is unavailable while the database is offline.': '資料庫離線時無法註冊。',
        'Passwords do not match': '兩次輸入的密碼不一致。',
        'Passwords do not match.': '兩次輸入的密碼不一致。',
        'Password must be at least 6 characters': '密碼至少需要 6 個字元。',
        'Invalid email address': '電子郵件格式不正確。',
        'Username must be at least 3 characters': '使用者名稱至少需要 3 個字元。',
        'Registration successful! You can now login.': '註冊成功，現在可以登入。',
        'Username or email already exists': '使用者名稱或電子郵件已存在。',
        'Profile updated successfully!': '個人資料更新成功！',
        'Email already exists or invalid.': '電子郵件已存在或格式不正確。',
        'Password changed successfully!': '密碼變更成功！',
        'Failed to change password.': '密碼變更失敗。',
        'New password must be at least 6 characters and match confirmation.': '新密碼至少需要 6 個字元，且兩次輸入必須一致。',
        'Current password is incorrect.': '目前密碼不正確。',
        'Product added successfully!': '商品新增成功！',
        'Failed to add product. Code may already exist.': '商品新增失敗，商品代碼可能已存在。',
        'Product updated successfully!': '商品更新成功！',
        'Failed to update product.': '商品更新失敗。',
        'Product deleted successfully!': '商品刪除成功！',
        'Failed to delete product.': '商品刪除失敗。',
        'Order status updated successfully.': '訂單狀態更新成功。',
        'If the email exists, a password reset link has been prepared.': '若此電子郵件存在，系統已準備密碼重設連結。',
        'Password reset is unavailable while the database is offline.': '資料庫離線時無法重設密碼。',
        'Unable to prepare a reset link. Please try again.': '無法產生重設連結，請稍後再試。',
        'This reset link is invalid or has expired.': '此重設連結無效或已過期。',
        'Password updated. You can now log in.': '密碼更新成功，現在可以登入。',
        'Unable to update the password. Please try again.': '無法更新密碼，請稍後再試。',
        'Invalid or expired card date. Use a future MM/YY value.': '卡片有效期限無效或已過期，請輸入未來的月／年。',
        'Invalid card details. Visa starts with 4, Mastercard starts with 5, Amex starts with 3.': '卡片資料無效：Visa 以 4 開頭、Mastercard 以 5 開頭、Amex 以 3 開頭。',
        'Database connection is temporarily unavailable.': '資料庫暫時無法連線。'
    });

    // AI 修改：補齊後台表格與付款頁常見欄位的繁中顯示。
    Object.assign(zhTranslations, {
        'Card Type': '卡片類型',
        'Total': '總計',
        'Items': '商品數',
        'Date': '日期',
        'ID': '編號',
        'User': '使用者',
        'Admin': '管理員',
        'Add New Product': '新增商品',
        'Add Product': '新增商品',
        'Pickup Code': '取餐碼',
        'Paid': '已付款',
        'Success': '成功',
        'Failed': '失敗',
        'Manage Products →': '管理商品 →',
        'View Orders →': '查看訂單 →',
        'Edit Profile →': '編輯個人資料 →',
        'View Payments →': '查看付款紀錄 →',
        'Manage Users →': '管理使用者 →',
        'Update quantity': '更新數量',
        'Products:': '商品：',
        'Accepted card format:': '可接受卡號格式：',
        'Visa starts with 4, Mastercard starts with 5, Amex starts with 3': 'Visa 以 4 開頭、Mastercard 以 5 開頭、Amex 以 3 開頭',
        'Example:': '範例：',
        'Ordering progress': '點餐進度',
        'Shopping progress': '購物車流程',
        'Checkout progress': '結帳流程',
        'Order complete': '訂單完成',
        'QR code scanner': 'QR Code 掃描器',
        'Pickup QR token': '取餐 QR Code',
        'Language': '語言',
        'Currency': '幣別',
        'Reference exchange rate': '參考匯率'
    });

    const placeholderTranslations = {
        'Username': '使用者名稱',
        'Email': '電子郵件',
        'Username or Email': '使用者名稱或電子郵件',
        'Password': '密碼',
        'Confirm Password': '確認密碼',
        'Password (min 6 characters)': '密碼（至少 6 個字元）',
        'Example: N001': '例如：N001',
        'Qty': '數量',
        'MM/YY': '月/年',
        'Card Number': '卡號',
        'CVV': '安全碼',
        'you@example.com': 'you@example.com'
    };

    const titleTranslations = {
        'Staffless Instant Noodle Store': '無人拉麵商店',
        'Dashboard - Noodle Store': '點餐台－無人拉麵商店',
        'Cart - Noodle Store': '購物車－無人拉麵商店',
        'Checkout - Noodle Store': '結帳－無人拉麵商店',
        'Login - Noodle Store': '登入－無人拉麵商店',
        'Register - Noodle Store': '註冊－無人拉麵商店',
        'My Orders - Noodle Store': '我的訂單－無人拉麵商店',
        'Order Success - Noodle Store': '訂單完成－無人拉麵商店',
        'My Profile - Noodle Store': '我的個人資料－無人拉麵商店',
        'Ramen Lab - Noodle Store': '拉麵實驗室－無人拉麵商店',
        'Kitchen Status - Noodle Store': '製作狀態－無人拉麵商店',
        'Forgot Password - Noodle Store': '忘記密碼－無人拉麵商店',
        'Reset Password - Noodle Store': '重設密碼－無人拉麵商店',
        'Admin Dashboard - Noodle Store': '管理後台－無人拉麵商店',
        'Smart Analytics - Admin': '智慧營運分析－管理後台',
        'Manage Orders - Admin': '訂單管理－管理後台',
        'Manage Products - Admin': '商品管理－管理後台',
        'Manage Users - Admin': '使用者管理－管理後台',
        'Payment Logs - Admin': '付款紀錄－管理後台',
        'Admin Profile - Noodle Store': '管理員資料－無人拉麵商店'
    };
    const enTitleTranslations = {
        '活動優惠－無人拉麵商店': 'Promotions - Noodle Store',
        '點數兌換－無人拉麵商店': 'Rewards - Noodle Store',
        '合作店家－無人拉麵商店': 'Partners - Noodle Store',
        '門市狀態－無人拉麵商店': 'Store Status - Noodle Store',
        '會員中心－無人拉麵商店': 'Member Center - Noodle Store',
        '活動與會員管理－管理後台': 'Campaign & Member Management - Admin',
        '顧客回饋－管理後台': 'Customer Feedback - Admin'
    };

    // AI 修改：新功能頁以繁中為主要文案，切換 EN 時提供對應英文介面。
    const enTranslations = {
        '活動優惠中心': 'Promotion Center',
        '首頁': 'Home',
        '點餐台': 'Order Kiosk',
        '點數兌換': 'Rewards',
        '合作店家': 'Partners',
        '門市狀態': 'Store Status',
        '優惠會在購物車自動套用': 'Promotions are applied automatically',
        '不用輸入折扣碼，系統會依照商品與消費金額計算目前可使用的活動。': 'No coupon code needed. The system checks products and order value automatically.',
        '前往點餐': 'Start Ordering',
        '活動中': 'LIVE',
        '結帳自動套用': 'Applied automatically at checkout',
        '滿 NT$300 折 NT$30': 'Spend NT$300, save NT$30',
        '辛拉麵雙入優惠': 'Shin Ramyun Duo Deal',
        '火辣夜限定 9 折': 'Fire Night 10% Off',
        '結帳金額達 NT$300 自動折抵 NT$30': 'Save NT$30 automatically when the order reaches NT$300.',
        '購買兩份 N001 自動折抵 NT$25': 'Buy two N001 items and save NT$25 automatically.',
        'N004 火辣雞麵期間限定九折': 'Limited-time 10% discount on N004.',
        '點數兌換商城': 'Rewards Store',
        '會員中心': 'Member Center',
        '活動優惠': 'Promotions',
        '目前可用點數': 'Available Points',
        '每消費 NT$10 累積 1 點，10 點可折抵 NT$1。': 'Earn 1 point per NT$10. Redeem 10 points for NT$1.',
        '免費溏心蛋': 'Free Ajitama Egg',
        '合作店家茶飲券': 'Partner Tea Voucher',
        '限量霓虹拉麵碗': 'Limited Neon Ramen Bowl',
        '兌換後可於下一碗客製拉麵免費加蛋': 'Add a free egg to your next custom ramen.',
        '可至 NEON TEA 兌換限定無糖冷泡茶': 'Redeem a limited cold-brew tea at NEON TEA.',
        '會員限定收藏碗，限量 12 份': 'Member-exclusive collectible bowl, limited to 12.',
        '立即兌換': 'Redeem Now',
        '點數不足': 'Not Enough Points',
        '合作店家': 'Partner Stores',
        '取餐碼也是合作優惠通行證': 'Your pickup code unlocks partner offers',
        '完成拉麵訂單後，可在合作品牌出示取餐碼享用跨店優惠。': 'Show your pickup code at partner brands after ordering to unlock cross-store offers.',
        '營業中': 'OPEN',
        '即將打烊': 'CLOSING SOON',
        '休息中': 'CLOSED',
        '營業時間與設備狀態': 'Hours & System Status',
        '無人商店目前正常營業': 'The staffless store is open',
        '商店購物區全天開放；自動廚房與取餐櫃依服務時段運作。': 'Shopping is available 24/7. Kitchen and locker services follow their schedules.',
        '無人商店': 'Staffless Store',
        '自動化廚房': 'Automated Kitchen',
        '無人取餐櫃': 'Pickup Locker',
        '24 小時營業': 'OPEN 24 HOURS',
        '系統公告': 'SYSTEM NOTICE',
        '每日 03:30－03:45 進行貨架盤點': 'Shelf audit daily from 03:30 to 03:45',
        '盤點期間可瀏覽商品與會員中心，部分低庫存商品可能暫停加入購物車。': 'Browsing remains available during audits; low-stock products may be temporarily unavailable.',
        '會員點數中心': 'Member Points Center',
        '點數商城': 'Rewards Store',
        '個人資料': 'Profile',
        '距離下一等級還差': 'Points until next level:',
        '點數規則': 'Point Rules',
        '每消費 NT$10 累積 1 點': 'Earn 1 point per NT$10 spent',
        '10 點可於結帳折抵 NT$1': 'Redeem 10 points for NT$1 at checkout',
        '單筆訂單最多折抵商品金額 50%': 'Points can cover up to 50% of one order',
        '前往兌換商城': 'Open Rewards Store',
        '點數紀錄': 'Point History',
        '我的兌換券': 'My Reward Vouchers',
        '尚未兌換獎品。': 'No rewards redeemed yet.',
        '完成第一筆訂單後，點數紀錄會出現在這裡。': 'Point transactions will appear after your first order.',
        '主打無糖冷泡茶與氣泡飲的合作品牌': 'A partner specializing in cold-brew tea and sparkling drinks.',
        '提供飯糰、甜點與日用品的智慧便利店': 'A smart convenience store for rice balls, desserts and essentials.',
        '每日少量製作科技感麻糬甜點': 'A dessert studio producing limited daily mochi.',
        '拉麵訂單滿 NT$250，茶飲折 NT$20': 'Save NT$20 on tea with a NT$250 ramen order.',
        '會員出示取餐碼可獲限定甜點 9 折': 'Members receive 10% off selected desserts with a pickup code.',
        '兌換 150 點可獲聯名麻糬一份': 'Redeem 150 points for a collaboration mochi.',
        '客製拉麵與加熱服務': 'Custom ramen and heating service',
        '取餐碼解鎖與保溫服務': 'Pickup-code unlock and warming service',
        '貨架選購與線上結帳': 'Shelf shopping and online checkout',
        '活動與會員管理': 'Campaign & Member Management',
        '會員營運控制': 'Growth Control',
        '儀表板': 'Dashboard',
        '營運分析': 'Analytics',
        '商品': 'Products',
        '商品管理': 'Products',
        '訂單': 'Orders',
        '使用者': 'Users',
        '付款紀錄': 'Payments',
        '個人資料': 'Profile',
        '會員頭像': 'Profile Avatar',
        '管理員頭像': 'Admin Avatar',
        '選擇頭像': 'Choose Avatar',
        '更新頭像': 'Update Avatar',
        '頭像更新成功！': 'Avatar updated successfully!',
        '頭像更新失敗。': 'Failed to update avatar.',
        '建議使用正方形圖片，JPG、PNG、WEBP 或 GIF，2MB 以內。': 'Use a square JPG, PNG, WEBP or GIF under 2MB.',
        '圖片上傳失敗，請重新選擇檔案。': 'Image upload failed. Please choose the file again.',
        '圖片請小於 2MB。': 'Please keep the image under 2MB.',
        '圖片格式請使用 JPG、PNG、WEBP 或 GIF。': 'Please use JPG, PNG, WEBP or GIF.',
        '無法建立圖片資料夾。': 'Unable to create the image folder.',
        '圖片儲存失敗，請確認資料夾權限。': 'Unable to save the image. Please check folder permissions.',
        '顧客回饋': 'Feedback',
        '登出': 'Logout',
        '活動管理': 'Campaigns',
        '活動開關': 'Promotion Controls',
        '點數獎品': 'Rewards',
        '獎品庫存': 'Reward Inventory',
        '新增點數獎品': 'Add Reward',
        '中文名稱': 'Chinese Name',
        '英文名稱': 'English Name',
        '兌換點數': 'Points Required',
        '獎品類型': 'Reward Type',
        '加料': 'Topping',
        '優惠券': 'Coupon',
        '限量商品': 'Limited Item',
        '獎品圖片': 'Reward Image',
        '獎品說明': 'Description',
        '新增獎品': 'Create Reward',
        '新獎品已新增，前台點數商城會自動顯示。': 'Reward created. It will appear in the rewards store automatically.',
        '請完整填寫獎品名稱、英文名稱、說明與類型。': 'Please complete the reward names, description, and type.',
        '服務時間': 'Service Hours',
        '啟用中': 'ACTIVE',
        '已停用': 'INACTIVE',
        '合作中': 'ACTIVE',
        '已下架': 'HIDDEN',
        '儲存': 'Save',
        '點數': 'Points',
        '庫存': 'Stock',
        '活動自動折扣：': 'Automatic Promotion:',
        '優惠後預估：': 'Estimated Total:',
        '已套用優惠': 'Applied Promotions',
        '商品小計': 'Subtotal',
        '活動折扣': 'Promotion Discount',
        '會員點數折抵': 'Redeem Member Points',
        '套用點數': 'Apply Points',
        '實付金額': 'Amount Due',
        '優惠折抵：': 'Discount:',
        '本次獲得點數：': 'Points Earned:'
    };

    const translateDynamicEnglish = (text) => {
        const rules = [
            [/^剩餘\s+(\d+)\s+天\s+·\s+結帳自動套用$/, '$1 days left · applied automatically at checkout'],
            [/^剩餘\s+(\d+)$/, '$1 left'],
            [/^距離下一等級還差\s+(\d+)\s+點$/, '$1 points until the next level'],
            [/^可用\s+(\d+)\s+P$/, '$1 P available'],
            [/^本次預計獲得\s+(\d+)\s+P$/, 'Earn $1 P from this order']
        ];
        for (const [pattern, replacement] of rules) {
            if (pattern.test(text)) return text.replace(pattern, replacement);
        }
        return null;
    };

    const originalTextNodes = new WeakMap();
    const originalAttributes = new WeakMap();
    const originalValues = new WeakMap();
    const originalAriaLabels = new WeakMap();
    const originalUsdTextNodes = new WeakMap();
    const originalDocumentTitle = document.title;
    let currentLanguage = localStorage.getItem('staffless-language') || 'en';

    const translateDynamicText = (text) => {
        const monthNumbers = {
            Jan: 1, January: 1, Feb: 2, February: 2, Mar: 3, March: 3,
            Apr: 4, April: 4, May: 5, Jun: 6, June: 6, Jul: 7, July: 7,
            Aug: 8, August: 8, Sep: 9, September: 9, Oct: 10, October: 10,
            Nov: 11, November: 11, Dec: 12, December: 12
        };
        const englishDate = text.match(/^([A-Z][a-z]+)\s+(\d{1,2}),\s+(\d{4})(?:\s+(\d{1,2}):(\d{2})\s+(AM|PM))?$/);
        if (englishDate && monthNumbers[englishDate[1]]) {
            const [, month, day, year, hour, minute, period] = englishDate;
            const time = hour ? ` ${period === 'AM' ? '上午' : '下午'} ${hour}:${minute}` : '';
            return `${year} 年 ${monthNumbers[month]} 月 ${day} 日${time}`;
        }

        const shortEnglishDate = text.match(/^([A-Z][a-z]+)\s+(\d{1,2}),\s+(\d{1,2}):(\d{2})\s+(AM|PM)$/);
        if (shortEnglishDate && monthNumbers[shortEnglishDate[1]]) {
            const [, month, day, hour, minute, period] = shortEnglishDate;
            return `${monthNumbers[month]} 月 ${day} 日 ${period === 'AM' ? '上午' : '下午'} ${hour}:${minute}`;
        }

        const pricedOption = text.match(/^(.+?)(\s+\+\$[\d.]+)$/);
        if (pricedOption && zhTranslations[pricedOption[1]]) {
            return `${zhTranslations[pricedOption[1]]}${pricedOption[2]}`;
        }

        const rules = [
            [/^User:\s*(.+)$/, '使用者：$1'],
            [/^Admin:\s*(.+)$/, '管理員：$1'],
            [/^Welcome,\s*(.+)!$/, '歡迎，$1！'],
            [/^Cart \((\d+)\)$/, '購物車 ($1)'],
            [/^In Stock:\s*(\d+)$/, '庫存：$1'],
            [/^(\d+)\s+left$/, '剩餘 $1'],
            [/^(\d+)x\s+(.+)$/, '$1 份 $2'],
            [/^Pay \$(.+)$/, '付款 $$$1'],
            [/^Scan (N\d+)$/, '掃描 $1'],
            [/^(\d+)\s+sold\s+·\s+\$(.+)\s+revenue$/, '售出 $1 份 · 營業額 $$$2'],
            [/^(\d+)\s+in stock$/, '庫存 $1 份'],
            [/^Restock \+(\d+)$/, '建議補貨 +$1'],
            [/^(\d+)\s+item\(s\)$/, '$1 件商品'],
            [/^Added (\d+)x (.+) to cart!$/, '已將 $1 份 $2 加入購物車！'],
            [/^Insufficient stock for (.+)\. You can add (\d+) more\.$/, '$1 庫存不足，最多還可加入 $2 份。'],
            [/^Noodle code '(.+)' not found!$/, '找不到拉麵代碼「$1」！'],
            [/^Order confirmation is prepared for (.+)\.$/, '已為 $1 準備訂單確認資訊。'],
            [/^(\d+)\s+left$/, '剩餘 $1 份']
        ];
        for (const [pattern, replacement] of rules) {
            if (pattern.test(text)) return text.replace(pattern, replacement);
        }
        return null;
    };

    const applyLanguage = (language) => {
        currentLanguage = language;
        localStorage.setItem('staffless-language', language);
        document.documentElement.lang = language === 'zh-TW' ? 'zh-Hant' : 'en';
        document.title = language === 'zh-TW'
            ? (titleTranslations[originalDocumentTitle]
                || originalDocumentTitle
                    .replace('Staffless Instant Noodle Store', '無人拉麵商店')
                    .replace('Noodle Store', '無人拉麵商店'))
            : (enTitleTranslations[originalDocumentTitle] || originalDocumentTitle);

        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach((node) => {
            const parentTag = node.parentElement?.tagName;
            if (!node.parentElement || ['SCRIPT', 'STYLE'].includes(parentTag)) return;
            if (node.parentElement.closest('[data-en][data-zh]')) return;
            if (!originalTextNodes.has(node)) originalTextNodes.set(node, node.nodeValue);
            const original = originalTextNodes.get(node);

            if (language === 'en') {
                const trimmed = original.trim();
                const iconParts = trimmed.match(/^([^\p{L}\p{N}]*)(.*)$/u);
                const iconPrefix = iconParts?.[1] || '';
                const textWithoutIcon = iconParts?.[2] || trimmed;
                const translated = enTranslations[trimmed]
                    || (enTranslations[textWithoutIcon] ? `${iconPrefix}${enTranslations[textWithoutIcon]}` : null)
                    || translateDynamicEnglish(trimmed);
                if (translated) {
                    const leading = original.match(/^\s*/)?.[0] || '';
                    const trailing = original.match(/\s*$/)?.[0] || '';
                    node.nodeValue = `${leading}${translated}${trailing}`;
                } else {
                    node.nodeValue = original;
                }
                return;
            }

            const trimmed = original.trim();
            const iconParts = trimmed.match(/^([^\p{L}\p{N}]*)(.*)$/u);
            const iconPrefix = iconParts?.[1] || '';
            const textWithoutIcon = iconParts?.[2] || trimmed;
            const translatedWithoutIcon = translateDynamicText(textWithoutIcon);
            const translated = zhTranslations[trimmed]
                || (zhTranslations[textWithoutIcon] ? `${iconPrefix}${zhTranslations[textWithoutIcon]}` : null)
                || (translatedWithoutIcon ? `${iconPrefix}${translatedWithoutIcon}` : null)
                || translateDynamicText(trimmed);
            if (translated) {
                const leading = original.match(/^\s*/)?.[0] || '';
                const trailing = original.match(/\s*$/)?.[0] || '';
                node.nodeValue = `${leading}${translated}${trailing}`;
            }
        });

        document.querySelectorAll('[data-en][data-zh]').forEach((element) => {
            element.textContent = language === 'zh-TW' ? element.dataset.zh : element.dataset.en;
        });

        document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach((field) => {
            if (field.dataset.placeholderEn && field.dataset.placeholderZh) {
                field.placeholder = language === 'zh-TW'
                    ? field.dataset.placeholderZh
                    : field.dataset.placeholderEn;
                return;
            }
            if (!originalAttributes.has(field)) {
                originalAttributes.set(field, { placeholder: field.placeholder });
            }
            const original = originalAttributes.get(field).placeholder;
            field.placeholder = language === 'zh-TW'
                ? (placeholderTranslations[original] || original)
                : original;
        });

        // AI 修改：同步翻譯無障礙標籤與唯讀欄位，避免繁中介面殘留英文。
        document.querySelectorAll('[aria-label]').forEach((element) => {
            if (!originalAriaLabels.has(element)) {
                originalAriaLabels.set(element, element.getAttribute('aria-label'));
            }
            const original = originalAriaLabels.get(element);
            element.setAttribute(
                'aria-label',
                language === 'zh-TW'
                    ? (zhTranslations[original] || original)
                    : (enTranslations[original] || original)
            );
        });

        document.querySelectorAll('input[disabled][value], input[readonly][value]').forEach((field) => {
            if (!originalValues.has(field)) originalValues.set(field, field.value);
            const original = originalValues.get(field);
            field.value = language === 'zh-TW'
                ? (zhTranslations[original] || translateDynamicText(original) || original)
                : original;
        });

        document.querySelectorAll('[data-language-option]').forEach((button) => {
            const isActive = button.dataset.languageOption === language;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });

        refreshCurrencyDisplay();
        updateRateBadge();
        document.dispatchEvent(new CustomEvent('staffless:languagechange', { detail: { language } }));
    };

    // AI 修改：全站 USD / TWD 顯示切換，價格資料仍以 USD 儲存與結帳。
    const replaceUsdAmounts = (text) => text.replace(
        /\+?\$([\d,]+(?:\.\d+)?)/g,
        (match, amount, offset, fullText) => {
            if (fullText.slice(Math.max(0, offset - 2), offset) === 'NT') return match;
            const prefix = match.startsWith('+') ? '+' : '';
            return `${prefix}${formatMoney(Number(amount.replace(/,/g, '')))}`;
        }
    );

    const refreshCurrencyDisplay = () => {
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach((node) => {
            const parent = node.parentElement;
            if (!parent || ['SCRIPT', 'STYLE'].includes(parent.tagName)) return;
            if (parent.closest('.currency-widget')) return;

            if (currentCurrency === 'USD') {
                if (originalUsdTextNodes.has(node)) {
                    node.nodeValue = originalUsdTextNodes.get(node);
                }
                return;
            }

            if (node.nodeValue.includes('$') && !node.nodeValue.includes('NT$')) {
                originalUsdTextNodes.set(node, node.nodeValue);
                node.nodeValue = replaceUsdAmounts(node.nodeValue);
            }
        });
    };

    const updateRateBadge = () => {
        const badge = document.querySelector('[data-rate-display]');
        if (!badge) return;

        badge.textContent = `1 USD = NT$${usdToTwdRate.toFixed(3)}`;
        badge.classList.toggle('is-stale', Boolean(rateMetadata?.stale));
        const date = rateMetadata?.date || '2026-06-15';
        const source = rateMetadata?.source === 'Frankfurter' ? 'Frankfurter' : '備援匯率';
        badge.title = currentLanguage === 'zh-TW'
            ? `參考匯率｜${date}｜來源：${source}`
            : `Reference rate | ${date} | Source: ${source}`;
    };

    const applyCurrency = (currency) => {
        currentCurrency = currency === 'TWD' ? 'TWD' : 'USD';
        localStorage.setItem('staffless-currency', currentCurrency);

        // 先還原語言版本中的 USD 原始文字，再依最新匯率重新換算。
        applyLanguage(currentLanguage);
        document.querySelectorAll('[data-currency-option]').forEach((button) => {
            const isActive = button.dataset.currencyOption === currentCurrency;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });
        refreshCurrencyDisplay();
        updateRateBadge();
        document.dispatchEvent(new CustomEvent('staffless:currencychange', {
            detail: { currency: currentCurrency, rate: usdToTwdRate }
        }));
    };

    const languageSwitch = document.createElement('div');
    languageSwitch.className = 'language-switch';
    languageSwitch.setAttribute('role', 'group');
    languageSwitch.setAttribute('aria-label', 'Language');
    languageSwitch.innerHTML = `
        <button type="button" data-language-option="en" aria-pressed="false">EN</button>
        <button type="button" data-language-option="zh-TW" aria-pressed="false">繁中</button>
    `;

    const languageTarget = document.querySelector('header .nav-links, header nav');
    if (languageTarget) {
        languageTarget.appendChild(languageSwitch);
    } else {
        const authForm = document.querySelector('.auth-form');
        if (authForm) authForm.insertAdjacentElement('afterbegin', languageSwitch);
    }

    languageSwitch.querySelectorAll('[data-language-option]').forEach((button) => {
        button.addEventListener('click', () => applyLanguage(button.dataset.languageOption));
    });

    const currencyWidget = document.createElement('div');
    currencyWidget.className = 'currency-widget';
    currencyWidget.innerHTML = `
        <div class="currency-switch" role="group" aria-label="Currency">
            <button type="button" data-currency-option="USD" aria-pressed="false">USD</button>
            <button type="button" data-currency-option="TWD" aria-pressed="false">TWD</button>
        </div>
        <span class="exchange-rate" data-rate-display aria-label="Reference exchange rate">
            1 USD = NT$${usdToTwdRate.toFixed(3)}
        </span>
    `;
    languageSwitch.insertAdjacentElement('afterend', currencyWidget);

    currencyWidget.querySelectorAll('[data-currency-option]').forEach((button) => {
        button.addEventListener('click', () => applyCurrency(button.dataset.currencyOption));
    });

    applyLanguage(currentLanguage);
    applyCurrency(currentCurrency);

    const appScript = [...document.scripts].find((script) => /\/assets\/app\.js(?:\?|$)/.test(script.src));
    const exchangeRateUrl = appScript
        ? new URL('../exchange-rate.php', appScript.src)
        : new URL('exchange-rate.php', window.location.href);

    fetch(exchangeRateUrl, { headers: { Accept: 'application/json' } })
        .then((response) => {
            if (!response.ok) throw new Error('Exchange rate unavailable');
            return response.json();
        })
        .then((data) => {
            if (!data.stale) return data;

            // 本機 PHP 可能缺少外部 SSL 憑證，改由瀏覽器直接取得最新公開匯率。
            return fetch('https://api.frankfurter.dev/v2/rate/USD/TWD', {
                headers: { Accept: 'application/json' }
            })
                .then((response) => response.ok ? response.json() : data)
                .then((remote) => Number(remote.rate) > 0
                    ? {
                        ...data,
                        ...remote,
                        source: 'Frankfurter',
                        stale: false,
                        cached: false
                    }
                    : data)
                .catch(() => data);
        })
        .then((data) => {
            if (!Number.isFinite(Number(data.rate)) || Number(data.rate) <= 0) return;
            usdToTwdRate = Number(data.rate);
            rateMetadata = data;
            applyCurrency(currentCurrency);
        })
        .catch(() => {
            rateMetadata = { source: 'Fallback', date: '2026-06-15', stale: true };
            updateRateBadge();
        });

    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.querySelectorAll('header').forEach((header) => {
        const nav = header.querySelector('.nav-links, nav');
        if (!nav || header.querySelector('.nav-toggle')) return;

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'nav-toggle';
        toggle.setAttribute('aria-label', 'Toggle navigation');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.textContent = '☰';
        toggle.addEventListener('click', () => {
            const isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.textContent = isOpen ? '✕' : '☰';
        });
        header.appendChild(toggle);

        nav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.textContent = '☰';
            });
        });
    });

    document.querySelectorAll('[data-fill-demo-login]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.querySelector('[data-login-form]');
            if (!form) return;
            const isAdmin = button.dataset.fillDemoLogin === 'admin';
            form.querySelector('[data-login-username]').value = isAdmin ? 'admin' : 'john_doe';
            form.querySelector('[data-login-password]').value = isAdmin ? 'admin123' : 'user123';
        });
    });

    document.querySelectorAll('[data-demo-choice]').forEach((button) => {
        button.addEventListener('click', () => {
            const [code, name, desc] = button.dataset.demoChoice.split('|');
            const demo = document.querySelector('[data-kiosk-demo]');
            if (!demo) return;

            demo.querySelector('[data-demo-code]').textContent = code;
            demo.querySelector('[data-demo-name]').textContent = name;
            demo.querySelector('[data-demo-desc]').textContent = desc || 'Ready to add this noodle by code.';
            demo.querySelector('.kiosk-status').textContent = currentLanguage === 'zh-TW' ? 'QR Code 掃描成功' : 'QR scan accepted';
            demo.classList.add('is-scanned');
            setTimeout(() => demo.classList.remove('is-scanned'), 700);
        });
    });

    document.querySelectorAll('[data-noodle-code]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.querySelector('[data-noodle-code-input]');
            const screen = document.querySelector('[data-kiosk-screen]');
            if (!input) return;

            input.value = button.dataset.noodleCode;
            input.focus();

            if (screen) {
                const loaded = currentLanguage === 'zh-TW' ? '已載入' : 'loaded';
                const ready = currentLanguage === 'zh-TW' ? '可以加入購物車。' : 'is ready to add.';
                screen.innerHTML = `<span class="kiosk-dot"></span><strong>${button.dataset.noodleCode} ${loaded}</strong><p>${button.dataset.noodleName} ${ready}</p>`;
                screen.classList.add('is-ready');
            }
        });
    });

    const recommender = document.querySelector('[data-ai-recommender]');
    if (recommender) {
        const resultPanel = document.querySelector('[data-ai-result]');
        const catalog = [
            { code: 'N005', name: 'Maruchan Chicken', style: 'classic', spice: 'mild', mood: 'comfort', image: 'assets/images/noodles/N005-chicken.webp',
                reasonEn: 'Classic chicken broth is gentle, balanced and made for a comforting meal.',
                reasonZh: '經典雞湯風味溫和均衡，很適合需要療癒的一餐。' },
            { code: 'N006', name: 'Cup Noodles', style: 'light', spice: 'mild', mood: 'quick', image: 'assets/images/noodles/N006-cup.webp',
                reasonEn: 'A light, convenient cup is the fastest match when you want a simple meal.',
                reasonZh: '清爽便利的杯麵，最適合想快速享用簡單一餐的時刻。' },
            { code: 'N007', name: 'Sapporo Ichiban', style: 'classic', spice: 'mild', mood: 'comfort', image: 'assets/images/noodles/N007-shoyu.webp',
                reasonEn: 'Balanced shoyu flavor brings familiar Japanese comfort without heavy spice.',
                reasonZh: '均衡醬油風味帶來熟悉的日式療癒感，辣度也相當溫和。' },
            { code: 'N002', name: 'Indomie Mi Goreng', style: 'dry', spice: 'medium', mood: 'energy', image: 'assets/images/noodles/N002-mi-goreng.webp',
                reasonEn: 'Savory dry noodles and a medium kick deliver a lively energy boost.',
                reasonZh: '鹹香乾拌麵搭配中等辣度，能帶來充滿活力的滿足感。' },
            { code: 'N003', name: 'Mama Tom Yum', style: 'sour', spice: 'hot', mood: 'adventure', image: 'assets/images/noodles/N003-tom-yum.webp',
                reasonEn: 'Bright tom yum aroma and bold heat are ideal for an adventurous appetite.',
                reasonZh: '鮮明冬蔭功酸香與強烈辣度，最適合想冒險嘗鮮的你。' },
            { code: 'N008', name: 'Nissin Raoh', style: 'rich', spice: 'medium', mood: 'reward', image: 'assets/images/noodles/N008-tonkotsu.webp',
                reasonEn: 'Rich tonkotsu broth is a rewarding match for a premium ramen moment.',
                reasonZh: '濃厚豚骨湯頭很有犒賞感，適合享受高級拉麵時刻。' }
        ];
        let currentRecommendation = catalog[5];

        const renderRecommendation = (item, confidence = 96) => {
            currentRecommendation = item;
            resultPanel.dataset.recommendationCode = item.code;
            resultPanel.querySelector('[data-ai-result-image]').src = item.image;
            resultPanel.querySelector('[data-ai-result-image]').alt = item.name;
            resultPanel.querySelector('[data-ai-result-name]').textContent = item.name;
            resultPanel.querySelector('[data-ai-confidence]').textContent =
                currentLanguage === 'zh-TW' ? `${confidence}% 配對` : `${confidence}% MATCH`;
            resultPanel.querySelector('[data-ai-result-reason]').textContent =
                currentLanguage === 'zh-TW' ? item.reasonZh : item.reasonEn;
            resultPanel.querySelector('.ai-result-status').textContent =
                currentLanguage === 'zh-TW' ? `AI 推薦完成 · ${item.code}` : `AI match complete · ${item.code}`;
            const orderLink = resultPanel.querySelector('[data-ai-order-link]');
            if (orderLink) {
                const target = `dashboard.php?code=${encodeURIComponent(item.code)}`;
                orderLink.href = orderLink.dataset.authenticated === '1'
                    ? target
                    : `login.php?next=${encodeURIComponent(target)}`;
            }
            resultPanel.classList.add('is-matched');
            setTimeout(() => resultPanel.classList.remove('is-matched'), 700);
        };

        recommender.addEventListener('submit', (event) => {
            event.preventDefault();
            const preferences = new FormData(recommender);
            const result = catalog
                .map((item) => ({
                    item,
                    score: (item.style === preferences.get('taste') ? 5 : 0)
                        + (item.spice === preferences.get('spice') ? 3 : 0)
                        + (item.mood === preferences.get('mood') ? 4 : 0)
                }))
                .sort((a, b) => b.score - a.score)[0];

            resultPanel.querySelector('.ai-result-status').textContent =
                currentLanguage === 'zh-TW' ? 'AI 正在分析你的口味...' : 'AI is analyzing your taste...';
            setTimeout(() => renderRecommendation(result.item, Math.min(99, 82 + result.score)), 500);
        });

        document.addEventListener('staffless:languagechange', () => {
            renderRecommendation(currentRecommendation, Number(resultPanel.querySelector('[data-ai-confidence]').textContent.replace(/\D/g, '')) || 96);
        });
        renderRecommendation(currentRecommendation, 96);
    }

    document.querySelectorAll('[data-live-subtotal]').forEach((input) => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            const subtotal = row?.querySelector('[data-line-subtotal]');
            if (!subtotal) return;
            subtotal.textContent = formatMoney(Number(input.dataset.price) * Number(input.value || 0));

            // AI 修改：數量變動時同步更新購物車總額，不必先送出表單才看得到結果
            const total = [...document.querySelectorAll('[data-live-subtotal]')]
                .reduce((sum, item) => sum + (Number(item.dataset.price) * Number(item.value || 0)), 0);
            const totalNode = document.querySelector('[data-cart-total]');
            if (totalNode) totalNode.textContent = formatMoney(total);
        });
    });

    // AI 修改：dashboard 加料艙即時計算，送出時仍由 PHP 後端重新計價。
    const addonPanel = document.querySelector('[data-addon-panel]');
    if (addonPanel) {
        const maxAddons = 6;
        const checks = [...addonPanel.querySelectorAll('input[type="checkbox"][data-addon-price]')];
        const countNode = addonPanel.querySelector('[data-addon-count]');
        const totalNode = addonPanel.querySelector('[data-addon-total]');
        const warning = document.createElement('small');
        warning.className = 'addon-warning';
        addonPanel.appendChild(warning);

        const updateAddons = (changedInput = null) => {
            const selected = checks.filter((item) => item.checked);
            if (selected.length > maxAddons && changedInput) {
                changedInput.checked = false;
                warning.textContent = currentLanguage === 'zh-TW'
                    ? `最多只能選 ${maxAddons} 項加料。`
                    : `Choose up to ${maxAddons} add-ons.`;
            } else {
                warning.textContent = '';
            }

            const nextSelected = checks.filter((item) => item.checked);
            const total = nextSelected.reduce((sum, item) => sum + Number(item.dataset.addonPrice || 0), 0);
            if (countNode) countNode.textContent = currentLanguage === 'zh-TW' ? `已選 ${nextSelected.length}` : `Selected ${nextSelected.length}`;
            if (totalNode) totalNode.textContent = formatMoney(total);
            addonPanel.classList.toggle('has-selection', nextSelected.length > 0);
        };

        addonPanel.querySelectorAll('.addon-option').forEach((label) => {
            label.addEventListener('click', (event) => {
                event.preventDefault();
                const input = label.querySelector('input[type="checkbox"]');
                if (!input || input.disabled) return;
                input.checked = !input.checked;
                updateAddons(input);
            });
        });
        checks.forEach((input) => input.addEventListener('change', () => updateAddons(input)));
        document.addEventListener('staffless:languagechange', () => updateAddons());
        document.addEventListener('staffless:currencychange', () => updateAddons());
        updateAddons();
    }

    const paymentForm = document.querySelector('[data-payment-form]');
    if (paymentForm) {
        const cardNumber = paymentForm.querySelector('[data-card-number]');
        const expiry = paymentForm.querySelector('[data-card-expiry]');
        const cvv = paymentForm.querySelector('[data-card-cvv]');
        const preview = document.querySelector('[data-payment-preview]');

        const updatePreview = (message) => {
            if (preview) preview.querySelector('strong').textContent = message;
        };

        paymentForm.querySelectorAll('input[name="card_type"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                updatePreview(`${radio.value.toUpperCase()} reader selected`);
                if (cvv) cvv.maxLength = radio.value === 'amex' ? 4 : 3;
            });
        });

        cardNumber?.addEventListener('input', () => {
            const digits = cardNumber.value.replace(/\D/g, '').slice(0, 16);
            cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
        });

        expiry?.addEventListener('input', () => {
            const digits = expiry.value.replace(/\D/g, '').slice(0, 4);
            expiry.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
        });

        cvv?.addEventListener('input', () => {
            cvv.value = cvv.value.replace(/\D/g, '').slice(0, Number(cvv.maxLength || 4));
        });

        document.querySelector('[data-fill-demo-card]')?.addEventListener('click', () => {
            const selected = paymentForm.querySelector('input[name="card_type"]:checked') || paymentForm.querySelector('input[name="card_type"]');
            selected.checked = true;
            selected.dispatchEvent(new Event('change'));

            if (cardNumber) cardNumber.value = selected.dataset.demoCard.replace(/(.{4})/g, '$1 ').trim();
            if (expiry) expiry.value = '12/28';
            if (cvv) cvv.value = selected.value === 'amex' ? '1234' : '123';
            updatePreview('Payment details filled');
        });
    }

    const tracker = document.querySelector('[data-pickup-tracker]');
    if (tracker) {
        const stages = [...tracker.querySelectorAll('.pickup-stage')];
        stages.forEach((stage, index) => {
            setTimeout(() => {
                stages.forEach((item, itemIndex) => item.classList.toggle('is-active', itemIndex <= index));
            }, index * 900);
        });
    }

    const pickupQr = document.querySelector('[data-pickup-qr]');
    if (pickupQr) {
        // AI 修改：用取餐碼生成固定驗證矩陣，模擬無人取餐櫃掃描畫面
        const token = pickupQr.dataset.pickupQr || 'STAFFLESS';
        const size = 15;
        const finderCells = new Set();
        const markFinder = (offsetX, offsetY) => {
            for (let y = 0; y < 5; y += 1) {
                for (let x = 0; x < 5; x += 1) {
                    const edge = x === 0 || y === 0 || x === 4 || y === 4;
                    const center = x >= 2 && x <= 2 && y >= 2 && y <= 2;
                    if (edge || center) finderCells.add(`${offsetX + x}:${offsetY + y}`);
                }
            }
        };
        markFinder(0, 0);
        markFinder(size - 5, 0);
        markFinder(0, size - 5);

        let seed = [...token].reduce((sum, character, index) => sum + character.charCodeAt(0) * (index + 3), 0);
        for (let y = 0; y < size; y += 1) {
            for (let x = 0; x < size; x += 1) {
                seed = (seed * 9301 + 49297) % 233280;
                const cell = document.createElement('span');
                if (finderCells.has(`${x}:${y}`) || seed / 233280 > 0.52) {
                    cell.classList.add('is-dark');
                }
                pickupQr.appendChild(cell);
            }
        }
    }

    document.querySelectorAll('[data-flip-card]').forEach((card) => {
        card.addEventListener('click', (event) => {
            if (event.target.closest('a, button, input, select, textarea, label')) return;
            card.classList.toggle('is-flipped');
        });

        card.addEventListener('keydown', (event) => {
            if (!['Enter', ' '].includes(event.key)) return;
            if (event.target !== card) return;
            event.preventDefault();
            card.classList.toggle('is-flipped');
        });
    });

    const revealItems = document.querySelectorAll('.noodle-card, .timeline-item, .step, .order-card, .stat-card');
    if (revealItems.length && 'IntersectionObserver' in window) {
        revealItems.forEach((item) => item.classList.add('reveal-on-scroll'));
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        revealItems.forEach((item) => observer.observe(item));
    }

    const noodleCodeInput = document.querySelector('[data-noodle-code-input]');
    if (noodleCodeInput) {
        noodleCodeInput.addEventListener('input', () => {
            noodleCodeInput.value = noodleCodeInput.value.toUpperCase();
        });
    }
});

// AI 修改：載入逐步遷移的語系層與 Word 更新清單共用互動。
(() => {
    const current = document.currentScript;
    if (!current) return;
    const base = new URL('.', current.src);
    const i18n = document.createElement('script');
    i18n.src = new URL('i18n.js?v=20260626c', base).href;
    i18n.onload = () => {
        const innovations = document.createElement('script');
        innovations.src = new URL('innovations.js?v=20260626d', base).href;
        document.head.appendChild(innovations);
    };
    document.head.appendChild(i18n);
})();
