import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

document.addEventListener('alpine:init', () => {
    Alpine.directive('datepicker', (el, { expression }, { evaluate }) => {
        const options = expression ? evaluate(expression) : {};
        
        const originalOnChange = options.onChange;
        options.onChange = function(selectedDates, dateStr, instance) {
            el.value = dateStr;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            if (typeof originalOnChange === 'function') {
                originalOnChange(selectedDates, dateStr, instance);
            } else if (Array.isArray(originalOnChange)) {
                originalOnChange.forEach(fn => fn(selectedDates, dateStr, instance));
            }
        };

        flatpickr(el, {
            dateFormat: "Y-m-d",
            ...options
        });
    });
});
