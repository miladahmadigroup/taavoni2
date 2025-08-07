// Persian Date Picker Component
function createPersianDatePicker(containerId, inputName, defaultDate = null) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    // Get current Persian date
    const today = new Date();
    const currentYear = today.getFullYear() - 621;
    const currentMonth = today.getMonth() + 1;
    const currentDay = today.getDate();
    
    // Parse default date if provided
    let selectedYear = currentYear;
    let selectedMonth = currentMonth;
    let selectedDay = currentDay;
    
    if (defaultDate) {
        const parts = defaultDate.split('/');
        if (parts.length === 3) {
            selectedYear = parseInt(parts[0]);
            selectedMonth = parseInt(parts[1]);
            selectedDay = parseInt(parts[2]);
        }
    }
    
    // Create HTML structure
    container.innerHTML = `
        <div class="row">
            <div class="col-4">
                <label class="form-label">سال:</label>
                <select class="form-control" id="${inputName}_year" name="${inputName}_year" required>
                    ${generateYearOptions(selectedYear)}
                </select>
            </div>
            <div class="col-4">
                <label class="form-label">ماه:</label>
                <select class="form-control" id="${inputName}_month" name="${inputName}_month" required>
                    ${generateMonthOptions(selectedMonth)}
                </select>
            </div>
            <div class="col-4">
                <label class="form-label">روز:</label>
                <select class="form-control" id="${inputName}_day" name="${inputName}_day" required>
                    ${generateDayOptions(selectedDay)}
                </select>
            </div>
        </div>
        <input type="hidden" name="${inputName}" id="${inputName}_hidden" value="${selectedYear}/${selectedMonth.toString().padStart(2, '0')}/${selectedDay.toString().padStart(2, '0')}">
    `;
    
    // Add event listeners
    const yearSelect = document.getElementById(`${inputName}_year`);
    const monthSelect = document.getElementById(`${inputName}_month`);
    const daySelect = document.getElementById(`${inputName}_day`);
    const hiddenInput = document.getElementById(`${inputName}_hidden`);
    
    function updateHiddenInput() {
        const year = yearSelect.value;
        const month = monthSelect.value.padStart(2, '0');
        const day = daySelect.value.padStart(2, '0');
        hiddenInput.value = `${year}/${month}/${day}`;
    }
    
    function updateDayOptions() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);
        const maxDays = getMaxDaysInPersianMonth(year, month);
        const currentDay = parseInt(daySelect.value);
        
        daySelect.innerHTML = '';
        for (let i = 1; i <= maxDays; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = toPersianDigits(i);
            if (i === currentDay && i <= maxDays) {
                option.selected = true;
            }
            daySelect.appendChild(option);
        }
        
        if (currentDay > maxDays) {
            daySelect.value = maxDays;
        }
        
        updateHiddenInput();
    }
    
    yearSelect.addEventListener('change', updateDayOptions);
    monthSelect.addEventListener('change', updateDayOptions);
    daySelect.addEventListener('change', updateHiddenInput);
}

function generateYearOptions(selectedYear) {
    let options = '';
    const startYear = 1380;
    const endYear = 1420;
    
    for (let year = startYear; year <= endYear; year++) {
        const selected = year === selectedYear ? 'selected' : '';
        options += `<option value="${year}" ${selected}>${toPersianDigits(year)}</option>`;
    }
    return options;
}

function generateMonthOptions(selectedMonth) {
    const months = [
        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
    ];
    
    let options = '';
    for (let i = 1; i <= 12; i++) {
        const selected = i === selectedMonth ? 'selected' : '';
        options += `<option value="${i}" ${selected}>${months[i-1]}</option>`;
    }
    return options;
}

function generateDayOptions(selectedDay, maxDays = 31) {
    let options = '';
    for (let i = 1; i <= maxDays; i++) {
        const selected = i === selectedDay ? 'selected' : '';
        options += `<option value="${i}" ${selected}>${toPersianDigits(i)}</option>`;
    }
    return options;
}

function getMaxDaysInPersianMonth(year, month) {
    if (month <= 6) {
        return 31;
    } else if (month <= 11) {
        return 30;
    } else {
        // Check if leap year
        return isLeapYear(year) ? 30 : 29;
    }
}

function isLeapYear(year) {
    // Simple leap year calculation for Persian calendar
    const cycle = year % 128;
    const leapYears = [1, 5, 9, 13, 17, 22, 26, 30, 34, 38, 42, 46, 50, 55, 59, 63, 67, 71, 75, 79, 83, 88, 92, 96, 100, 104, 108, 112, 116, 121, 125];
    return leapYears.includes(cycle);
}

function toPersianDigits(num) {
    const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    let str = num.toString();
    for (let i = 0; i < english.length; i++) {
        str = str.replace(new RegExp(english[i], 'g'), persian[i]);
    }
    return str;
}

// Initialize date pickers when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize any date picker containers
    const datePickers = document.querySelectorAll('[data-date-picker]');
    datePickers.forEach(function(picker) {
        const inputName = picker.getAttribute('data-date-picker');
        const defaultDate = picker.getAttribute('data-default-date');
        createPersianDatePicker(picker.id, inputName, defaultDate);
    });
});