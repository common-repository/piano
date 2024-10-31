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

<script type="application/javascript">
    (function () {
        <?php require PIANO_PLUGIN_DIR . '/views/_base-script.php' ?>


        <?php if ( $user_provider == USER_PROVIDER_USERREF
                   && $piano->settings( Settings::USER_PROVIDER_INTEGRATION_ENABLED ) ) { ?>
            tp.push(["setUseTinypassAccounts", false ]);
            <?php if ( ! is_user_logged_in() ) { ?>
                tp.push( [ "addHandler", "loginRequired", function (params) {
                    (sessionStorage || localStorage).setItem('__pianoParams', JSON.stringify(params));
                    location.href = "<?php echo wp_login_url( $_SERVER['REQUEST_URI'] ) ?>";
                }]);
            <?php } else { ?>
                tp.push(["setUserRef", "<?php echo esc_attr( $piano->content()->get_user_token() ) ?>"]);
            <?php } ?>
        <?php } elseif ( $user_provider  == USER_PROVIDER_PIANO_ID ) { ?>
            tp.push(["setUseTinypassAccounts", false ]);
            tp.push(["setUsePianoIdUserProvider", true ]);
            <?php $piano_id_endpoint = $piano->settings()->get_endpoint( ENVIRONMENT_ENDPOINT_PIANO_ID ); ?>
            <?php if ( $piano_id_endpoint != null ) { ?>
                tp.push(["setPianoIdUrl", "<?php echo $piano_id_endpoint ?>"]);
            <?php } ?>
        <?php } ?>

        <?php if ( is_single() ) {
            $post = get_post();
            $author = get_userdata( $post->post_author );
            $tags = get_the_tags();

            if ( ! empty( $tags ) ) { ?>

                var tags = [];
                <?php foreach ( $tags as $tag ) { ?>
                    tags.push("<?php echo esc_attr( $tag->name ) ?>");
                <?php } ?>
                tp.push(["setTags", tags]);

            <?php } ?>

            tp.push(["setCustomVariable", "page_type", "post"]);

            tp.push(["setContentCreated", "<?php echo esc_attr( preg_replace( '/[\s ]+/', 'T', $post->post_date_gmt ) ) ?>"]);
            tp.push(["setContentAuthor", "<?php echo esc_attr( $author->display_name ) ?>"]);

        <?php } elseif ( is_page() ) { ?>
            tp.push(["setCustomVariable", "page_type", "page"]);
        <?php } ?>

        tp.push(["init", function () {
            <?php if ( $user_provider == USER_PROVIDER_USERREF
                   && $piano->settings( Settings::USER_PROVIDER_INTEGRATION_ENABLED ) ) { ?>
                if (tp.user.isUserValid()) {
                    var storage = sessionStorage || localStorage;
                    var params = storage.getItem('__pianoParams');
                    if (params) {
                        try {
                            params = JSON.parse(params);
                        } catch (e) {
                            params = false;
                        }

                        storage.removeItem('__pianoParams');

                        if (params) {
                            tp.offer.startCheckout(params);
                        }
                    }
                }
            <?php } elseif ( $user_provider == USER_PROVIDER_PIANO_ID) { ?>
                var buttons = document.getElementsByClassName("<?php echo PIANO_ID_BUTTON_CLASS ?>");

                tp.pianoId.init({
                    loggedIn: function () {
                        for (var i = 0; i < buttons.length; i++) {
                            buttons[i].classList.add('hide');
                        }
                    },
                    loggedOut: function () {
                        location.href = location.href;
                    }
                });

                if (!tp.user.isUserValid()){
                    for (var i = 0; i < buttons.length; i++) {
                        var button = buttons[i];
                        button.classList.remove('hide');
                        button.addEventListener("click", function (evt) {
                            evt.preventDefault();
                            tp.pianoId.show();
                        });
                    }
                }
            <?php } ?>

            tp.experience.init();

            <?php if ( $piano->content()->is_show_my_account() ) { ?>
                tp.myaccount.show({
                    containerSelector: '.<?php echo MY_ACCOUNT_CLASS ?>'
                });
            <?php } ?>
        }]);

        function onCheckoutClose(event) {
            if (event && event.state === "checkoutCompleted") {
                location.reload();
            }
        }

        function onExternalCheckoutComplete() {
            location.reload();
        }

        tp.push(["addHandler", "checkoutClose", onCheckoutClose]);
        tp.push(["addHandler", "externalCheckoutComplete", onExternalCheckoutComplete]);

        (function() {
            var a = document.createElement("script");
            a.type = "text/javascript";
            a.async = true;
            a.src = "//<?php echo $endpoint_host ?>/api/tinypass.min.js";
            var b = document.getElementsByTagName("script")[0];
            b.parentNode.insertBefore(a, b)
        })();
    })();
</script>
