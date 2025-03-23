<?= $this->include('templates/header') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Kết nối với Google Ads</h4>
            </div>
            <div class="card-body">
                <p>Để sử dụng các tính năng quản lý Google Ads, bạn cần kết nối tài khoản Google của mình với ứng dụng.</p>
                
                <div class="text-center my-4">
                    <a href="<?= $googleOAuthUrl ?>" class="btn btn-danger btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google" viewBox="0 0 16 16">
                            <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                        </svg>
                        Kết nối với Google Ads
                    </a>
                </div>
                
                <div class="alert alert-info mt-4">
                    <h5>Lưu ý:</h5>
                    <ul>
                        <li>Bạn sẽ được chuyển hướng đến trang đăng nhập của Google.</li>
                        <li>Đảm bảo bạn đăng nhập bằng tài khoản có quyền truy cập vào Google Ads.</li>
                        <li>Sau khi cấp quyền, bạn sẽ được chuyển hướng trở lại ứng dụng.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>