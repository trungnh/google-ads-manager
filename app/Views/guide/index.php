<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Hướng dẫn sử dụng hệ thống</h4>
                </div>
                <div class="card-body">
                    <div class="accordion" id="guideAccordion">
                        
                        <!-- 1. Kết nối tài khoản Google Ads -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <strong>1. Kết nối tài khoản Google Ads</strong>
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#guideAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Truy cập menu <strong>Cài đặt Google Ads</strong>.</li>
                                        <li>Click vào nút <strong>Đăng nhập với Google</strong> để xác thực và cấp quyền cho hệ thống truy cập tài khoản Google Ads của bạn.</li>
                                        <li>Sau khi cấp quyền thành công, hệ thống sẽ lưu token của bạn để tự động lấy dữ liệu và tối ưu chiến dịch.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Đồng bộ và Quản lý tài khoản quảng cáo -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <strong>2. Đồng bộ và Quản lý tài khoản quảng cáo</strong>
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#guideAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Truy cập menu <strong>Đồng bộ tài khoản Ads</strong>.</li>
                                        <li>Bấm <strong>Đồng bộ</strong> để hệ thống kéo danh sách các tài khoản quảng cáo (hoặc MCC) mà email Google của bạn đang quản lý.</li>
                                        <li>Vào menu <strong>Danh sách tài khoản</strong> để xem các tài khoản đã đồng bộ. Tại đây, bạn có thể thiết lập các cấu hình tối ưu tự động cho từng tài khoản quảng cáo.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Cấu hình tối ưu tự động -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>3. Cấu hình tối ưu tự động (Tắt/Bật chiến dịch, Tăng ngân sách)</strong>
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#guideAccordion">
                                <div class="accordion-body">
                                    <p>Trong phần <strong>Danh sách tài khoản</strong>, bấm vào nút <strong>Cài đặt</strong> của tài khoản bạn muốn cấu hình:</p>
                                    <ul>
                                        <li><strong>Bật/tắt tự động tối ưu:</strong> Cho phép hệ thống chạy ngầm để kiểm tra và tối ưu tài khoản này.</li>
                                        <li><strong>Tự động Tắt/Bật chiến dịch:</strong> Hệ thống sẽ tự động tạm dừng chiến dịch nếu chạy kém hiệu quả (CPA cao, không có đơn) dựa trên ngưỡng bạn cài đặt.</li>
                                        <li><strong>Ngưỡng CPA/ROAS:</strong> Cài đặt chi phí tối đa trên mỗi đơn hàng (CPA) hoặc tỷ lệ lợi nhuận trên chi phí (ROAS).</li>
                                        <li><strong>Ngưỡng chi tiêu bắt đầu tối ưu:</strong> Hệ thống chỉ can thiệp khi chiến dịch đã tiêu vượt qua mức tiền này.</li>
                                        <li><strong>Tăng ngân sách:</strong> Tự động tăng ngân sách khi chiến dịch đang chạy tốt (nhiều đơn, CPA rẻ).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Cấu hình Google Sheets để đối soát đơn thực tế -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <strong>4. Cấu hình Google Sheets để đối soát đơn thực tế</strong>
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#guideAccordion">
                                <div class="accordion-body">
                                    <p>Để hệ thống tối ưu chuẩn xác, bạn có thể kết nối Google Sheets chứa dữ liệu đơn hàng thực tế:</p>
                                    <ul>
                                        <li>Trong trang <strong>Cài đặt</strong> của tài khoản quảng cáo, kéo xuống phần <strong>Google Sheets</strong>.</li>
                                        <li>Nhập URL của Google Sheet chứa dữ liệu đơn. (Nên cấu hình quyền "Anyone with the link can view").</li>
                                        <li>Thiết lập các cột tương ứng (Ví dụ: Cột Ngày là A, Cột SĐT là B, Cột Doanh thu là C, Cột Campaign ID là D).</li>
                                        <li>Hệ thống sẽ dựa vào Campaign ID và Số điện thoại để đếm số đơn thực tế (Real Conversions) và tính toán Real CPA/ROAS.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Tích hợp Pancake -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPancake">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePancake" aria-expanded="false" aria-controls="collapsePancake">
                                    <strong>5. Tích hợp Pancake</strong>
                                </button>
                            </h2>
                            <div id="collapsePancake" class="accordion-collapse collapse" aria-labelledby="headingPancake" data-bs-parent="#guideAccordion">
                                <div class="accordion-body">
                                    <p>Ngoài Google Sheets, hệ thống hỗ trợ lấy dữ liệu doanh thu trực tiếp từ Pancake:</p>
                                    <ul>
                                        <li>Vào phần cài đặt tài khoản quảng cáo, chọn tab/phần <strong>Tích hợp Pancake</strong>.</li>
                                        <li>Bật tính năng sử dụng Pancake.</li>
                                        <li>Nhập <strong>API Key</strong> của Pancake (Lấy trong phần cài đặt của Pancake).</li>
                                        <li>Nếu cần, bạn có thể lọc đơn theo <strong>Product ID</strong> hoặc loại trừ các đơn có chứa <strong>Tags</strong> nhất định.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Theo dõi Báo cáo và Lịch sử tối ưu -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    <strong>6. Theo dõi Báo cáo, Biểu đồ và Lịch sử tối ưu</strong>
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#guideAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li><strong>Quản lý chiến dịch:</strong> Xem danh sách toàn bộ chiến dịch đang chạy, xem các chỉ số chi phí, click, CPA thực tế,...</li>
                                        <li><strong>Biểu đồ:</strong> Click vào xem biểu đồ chi tiết của từng chiến dịch (Biểu đồ 5 phút hoặc 30 phút).</li>
                                        <li><strong>Báo cáo doanh thu:</strong> Xem tổng quan doanh thu, lợi nhuận, chi phí quảng cáo so với thực tế được cập nhật hàng ngày.</li>
                                        <li><strong>Lịch sử tối ưu:</strong> Truy cập menu <strong>Logs tối ưu</strong> để xem lịch sử hệ thống đã tự động Tắt/Bật chiến dịch nào, vào lúc mấy giờ và lý do tại sao.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- 7. Nhận thông báo qua Telegram -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSix">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                    <strong>7. Cấu hình nhận thông báo qua Telegram</strong>
                                </button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#guideAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Truy cập menu <strong>Cài đặt chung</strong> hoặc <strong>Cài đặt Telegram</strong>.</li>
                                        <li>Nhập <strong>Chat ID</strong> của Telegram (Bạn có thể chat với con Bot của hệ thống để lấy Chat ID).</li>
                                        <li>Mỗi khi hệ thống tự động tắt chiến dịch, tăng ngân sách, hoặc có báo cáo cuối ngày, tin nhắn sẽ được bắn thẳng về Telegram của bạn.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>