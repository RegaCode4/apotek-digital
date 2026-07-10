import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

document.addEventListener('alpine:init', () => {
    Alpine.directive('datepicker', (el, { expression }, { evaluate }) => {
        const options = expression ? evaluate(expression) : {};
        flatpickr(el, {
            dateFormat: "Y-m-d",
            ...options
        });
    });
});
