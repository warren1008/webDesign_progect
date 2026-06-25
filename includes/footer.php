<?php
// AI 修改：電商級三欄 Footer，保留沙盒揭露與本機訂閱回饋。
?>
<footer class="site-footer commerce-footer" data-site-footer>
    <div class="site-footer__column footer-newsletter">
        <p class="eyebrow" data-en="News Letter" data-zh="智能快報">News Letter</p>
        <h2 data-en="Get topping alerts" data-zh="訂閱限定配料通知">Get topping alerts</h2>
        <form class="footer-subscribe" data-footer-subscribe>
            <label class="sr-only" for="footer-email">Email</label>
            <input id="footer-email" type="email" placeholder="輸入 E-mail 獲得最新消息" required>
            <button type="submit" class="btn btn-primary btn-small" data-en="Send" data-zh="送出">Send</button>
        </form>
        <a href="<?php echo htmlspecialchars(appPath('store-info.php')); ?>" data-en="Check service hours" data-zh="查看服務時段">Check service hours</a>
        <span>LINE Demo ID: @ramen-lab-demo</span>
        <span>support@staffless-ramen.test</span>
        <span data-en="24H monitor concept, sandbox support only." data-zh="24H 監控概念營運，僅提供模擬客服。">24H monitor concept, sandbox support only.</span>
    </div>

    <div class="site-footer__column">
        <p class="eyebrow" data-en="About The Future" data-zh="關於未來">About The Future</p>
        <a href="<?php echo htmlspecialchars(appPath('index.php#brand-story')); ?>" data-en="Brand story" data-zh="品牌故事">Brand story</a>
        <a href="<?php echo htmlspecialchars(appPath('store-info.php')); ?>" data-en="Smart kiosk" data-zh="智慧機台">Smart kiosk</a>
        <a href="<?php echo htmlspecialchars(appPath('locations.php')); ?>" data-en="Ramen Core HQ" data-zh="Ramen Core 研發總部">Ramen Core HQ</a>
        <a href="<?php echo htmlspecialchars(appPath('partners.php')); ?>" data-en="Partner district" data-zh="合作商圈">Partner district</a>
    </div>

    <div class="site-footer__column">
        <p class="eyebrow" data-en="Shopping Guide" data-zh="消費指南">Shopping Guide</p>
        <a href="<?php echo htmlspecialchars(appPath('checkout.php')); ?>" data-en="Sandbox payment" data-zh="Sandbox 支付">Sandbox payment</a>
        <a href="<?php echo htmlspecialchars(appPath('order-history.php')); ?>" data-en="Simulated support cases" data-zh="模擬客服案件">Simulated support cases</a>
        <a href="<?php echo htmlspecialchars(appPath('store-info.php')); ?>" data-en="24H monitoring and privacy" data-zh="24H 監控與隱私">24H monitoring and privacy</a>
        <a href="<?php echo htmlspecialchars(appPath('dashboard.php')); ?>" data-en="How to order" data-zh="如何點餐">How to order</a>
        <span data-en="Passwords are stored as server hashes. Do not reuse important passwords during testing."
              data-zh="密碼以伺服器雜湊保存；測試期間請勿重用重要網站密碼。">Passwords are stored as server hashes. Do not reuse important passwords during testing.</span>
    </div>

    <div class="site-footer__bottom">
        <div class="footer-socials" aria-label="Social links">
            <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                <svg class="social-icon social-icon-facebook" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                    <circle cx="24" cy="24" r="22" fill="#1877F2" />
                    <path fill="#FFFFFF" d="M28.2 16.5h3.2V11h-4.7c-5.2 0-8 3.1-8 8v3.5h-5v5.9h5V37h6.1v-8.6h5l.9-5.9h-5.9v-2.9c0-1.8.8-3.1 3.4-3.1z" />
                </svg>
            </a>
            <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <svg class="social-icon social-icon-instagram" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                    <defs>
                        <linearGradient id="instagramGradientFooter" x1="8" y1="42" x2="40" y2="6" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#F58529" />
                            <stop offset="0.32" stop-color="#DD2A7B" />
                            <stop offset="0.62" stop-color="#8134AF" />
                            <stop offset="1" stop-color="#515BD4" />
                        </linearGradient>
                    </defs>
                    <rect x="6" y="6" width="36" height="36" rx="11" fill="url(#instagramGradientFooter)" />
                    <circle cx="24" cy="24" r="8" fill="none" stroke="#FFFFFF" stroke-width="3.2" />
                    <circle cx="33.5" cy="14.8" r="2.4" fill="#FFFFFF" />
                </svg>
            </a>
            <a href="https://line.me/" target="_blank" rel="noopener noreferrer" aria-label="LINE">
                <svg class="social-icon social-icon-line" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                    <rect x="4" y="7" width="40" height="34" rx="17" fill="#06C755" />
                    <path fill="#FFFFFF" d="M15 18.2h2.8v8.2h4.2v2.4h-7V18.2zm8 0h2.8v10.6H23V18.2zm4.7 0h2.7l3.6 5.8v-5.8h2.7v10.6H34l-3.6-5.8v5.8h-2.7V18.2z" />
                    <path fill="#FFFFFF" d="M11.7 36.5c2.8-.8 5-1.9 6.8-3.4-5.7-1.2-9.9-5.1-9.9-9.8 0-5.7 6.9-10.3 15.4-10.3s15.4 4.6 15.4 10.3S32.5 33.6 24 33.6c-.9 0-1.8-.1-2.7-.2-2 1.6-5.1 3-9.6 3.1z" opacity="0.18" />
                </svg>
            </a>
        </div>
        <strong>©2026 Staffless Ramen Store. All Rights Reserved.</strong>
        <small data-en="This platform is an Alpha 2.0 simulated commercial testing environment."
               data-zh="本平台為 Alpha 2.0 模擬商用測試環境。">This platform is an Alpha 2.0 simulated commercial testing environment.</small>
    </div>
</footer>
<script defer src="<?php echo htmlspecialchars(appPath('assets/site-polish.js')); ?>"></script>
