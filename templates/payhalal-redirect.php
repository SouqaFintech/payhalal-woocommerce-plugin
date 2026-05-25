<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<form id="payhalal-payment-form" method="post" action="<?php echo esc_url( $action_url ); ?>">
    <?php foreach ( $data as $key => $value ) : ?>
        <input
            type="hidden"
            name="<?php echo esc_attr( $key ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
        />
    <?php endforeach; ?>

    <div style="display:grid;align-items:center;margin:40px auto;text-align:center;">
        <p><?php esc_html_e( 'Redirecting to PayHalal. Please wait...', 'payhalal-for-woocommerce' ); ?></p>

        <button type="submit">
            <?php esc_html_e( 'Click here if you are not redirected automatically.', 'payhalal-for-woocommerce' ); ?>
        </button>
    </div>
</form>

<script>
    document.getElementById('payhalal-payment-form').submit();
</script>