<?php

namespace Piano;

$piano = \WP_Piano::piano();

$params = [
    'protocol_version' => '1',
    'aid' => esc_attr( $piano->settings( Settings::AID ) ),
    'reader_id' => 'READER_ID',
    'url' => 'SOURCE_URL',
    'referer' => 'DOCUMENT_REFERRER',
    '_' => 'RANDOM'
];

if ( is_single() ) {
    $post = get_post();
    $author = get_userdata( $post->post_author );

    $tags = get_the_tags();
    if ( ! empty( $tags ) ) {
        $tags_param = [];
        foreach ( $tags as $tag ) {
            $tags_param []= $tag->name;
        }

        $params[ 'tags' ] = implode( ',', $tags_param );
    }

    $params[ 'custom_variables' ] = json_encode([
        'page_type' => 'post'
    ]);

    $params[ 'content_created' ] = esc_attr( preg_replace( '/[\s ]+/', 'T', $post->post_date_gmt ) );
    $params[ 'content_author' ] = esc_attr( $author->display_name );
} elseif ( is_page() ) {
    $params[ 'custom_variables' ] = json_encode([
        'page_type' => 'page'
    ]);
}

$amp_access = [
    'authorization' => esc_url( $piano->settings()->get_endpoint( ENVIRONMENT_ENDPOINT_EXPERIENCE ) . '/xbuilder/experience/executeAmp' ) . '?' . http_build_query( $params ),
    'noPingback' => 'true',
    'login' => $piano->content()->amp_actions_url(),
    'authorizationFallbackResponse' => [
        'error' => true
    ]
];

?>

<script async custom-element="amp-access" src="https://cdn.ampproject.org/v0/amp-access-0.1.js"></script>
<script id="amp-access" type="application/json"><?php echo json_encode( $amp_access, JSON_PRETTY_PRINT ) ?></script>
