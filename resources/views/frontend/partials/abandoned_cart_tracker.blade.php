{{-- Real-time Abandoned Cart Checkout Telemetry Script --}}
<script>
    (function () {
        let syncTimeout = null;

        function captureCheckoutFields() {
            // চেকআউটের সাধারণ ইনপুট ফিল্ডগুলোর আইডি বা নেইম স্বয়ংক্রিয়ভাবে খুঁজে নেওয়া
            const emailInput = document.querySelector('input[type="email"], input[name="email"], input[name="customer_email"], input[name="shipping_email"]');
            const phoneInput = document.querySelector('input[name="phone"], input[name="customer_phone"], input[name="shipping_phone"], input[name="billing_phone"]');
            const nameInput  = document.querySelector('input[name="name"], input[name="customer_name"], input[name="shipping_name"]');

            const email = emailInput ? emailInput.value.trim() : '';
            const phone = phoneInput ? phoneInput.value.trim() : '';
            const name  = nameInput ? nameInput.value.trim() : '';

            // ফোন অন্তত ১১ ডিজিট অথবা সঠিক ইমেইল হলে ব্যাকগ্রাউন্ডে সিঙ্ক শুরু হবে
            const isEmailValid = email.includes('@') && email.includes('.');
            const isPhoneValid = phone.length >= 10;

            if (isEmailValid || isPhoneValid) {
                const payload = {
                    email: email,
                    phone: phone,
                    name: name,
                    _token: '{{ csrf_token() }}'
                };

                fetch("{{ url('cart/abandoned/sync') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    // সেশন সাকসেসফুলি ট্র্যাক হয়েছে
                    if (data.success && window.sessionStorage) {
                        sessionStorage.setItem('active_abandoned_cart_token', data.recovery_token);
                    }
                })
                .catch(err => {
                    // সাইলেন্ট ফেইল (ইউজারের চেকআউটে কোনো ব্যাঘাত ঘটবে না)
                    console.debug('Telemetry sync standby');
                });
            }
        }

        // কাস্টমার টাইপ করার সময় ১.৫ সেকেন্ড বিরতি দিলে সিঙ্ক কল হবে (Debounced)
        function queueSync() {
            clearTimeout(syncTimeout);
            syncTimeout = setTimeout(captureCheckoutFields, 1400);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const checkoutInputs = document.querySelectorAll(
                'input[name*="email"], input[name*="phone"], input[name*="name"], input[type="email"]'
            );

            checkoutInputs.forEach(input => {
                input.addEventListener('input', queueSync);
                input.addEventListener('change', captureCheckoutFields);
                input.addEventListener('blur', captureCheckoutFields);
            });
        });
    })();
</script>