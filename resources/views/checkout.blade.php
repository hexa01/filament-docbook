<div class="container mx-auto py-10">
    <div class="max-w-lg mx-auto bg-white shadow-lg rounded-lg p-6">
        @if (session('success'))
            <div class="text-green-600 border border-green-600 rounded-lg text-center py-2 mb-4">
                Payment Successful!
            </div>
        @endif
        <form id="checkout-form" method="POST" action="{{ route('stripe.create-charge', ['payment' => $payment]) }}">
            @csrf
            <input type="hidden" name="stripeToken" id="stripe-token-id">
            <h2 class="text-2xl font-semibold mb-5 text-gray-800">Checkout Form</h2>
            <div id="card-element" class="border border-gray-300 rounded-lg p-3 mb-5 shadow-sm"></div>
            <button id="pay-btn" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 rounded" type="button" onclick="createToken()">
                PAY ${{ $payment->amount }}
            </button>
        </form>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('{{ env('STRIPE_KEY') }}');
    var elements = stripe.elements();
    var cardElement = elements.create('card');
    cardElement.mount('#card-element');

    function createToken() {
        document.getElementById("pay-btn").disabled = true;
        stripe.createToken(cardElement).then(function(result) {
            if (result.error) {
                document.getElementById("pay-btn").disabled = false;
                alert(result.error.message);
            }
            if (result.token) {
                document.getElementById("stripe-token-id").value = result.token.id;
                document.getElementById('checkout-form').submit();
            }
        });
    }
</script>
