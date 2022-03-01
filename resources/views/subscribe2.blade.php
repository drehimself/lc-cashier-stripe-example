<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Subscribe 2
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form id="payment-form" method="POST" action="{{ route('subscribe2.post') }}">
                        @csrf
                        <div class="mt-4">
                            <input type="radio" name="plan" id="standard" value="price_1HmXIkHcC5z5MAw3pfNKs65q" checked>
                            <label for="standard">Standard - $10 / month</label> <br>

                            <input type="radio" name="plan" id="premium" value="price_1HmXIkHcC5z5MAw3DZfIg9IZ">
                            <label for="premium">Premium - $20 / month</label>
                        </div>
                        <div id="payment-element">
                            <!--Stripe.js injects the Payment Element-->
                        </div>
                        <button id="btnSubmit" class="bg-gray-900 text-white px-4 py-2 rounded">
                            <div class="spinner hidden" id="spinner"></div>
                            <span id="button-text">Pay now</span>
                        </button>
                        <div id="payment-message" class="hidden"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            const stripe = Stripe("pk_test_zmKNlnptONWFeIFjx9V6Ft2s");

            let elements;

            initialize();

            document
                .querySelector("#payment-form")
                .addEventListener("submit", handleSubmit);


            function initialize() {
                elements = stripe.elements({
                    clientSecret: "{{ $intent->client_secret }}"
                });

                const paymentElement = elements.create("payment");
                paymentElement.mount("#payment-element");
            }

            async function handleSubmit(e) {
                e.preventDefault();

                const {
                    setupIntent,
                    error
                } = await stripe.confirmSetup({
                    elements,
                    confirmParams: {
                        // Make sure to change this to your payment completion page
                        return_url: "http://localhost:4242/public/checkout.html",
                    },
                    redirect: 'if_required'
                });

                // This point will only be reached if there is an immediate error when
                // confirming the payment. Otherwise, your customer will be redirected to
                // your `return_url`. For some payment methods like iDEAL, your customer will
                // be redirected to an intermediate site first to authorize the payment, then
                // redirected to the `return_url`.

                if (error) {
                    if (error.type === "card_error" || error.type === "validation_error") {
                        showMessage(error.message);
                    } else {
                        showMessage("An unexpected error occured.");
                    }
                } else {
                    // console.log(setupIntent)
                    var form = document.getElementById('payment-form');
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'paymentMethod');
                    hiddenInput.setAttribute('value', setupIntent.payment_method);
                    form.appendChild(hiddenInput);

                    // Submit the form
                    form.submit();
                }

            }

            function showMessage(messageText) {
                const messageContainer = document.querySelector("#payment-message");

                messageContainer.classList.remove("hidden");
                messageContainer.textContent = messageText;

                setTimeout(function() {
                    messageContainer.classList.add("hidden");
                    messageText.textContent = "";
                }, 4000);
            }
        </script>
    @endpush
</x-app-layout>
