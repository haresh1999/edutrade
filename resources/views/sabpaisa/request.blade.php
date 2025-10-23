<form id="payment-form" action="{{ $input['url'] }}" method="post">
    <input type="hidden" name="encData" value="{{$input['enc_data']}}" />
    <input type="hidden" name="clientCode" value="{{$clientCode}}" />
    <p>Please wait while we redirecting you...</p>
</form>
<script>
    setTimeout(() => {
        document.getElementById('payment-form').submit();
    }, 1000);
</script>