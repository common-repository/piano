<?php

namespace Piano;

$piano = \WP_Piano::piano();

$environment = $piano->settings( Settings::ENVIRONMENT );

$endpoint = parse_url( $piano->settings()->get_endpoint() );
$endpoint_host = $endpoint['host'];
if ( isset( $endpoint[ 'port' ] ) ) {
    $endpoint_host .= ":{$endpoint['port']}";
}

$endpoint_url = "{$endpoint['scheme']}://{$endpoint_host}";
$user_provider = $piano->settings( Settings::USER_PROVIDER );

?>

<!DOCTYPE html>
<html>
    <head>
        <script>
            <?php require PIANO_PLUGIN_DIR . '/views/_base-script.php' ?>

            tp.push(["init", function() {
                tp.amp.logout();
            }]);

            (function() {
                var a = document.createElement("script");
                a.type = "text/javascript";
                a.async = true;
                a.src = "//<?php echo $endpoint_host ?>/api/tinypass.min.js";
                var b = document.getElementsByTagName("script")[0];
                b.parentNode.insertBefore(a, b)
            })();
        </script>
    </head>
    <body></body>
</html>