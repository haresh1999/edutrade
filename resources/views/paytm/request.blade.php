<form id="payment-form" action="{{ $actionUrl }}" method="post">
    @csrf
    <input type="hidden" name="order_id" value="{{ $input['order_id'] }}">
    <input type="hidden" name="amount" value="{{ $input['amount'] }}">
    <input type="hidden" name="payer_name" value="{{ $input['payer_name'] }}">
    <input type="hidden" name="payer_email" value="{{ $input['payer_email'] }}">
    <input type="hidden" name="payer_mobile" value="{{ $input['payer_mobile'] }}">
    <input type="hidden" name="user_id" value="{{ $userId }}">
    <input type="hidden" name="refresh_token" value="{{ $token }}">
    <p>Please wait while we redirecting you...</p>
</form>
<script>
    setTimeout(() => {
        document.getElementById('payment-form').submit();
    }, 1000);
</script>