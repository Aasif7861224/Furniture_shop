document.addEventListener('DOMContentLoaded', function () {
    var paymentMethod = document.getElementById('payment_method');

    if (paymentMethod) {
        var sections = document.querySelectorAll('[data-payment-section]');
        var togglePaymentSections = function () {
            for (var i = 0; i < sections.length; i++) {
                var section = sections[i];
                var shouldShow = section.getAttribute('data-payment-section') === paymentMethod.value;
                if (shouldShow) {
                    section.classList.add('is-visible');
                } else {
                    section.classList.remove('is-visible');
                }
            }
        };

        paymentMethod.addEventListener('change', togglePaymentSections);
        togglePaymentSections();
    }
});
