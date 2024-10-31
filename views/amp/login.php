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
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <script>
        <?php require PIANO_PLUGIN_DIR . '/views/_base-script.php' ?>

        <?php if ( $user_provider == USER_PROVIDER_USERREF ) { ?>
            tp.push(["setUseTinypassAccounts", false ]);
        <?php } elseif ( $user_provider  == USER_PROVIDER_PIANO_ID ) { ?>
            tp.push(["setUseTinypassAccounts", false ]);
            tp.push(["setUsePianoIdUserProvider", true ]);
            <?php $piano_id_endpoint = $piano->settings()->get_endpoint( ENVIRONMENT_ENDPOINT_PIANO_ID ); ?>
            <?php if ( $piano_id_endpoint != null ) { ?>
                tp.push(["setPianoIdUrl", "<?php echo $piano_id_endpoint ?>"]);
            <?php } ?>
        <?php } ?>

        tp.push(["init", function() {
            <?php if ( $user_provider == USER_PROVIDER_USERREF ) { ?>
                <?php if ( is_user_logged_in() ) { ?>
                    tp.amp.login({
                        userToken: "<?php echo esc_attr( $piano->content()->get_user_token() ) ?>",
                        userProvider: tp.user.getProvider().getName(),
                        loginSuccess: function() {
                            window.close();
                        },
                        loginFail: function() {
                            window.close();
                        }
                    });
                <?php } else { ?>
                    location.href = "<?php echo wp_login_url( $_SERVER['REQUEST_URI'] ) ?>";
                <?php } ?>
            <?php } elseif ( $user_provider  == USER_PROVIDER_PIANO_ID ) { ?>
                tp.pianoId.init();
                tp.amp.showLogin();
            <?php } ?>
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
    <style>
        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed,
        figure, figcaption, footer, header, hgroup,
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
        }
        body {
            line-height: 1;
            min-height: 100vh;
        }
    </style>
</head>
<body>
</body>
</html>
