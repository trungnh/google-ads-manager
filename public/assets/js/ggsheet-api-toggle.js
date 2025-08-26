document.addEventListener('DOMContentLoaded', function() {
    const useGgsheetApiCheckbox = document.getElementById('use_ggsheet_api');
    const ggsheetApiFields = document.getElementById('ggsheet_api_fields');
    const csvFields = document.getElementById('csv_fields');
    const ggsheetIdInput = document.getElementById('ggsheet_id');
    const ggsheetNameInput = document.getElementById('ggsheet_name');
    const ggsheet2IdInput = document.getElementById('ggsheet2_id');
    const ggsheet2NameInput = document.getElementById('ggsheet2_name');

    // Hàm xử lý hiển thị/ẩn các trường
    function toggleFields() {
        if (useGgsheetApiCheckbox.checked) {
            ggsheetApiFields.style.display = 'block';
            csvFields.style.display = 'none';
            // Thêm thuộc tính required cho các trường bắt buộc
            ggsheetIdInput.setAttribute('required', 'required');
            ggsheetNameInput.setAttribute('required', 'required');
        } else {
            ggsheetApiFields.style.display = 'none';
            csvFields.style.display = 'block';
            // Xóa thuộc tính required
            ggsheetIdInput.removeAttribute('required');
            ggsheetNameInput.removeAttribute('required');
        }
    }

    // Gọi hàm khi trang được tải
    toggleFields();

    // Gọi hàm khi checkbox thay đổi
    useGgsheetApiCheckbox.addEventListener('change', toggleFields);
});