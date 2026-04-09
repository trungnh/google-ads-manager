<!-- app/Views/ads_accounts/index.php -->
<?= $this->include('templates/header') ?>

<style>
    .report-header {
        font-size: 1.1rem;
        font-weight: 600;
        /* color: #00F4FF; */
    }

    .config-table td {
        padding: 0.25rem 0;
        border: none;
    }

    .config-label {
        background-color: #4361ee;
        color: white;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 4px;
        width: 140px;
        display: inline-block;
        font-size: 0.85rem;
    }

    .config-input {
        background-color: transparent !important;
        border: 1px solid #3b3b54 !important;
        /* color: white !important; */
        padding: 6px 10px !important;
        width: 150px;
        border-radius: 4px;
    }

    .summary-box {
        display: flex;
        align-items: stretch;
        margin-bottom: 5px;
        height: 40px;
        border: 1px solid #ccc !important;
    }

    .summary-label {
        background-color: #006400;
        color: white;
        font-weight: bold;
        padding: 0 15px;
        display: flex;
        align-items: center;
        width: 120px;
        font-size: 0.85rem;
    }

    .summary-value {
        background-color: white;
        color: red;
        font-weight: bold;
        flex: 1;
        display: flex;
        align-items: center;
        padding: 0 15px;
        font-size: 0.95rem;
    }

    .summary-profit-val {
        background-color: #ffcc00;
        color: #b30000;
    }

    .editable-table th {
        background-color: #1a2235 !important;
        color: white !important;
        font-size: 0.75rem !important;
        text-align: center;
        padding: 10px 5px !important;
        border: 1px solid #2b3040 !important;
    }

    .editable-table td {
        background-color: #f1f5f9;
        font-size: 0.8rem;
        padding: 0 !important;
        border: 1px solid #e2e8f0 !important;
        text-align: center;
        vertical-align: middle;
        height: 35px;
    }

    /* Alternate row color */
    .editable-table tbody tr:nth-child(even) td {
        background-color: #ffffff;
    }

    .editable-input {
        width: 100%;
        height: 100%;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 600;
        color: #0056b3;
        outline: none;
        padding: 0;
    }

    .editable-input:focus {
        background-color: #e0f2fe;
    }

    .calc-val {
        color: #64748b;
    }

    .calc-val.positive {
        color: #16a34a;
    }

    .calc-val.negative {
        color: #dc2626;
    }
</style>

<div class="container-fluid py-4" style="min-height: 100vh;">

    <div class="card mb-4 shadow-sm" style="border-radius: 8px;">
        <div class="card-body bg-white p-4">

            <div class="mb-4">
                <span class="report-header">Sửa Báo cáo
                    <?= esc($report['name']) ?>
                </span>
            </div>

            <form id="reportForm" action="<?= base_url('revenue_reports/update/' . $report['id']) ?>" method="POST">

                <!-- TOP SECTIONS: CONFIG & SUMMARY -->
                <div class="row mb-4">
                    <!-- Config Inputs -->
                    <div class="col-md-5">
                        <table class="config-table">
                            <tr>
                                <td>
                                    <div class="config-label">Tỉ lệ hoàn</div>
                                </td>
                                <td><input type="number" step="0.0001" id="cfg_return_rate"
                                        class="config-input text-dark border-secondary"
                                        value="<?= esc((float) $report['return_rate']) ?>"></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="config-label">Giá nhập</div>
                                </td>
                                <td><input type="number" id="cfg_import_price"
                                        class="config-input text-dark border-secondary"
                                        value="<?= esc((float) $report['import_price']) ?>"></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="config-label">Phí ship</div>
                                </td>
                                <td><input type="number" id="cfg_shipping_fee"
                                        class="config-input text-dark border-secondary"
                                        value="<?= esc((float) $report['shipping_fee']) ?>"></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="config-label">Thuế thu nhập</div>
                                </td>
                                <td><input type="number" step="0.0001" id="cfg_income_tax"
                                        class="config-input text-dark border-secondary"
                                        value="<?= esc((float) $report['income_tax']) ?>"></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="config-label">Thuế ads</div>
                                </td>
                                <td><input type="number" step="0.0001" id="cfg_ads_tax"
                                        class="config-input text-dark border-secondary"
                                        value="<?= esc((float) $report['ads_tax']) ?>"></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="config-label">Phí thanh toán</div>
                                </td>
                                <td><input type="number" step="0.0001" id="cfg_payment_fee"
                                        class="config-input text-dark border-secondary"
                                        value="<?= esc((float) $report['payment_fee']) ?>"></td>
                            </tr>
                        </table>
                        <div class="mt-3">
                            <button type="button" id="btnSave" class="btn btn-success px-4 bg-gradient-success"><i
                                    class="fas fa-save me-1"></i> Lưu báo cáo</button>
                        </div>
                    </div>

                    <!-- Summary Stats -->
                    <div class="col-md-5 offset-md-1">
                        <div class="summary-box">
                            <div class="summary-label">Tiền hàng</div>
                            <div class="summary-value" id="sum_goods_cost">-</div>
                        </div>
                        <div class="summary-box">
                            <div class="summary-label">Tiền Ads</div>
                            <div class="summary-value" id="sum_ads_cost">-</div>
                        </div>
                        <div class="summary-box">
                            <div class="summary-label">Doanh thu</div>
                            <div class="summary-value" id="sum_revenue">-</div>
                        </div>
                        <div class="summary-box mt-3">
                            <div class="summary-label" style="background-color: #005000;">Lợi nhuận</div>
                            <div class="summary-value summary-profit-val" id="sum_profit">- (-%)</div>
                        </div>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-responsive"
                    style="background: white; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding-bottom: 20px;">
                    <table class="table editable-table" id="dailyTable" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 80px;">NGÀY</th>
                                <th style="width: 70px;">ĐƠN</th>
                                <th style="width: 70px;">SL</th>
                                <th>TIỀN HÀNG</th>
                                <th>TIỀN ADS</th>
                                <th>VẬN CHUYỂN</th>
                                <th>TIỀN HOÀN</th>
                                <th>TỔNG CHI</th>
                                <th>DOANH THU</th>
                                <th>LỢI NHUẬN</th>
                                <th style="width: 70px;">CPA</th>
                                <th style="width: 60px;">% ADS</th>
                                <th style="width: 60px;">ROAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dailyData as $r): ?>
                                <tr class="daily-row" data-date="<?= esc($r['date']) ?>">
                                    <td class="text-dark fw-bold">
                                        <?= date('Y-m-d', strtotime($r['date'])) ?>
                                    </td>

                                    <!-- Editables -->
                                    <td><input type="text" class="editable-input inp-orders"
                                            value="<?= esc((int) $r['orders']) ?>"></td>
                                    <td><input type="text" class="editable-input inp-quantity"
                                            value="<?= esc((int) $r['quantity']) ?>"></td>

                                    <td class="calc-val out-goods-cost">-</td>

                                    <td>
                                        <div class="d-flex justify-content-between align-items-center"
                                            style="height: 100%; padding: 0 10px;">
                                            <span class="calc-val out-ads-cost fw-bold w-100 text-end pe-2">-</span>
                                            <input type="hidden" class="inp-ads-cost"
                                                value="<?= esc((float) $r['ads_cost']) ?>">
                                            <button class="btn btn-outline-info btn-xs mb-0 btn-fetch-ads px-2 py-1"
                                                type="button" data-date="<?= esc($r['date']) ?>"
                                                data-report="<?= $report['id'] ?>" title="Cập nhật chi phí từ hệ thống">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="calc-val out-ship-cost">-</td>
                                    <td class="calc-val out-return-cost">-</td>
                                    <td class="calc-val out-total-cost">-</td>

                                    <td><input type="text" class="editable-input inp-revenue"
                                            value="<?= esc((float) $r['revenue']) ?>"></td>

                                    <td class="calc-val fw-bold out-profit">-</td>

                                    <td class="calc-val text-xs out-cpa">-</td>
                                    <td class="calc-val text-xs out-pct-ads">-</td>
                                    <td class="calc-val text-xs out-roas">-</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Utility format currency
    function fmt(val) {
        if (!val && val !== 0) return '-';
        return Intl.NumberFormat('en-US').format(Math.round(val));
    }
    // Utility parse raw numbers
    function parseNum(val) {
        if (typeof val === 'string') {
            val = val.replace(/,/g, '');
        }
        let parsed = parseFloat(val);
        return isNaN(parsed) ? 0 : parsed;
    }

    // Auto-calculate logic
    function calculateGrid() {
        // config variables
        let cfgReturnRate = parseNum($('#cfg_return_rate').val());
        let cfgImportPrice = parseNum($('#cfg_import_price').val());
        let cfgShippingFee = parseNum($('#cfg_shipping_fee').val());
        let cfgIncomeTax = parseNum($('#cfg_income_tax').val());
        let cfgAdsTax = parseNum($('#cfg_ads_tax').val());
        let cfgPaymentFee = parseNum($('#cfg_payment_fee').val());

        let sumGoods = 0;
        let sumAds = 0;
        let sumRevenue = 0;
        let sumProfit = 0;

        $('.daily-row').each(function () {
            let row = $(this);

            let orders = parseNum(row.find('.inp-orders').val());
            let qty = parseNum(row.find('.inp-quantity').val());
            let adsCost = parseNum(row.find('.inp-ads-cost').val());
            let revenue = parseNum(row.find('.inp-revenue').val());

            // Calc Basic
            let goodsCost = qty * cfgImportPrice;
            let shipCost = orders * cfgShippingFee;

            // Calc Tiền hoàn
            // ((doanh thu - giá nhập * SL) * tỉ lệ hoàn) + (số đơn * tỉ lệ hoàn * (phí ship/2))
            // Lưu ý definition của user: "doanh thu - giá nhập", hệ thống hiểu là giá nhập * SL (tiền hàng) mới đúng cho toàn bộ
            let returnCost = ((revenue - goodsCost) * cfgReturnRate) + (orders * cfgReturnRate * (cfgShippingFee / 2));
            if (returnCost < 0) returnCost = 0; // Guard against weird states

            // Calc Tổng chi
            let totalCost = goodsCost + shipCost + returnCost + adsCost + (adsCost * cfgAdsTax) + (adsCost * cfgPaymentFee) + (revenue * cfgIncomeTax);

            // Calc Profit
            let profit = revenue - totalCost;
            let profitPct = revenue > 0 ? (profit / revenue * 100) : 0;

            // Calc metrics
            let cpa = orders > 0 ? (adsCost / orders) : 0;
            let pctAds = revenue > 0 ? (adsCost / revenue * 100) : 0;
            let roas = adsCost > 0 ? (revenue / adsCost) : 0;

            // Output row values
            row.find('.out-ads-cost').text(fmt(adsCost));
            row.find('.out-goods-cost').text(fmt(goodsCost));
            row.find('.out-ship-cost').text(fmt(shipCost));
            row.find('.out-return-cost').text(fmt(returnCost));
            row.find('.out-total-cost').text(fmt(totalCost));

            let profitEl = row.find('.out-profit');
            profitEl.text(fmt(profit) + ' (' + profitPct.toFixed(1) + '%)');
            profitEl.removeClass('positive negative');
            if (profit > 0) profitEl.addClass('positive');
            else if (profit < 0) profitEl.addClass('negative');

            row.find('.out-cpa').text(fmt(cpa));
            row.find('.out-pct-ads').text(pctAds.toFixed(1) + '%');
            row.find('.out-roas').text(roas.toFixed(2));

            // Accumulate sum
            sumGoods += goodsCost;
            sumAds += adsCost;
            sumRevenue += revenue;
            sumProfit += profit;
        });

        // Output sums
        $('#sum_goods_cost').text(fmt(sumGoods));
        $('#sum_ads_cost').text(fmt(sumAds));
        $('#sum_revenue').text(fmt(sumRevenue));

        let sumProfitPct = sumRevenue > 0 ? (sumProfit / sumRevenue * 100) : 0;
        $('#sum_profit').html(fmt(sumProfit) + ' <span class="ms-1" style="color:black">(' + sumProfitPct.toFixed(2) + '%)</span>');
    }

    // Attach listeners
    $(document).ready(function () {
        // init
        calculateGrid();

        // format input on blur and calculate on input
        $('.config-input, .editable-input').on('input', function () {
            calculateGrid();
        });

        // AJAX Fetch Ads Cost function
        $('.btn-fetch-ads').click(function (e) {
            e.preventDefault();
            let btn = $(this);
            let row = btn.closest('.daily-row');
            let date = btn.data('date');
            let reportId = btn.data('report');
            let inputField = row.find('.inp-ads-cost');

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: '<?= base_url('revenue_reports/fetchAdsCost') ?>',
                type: 'POST',
                data: {
                    report_id: reportId,
                    date: date
                },
                success: function (resp) {
                    if (resp.success) {
                        inputField.val(resp.ads_cost);
                        calculateGrid(); // Trigger recalculation
                        btn.removeClass('btn-outline-info').addClass('btn-outline-success').html('<i class="fas fa-check"></i>');
                        setTimeout(() => {
                            btn.removeClass('btn-outline-success').addClass('btn-outline-info').html('<i class="fas fa-sync-alt"></i>');
                        }, 2000);
                    } else {
                        alert(resp.message || 'Lỗi lấy dữ liệu chi phí Ads.');
                        btn.removeClass('btn-outline-info').addClass('btn-outline-danger').html('<i class="fas fa-exclamation"></i>');
                        setTimeout(() => {
                            btn.removeClass('btn-outline-danger').addClass('btn-outline-info').html('<i class="fas fa-sync-alt"></i>');
                        }, 2000);
                    }
                },
                error: function () {
                    alert('Đã xảy ra lỗi mạng kết nối hệ thống.');
                    btn.removeClass('btn-outline-info').addClass('btn-outline-danger').html('<i class="fas fa-exclamation"></i>');
                    setTimeout(() => {
                        btn.removeClass('btn-outline-danger').addClass('btn-outline-info').html('<i class="fas fa-sync-alt"></i>');
                    }, 2000);
                },
                complete: function () {
                    btn.prop('disabled', false);
                }
            });
        });

        // save action
        $('#btnSave').click(function (e) {
            e.preventDefault();
            let btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');

            let dailyData = [];
            $('.daily-row').each(function () {
                let r = $(this);
                // We recalculate just to grab numerical values reliably
                let orders = parseNum(r.find('.inp-orders').val());
                let qty = parseNum(r.find('.inp-quantity').val());
                let adsCost = parseNum(r.find('.inp-ads-cost').val());
                let revenue = parseNum(r.find('.inp-revenue').val());

                let cfgImportPrice = parseNum($('#cfg_import_price').val());
                let cfgShippingFee = parseNum($('#cfg_shipping_fee').val());
                let cfgReturnRate = parseNum($('#cfg_return_rate').val());
                let cfgIncomeTax = parseNum($('#cfg_income_tax').val());
                let cfgAdsTax = parseNum($('#cfg_ads_tax').val());
                let cfgPaymentFee = parseNum($('#cfg_payment_fee').val());

                let goodsCost = qty * cfgImportPrice;
                let shipCost = orders * cfgShippingFee;
                let returnCost = ((revenue - goodsCost) * cfgReturnRate) + (orders * cfgReturnRate * (cfgShippingFee / 2));
                if (returnCost < 0) returnCost = 0;
                let totalCost = goodsCost + shipCost + returnCost + adsCost + (adsCost * cfgAdsTax) + (adsCost * cfgPaymentFee) + (revenue * cfgIncomeTax);
                let profit = revenue - totalCost;

                dailyData.push({
                    date: r.data('date'),
                    orders: orders,
                    quantity: qty,
                    ads_cost: adsCost,
                    revenue: revenue,
                    goods_cost: goodsCost,
                    ship_cost: shipCost,
                    return_cost: returnCost,
                    total_cost: totalCost,
                    profit: profit
                });
            });

            // Post data via ajax
            $.ajax({
                url: $('#reportForm').attr('action'),
                type: 'POST',
                data: {
                    return_rate: $('#cfg_return_rate').val(),
                    import_price: $('#cfg_import_price').val(),
                    shipping_fee: $('#cfg_shipping_fee').val(),
                    income_tax: $('#cfg_income_tax').val(),
                    ads_tax: $('#cfg_ads_tax').val(),
                    payment_fee: $('#cfg_payment_fee').val(),
                    daily_data: JSON.stringify(dailyData)
                },
                success: function (resp) {
                    btn.html('<i class="fas fa-check"></i> Đã Lưu');
                    setTimeout(() => {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Lưu báo cáo');
                    }, 2000);
                },
                error: function () {
                    alert('Lỗi khi lưu báo cáo');
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Lưu báo cáo');
                }
            });
        });
    });
</script>
<?= $this->include('templates/footer') ?>