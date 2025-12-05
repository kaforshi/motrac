import './bootstrap';
import '../css/app.css';

// Dark mode toggle
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            fetch('/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ dark_mode: isDark })
            });
        });
    }

    // Privacy mode toggle
    const privacyModeToggle = document.getElementById('privacyModeToggle');
    if (privacyModeToggle) {
        privacyModeToggle.addEventListener('click', function() {
            const isActive = this.classList.toggle('active');
            document.body.classList.toggle('privacy-mode');
            fetch('/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ privacy_mode: isActive })
            });
        });
    }

    // Calculator functionality
    const calculatorDisplay = document.getElementById('calculatorDisplay');
    const calculatorButtons = document.querySelectorAll('[data-calc]');
    let calculatorValue = '0';
    let calculatorExpression = '';

    if (calculatorDisplay && calculatorButtons.length > 0) {
        calculatorButtons.forEach(button => {
            button.addEventListener('click', function() {
                const value = this.getAttribute('data-calc');
                
                if (value === 'clear') {
                    calculatorValue = '0';
                    calculatorExpression = '';
                } else if (value === '=') {
                    try {
                        calculatorValue = eval(calculatorExpression).toString();
                        calculatorExpression = calculatorValue;
                    } catch (e) {
                        calculatorValue = 'Error';
                        calculatorExpression = '';
                    }
                } else if (value === 'backspace') {
                    calculatorExpression = calculatorExpression.slice(0, -1);
                    calculatorValue = calculatorExpression || '0';
                } else {
                    calculatorExpression += value;
                    calculatorValue = calculatorExpression;
                }
                
                calculatorDisplay.value = calculatorValue;
                const amountInput = document.getElementById('amount');
                if (amountInput && calculatorValue !== 'Error') {
                    amountInput.value = calculatorValue;
                }
            });
        });
    }
});




