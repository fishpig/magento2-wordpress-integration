<?php
/**
 *
 */
namespace FishPig\WordPress\X;

/**
 * This class handles displaying of usually protected content but only when the Authorisation Key is present
 */
class Previews
{
    /**
     *
     */
    public function __construct()
    {
        add_action(
            'init', 
            function() {
                // Create redirect for preview URLS
                if (isset($_GET['preview']) && !empty($_SERVER['REQUEST_URI'])) {
                    $previewId = $_GET['preview_id'] ?? ($_GET['p'] ?? null);

                    if ($previewId && get_post($previewId)->post_status !== 'publish') {
                        if (preg_match('/^.*(\/index.php\/)(.*)(\?.*)$/', $_SERVER['REQUEST_URI'], $m)) {
                            if ($m[2]) {
                                wp_redirect(get_site_url() . $m[1] . $m[3], 302, 'FishPig Preview');
                                exit; // phpcs:ignore
                            }
                        }
                    }
                }
            }
        );

        // This adds a query string parameter to ensure each preview isn't cached
        // As the URL will be different on each preview
        // The link is also signed so Magento will show unpublished posts.
        // Verified in Magento by FishPig\WordPress\Model\Post\PreviewSignature
        add_filter(
            'preview_post_link',
            function ($link, $post) {
                $link = add_query_arg('preview_time', time(), $link);

                if ($post && ($key = \FishPig\WordPress\X\AuthorisationKey::getKey())) {
                    $expires = time() + HOUR_IN_SECONDS;
                    $link = add_query_arg(
                        [
                            'fp_exp' => $expires,
                            'fp_sig' => hash_hmac('sha256', 'preview|' . (int)$post->ID . '|' . $expires, $key),
                        ],
                        $link
                    );
                }

                return $link;
            },
            10,
            2
        );

        // Magento sends unsigned preview requests here (the block editor builds
        // draft preview links itself so the filter above is not applied).
        // If the user can edit the post, redirect back with a signed preview link.
        add_action(
            'admin_post_fishpig_preview',
            function () {
                $postId = (int)($_GET['post'] ?? 0);

                if (!$postId || !current_user_can('edit_post', $postId) || !($link = get_preview_post_link($postId))) {
                    wp_die(esc_html__('Sorry, you are not allowed to preview this post.'), 403);
                }

                wp_safe_redirect($link);
                exit; // phpcs:ignore
            }
        );

        // Not logged in so send to the login page, then back here
        add_action('admin_post_nopriv_fishpig_preview', 'auth_redirect');

        if (\FishPig\WordPress\X\AuthorisationKey::isAuthorised()) {
            // All code here is only executed for AJAX requests from Magento
            // And will have the secret key present as a HTTP header

            // Force draft posts to appear as published
            add_filter(
                'get_post_status', 
                function($post_status, $post) {            
                    if (is_preview() && $post_status === 'draft') {
                        return 'publish';
                    }

                    return $post_status;
                },
                100,
                2
            );

            if (!empty($_GET['preview_id'])) {
                $_GET['preview_nonce'] = wp_create_nonce('post_preview_' . (int)$_GET['preview_id']);
            }

            // Display password protected post content
            // Password protection is implemented in Magento
            add_filter(
                'post_password_required',
                function($flag, $post) {
                    return false;
                },
                100,
                2
            );
        }
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento