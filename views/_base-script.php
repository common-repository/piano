<?php

namespace Piano;

/**
 * @var $piano Piano
 * @var $environment string
 * @var $endpoint_url string
 */

 $cxense_site_id = $piano->settings( Settings::CXENSE_SITE_ID );

?>

tp = window["tp"] || [];

<?php if ( WP_DEBUG ) { ?>
    tp.push(["setDebug", true]);
<?php } ?>

tp.push(["setAid", "<?php echo esc_attr( $piano->settings( Settings::AID ) ) ?>"]);
tp.push(["setEndpoint", "<?php echo esc_url( $endpoint_url ) ?>/api/v3"]);

<?php if ( $environment == ENVIRONMENT_SANDBOX ) { ?>
    tp.push(["setSandbox", true]);
<?php } ?> 

<?php if ( $cxense_site_id ) { ?>
    tp.push(["setCxenseSiteId", "<?php echo esc_attr( $cxense_site_id ) ?>"]);
<?php } ?>
