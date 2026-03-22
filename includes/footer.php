
<footer class="footer" id="contact">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;text-align:left;margin-bottom:2rem;">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <div style="width:36px;height:36px;background:var(--red);border-radius:8px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-weight:600;font-size:13px;">SPD</span></div>
                    <div><strong>SPD Jobs Inc.</strong><br><span style="font-size:11px;">Bataan Branch</span></div>
                </div>
                <p style="font-size:13px;line-height:1.7;">Your trusted recruitment partner in the Hermosa Ecozone industrial zone, Bataan.</p>
            </div>
            <div>
                <p style="font-weight:600;color:#fff;margin-bottom:10px;">Contact Us</p>
                <p style="font-size:13px;line-height:2;">
                    📍 Manalo Village, Palihan<br>Hermosa, Bataan, Philippines<br>
                    📞 Globe: <?= COMPANY_GLOBE ?><br>
                    📞 Smart: <?= COMPANY_SMART ?><br>
                    📞 Sun: <?= COMPANY_SUN ?>
                </p>
            </div>
            <div>
                <p style="font-weight:600;color:#fff;margin-bottom:10px;">Quick Links</p>
                <p style="font-size:13px;line-height:2.2;">
                    <a href="<?= SITE_URL ?>/jobs.php" style="color:var(--gray-400);">Browse Jobs</a><br>
                    <a href="<?= SITE_URL ?>/register.php" style="color:var(--gray-400);">Create Account</a><br>
                    <a href="<?= SITE_URL ?>/login.php" style="color:var(--gray-400);">Applicant Login</a><br>
                    <a href="<?= SITE_URL ?>/admin/login.php" style="color:var(--gray-400);">Admin / HR Login</a>
                </p>
            </div>
            <div>
                <p style="font-weight:600;color:#fff;margin-bottom:10px;">Benefits We Offer</p>
                <p style="font-size:13px;line-height:2.2;">
                    ✔ SSS, PhilHealth, Pag-IBIG<br>
                    ✔ 13th Month Pay<br>
                    ✔ Overtime Pay<br>
                    ✔ Basic Training Provided<br>
                    ✔ Fresh Graduates Welcome
                </p>
            </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.1);padding-top:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <p style="font-size:12px;">© <?= date('Y') ?> <strong>SPD Jobs Inc.</strong> – Bataan Branch. All rights reserved.</p>
            <p style="font-size:12px;">Recruitment & Employee Status Monitoring System</p>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
