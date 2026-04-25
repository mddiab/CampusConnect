(() => {
            const departmentSelect = document.getElementById('department_id');
            const categorySelect = document.getElementById('service_category_id');

            if (!departmentSelect || !categorySelect) {
                return;
            }

            const allCategoryOptions = Array.from(categorySelect.querySelectorAll('option[data-department-id]')).map((option) => ({
                value: option.value,
                label: option.textContent,
                departmentId: option.dataset.departmentId,
            }));

            const renderCategories = (selectedCategoryId = categorySelect.dataset.selected || '') => {
                const departmentId = departmentSelect.value;
                const categories = departmentId
                    ? allCategoryOptions.filter((category) => String(category.departmentId) === String(departmentId))
                    : [];

                const seenCategoryLabels = new Set();
                const uniqueCategories = categories.filter((category) => {
                    const key = category.label.trim().toLowerCase();

                    if (seenCategoryLabels.has(key)) {
                        return false;
                    }

                    seenCategoryLabels.add(key);
                    return true;
                });

                categorySelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = departmentId
                    ? 'Select the category that best matches the request'
                    : 'Select a department first';

                categorySelect.appendChild(placeholder);

                uniqueCategories.forEach((category) => {
                    const option = document.createElement('option');
                    option.value = String(category.value);
                    option.textContent = category.label;

                    if (String(category.value) === String(selectedCategoryId)) {
                        option.selected = true;
                    }

                    categorySelect.appendChild(option);
                });

                categorySelect.disabled = false;
            };

            departmentSelect.addEventListener('change', () => renderCategories(''));
            departmentSelect.addEventListener('input', () => renderCategories(''));

            renderCategories();
        })();
