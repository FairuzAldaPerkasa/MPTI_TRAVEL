/**
 * MPTI TRAVEL - PAYMENT METHODS LOADER
 * Script untuk memuat metode pembayaran dinamis dari API
 * Version 1.0
 */

console.log('💳 Payment methods loader starting...');

let paymentMethods = [];

// Load payment methods on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPaymentMethods();
});

async function loadPaymentMethods() {
    console.log('💳 Loading payment methods...');
    try {
        const response = await fetch('../../BackEnd/get_payment_methods.php');
        const result = await response.json();
        
        if (result.success) {
            paymentMethods = result.data;
            console.log('✅ Payment methods loaded:', paymentMethods);
            updatePaymentMethodsDisplay();
        } else {
            console.warn('⚠️ Failed to load payment methods, using defaults');
            setDefaultPaymentMethods();
            updatePaymentMethodsDisplay();
        }
    } catch (error) {
        console.error('❌ Error loading payment methods:', error);
        setDefaultPaymentMethods();
        updatePaymentMethodsDisplay();
    }
}

function setDefaultPaymentMethods() {
    paymentMethods = [
        { name: 'VISA', type: 'card', icon: 'fab fa-cc-visa' },
        { name: 'Mastercard', type: 'card', icon: 'fab fa-cc-mastercard' },
        { name: 'BCA', type: 'bank', icon: 'fas fa-university' },
        { name: 'Mandiri', type: 'bank', icon: 'fas fa-university' },
        { name: 'GoPay', type: 'ewallet', icon: 'fas fa-mobile-alt' },
        { name: 'OVO', type: 'ewallet', icon: 'fas fa-wallet' }
    ];
}

function updatePaymentMethodsDisplay() {
    console.log('🔄 Updating payment methods display...');
    
    // Update payment methods section in footer
    const paymentContainer = document.querySelector('.payment-methods');
    if (paymentContainer) {
        updateFooterPaymentMethods(paymentContainer);
    }
    
    // Update payment methods in package detail page
    const packagePaymentSection = document.querySelector('.package-payment-methods');
    if (packagePaymentSection) {
        updatePackagePaymentMethods(packagePaymentSection);
    }
    
    console.log('✅ Payment methods display updated');
}

function updateFooterPaymentMethods(container) {
    // Clear existing content
    container.innerHTML = '';
    
    // Add payment methods
    paymentMethods.forEach(method => {
        const methodElement = document.createElement('div');
        methodElement.className = 'payment-item';
        methodElement.innerHTML = `
            <i class="${method.icon}"></i>
            <span>${method.name}</span>
        `;
        container.appendChild(methodElement);
    });
}

function updatePackagePaymentMethods(container) {
    // Clear existing content
    container.innerHTML = '';
    
    // Group payment methods by type
    const groupedMethods = {};
    paymentMethods.forEach(method => {
        if (!groupedMethods[method.type]) {
            groupedMethods[method.type] = [];
        }
        groupedMethods[method.type].push(method);
    });
    
    // Create sections for each type
    Object.keys(groupedMethods).forEach(type => {
        const typeSection = document.createElement('div');
        typeSection.className = `payment-type-${type}`;
        
        const typeTitle = document.createElement('h4');
        typeTitle.textContent = getPaymentTypeTitle(type);
        typeSection.appendChild(typeTitle);
        
        const methodsList = document.createElement('div');
        methodsList.className = 'payment-methods-list';
        
        groupedMethods[type].forEach(method => {
            const methodElement = document.createElement('div');
            methodElement.className = 'payment-method-item';
            methodElement.innerHTML = `
                <i class="${method.icon}"></i>
                <span>${method.name}</span>
            `;
            methodsList.appendChild(methodElement);
        });
        
        typeSection.appendChild(methodsList);
        container.appendChild(typeSection);
    });
}

function getPaymentTypeTitle(type) {
    const titles = {
        'bank': 'Transfer Bank',
        'card': 'Kartu Kredit',
        'ewallet': 'E-Wallet',
        'other': 'Metode Lain'
    };
    return titles[type] || 'Pembayaran';
}

// Global functions for other scripts to use
window.getPaymentMethods = function() {
    return paymentMethods;
};

window.refreshPaymentMethods = function() {
    loadPaymentMethods();
};

// CSS for payment methods display
const paymentMethodsCSS = `
.payment-methods {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.payment-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
}

.payment-item i {
    font-size: 16px;
    color: #6c757d;
}

.payment-type-bank .payment-item {
    background: #e3f2fd;
    border-color: #2196f3;
}

.payment-type-card .payment-item {
    background: #f3e5f5;
    border-color: #9c27b0;
}

.payment-type-ewallet .payment-item {
    background: #e8f5e8;
    border-color: #4caf50;
}

.payment-methods-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.payment-method-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

@media (max-width: 768px) {
    .payment-methods {
        justify-content: center;
    }
    
    .payment-item {
        flex: 1;
        min-width: 120px;
        justify-content: center;
    }
}
`;

// Inject CSS
const styleSheet = document.createElement('style');
styleSheet.textContent = paymentMethodsCSS;
document.head.appendChild(styleSheet);
