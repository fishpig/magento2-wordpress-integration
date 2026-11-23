<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Post;

use FishPig\WordPress\App\HTTP\AuthorisationKey;

/**
 * Verifies preview links signed by the FishPig WordPress theme (see
 * wptheme/includes/previews.php.sample). The signature is an HMAC of the post
 * ID and expiry time, keyed with the shared auth key. The auth key rotates, so
 * the current and previous keys are both accepted.
 */
class PreviewSignature
{
    /**
     * @const string
     */
    const PARAM_EXPIRES = 'fp_exp';
    const PARAM_SIGNATURE = 'fp_sig';

    /**
     * @var \FishPig\WordPress\Model\OptionRepository
     */
    private $optionRepository;

    /**
     * @param \FishPig\WordPress\Model\OptionRepository $optionRepository
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param  int $postId
     * @param  \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function isValid(int $postId, \Magento\Framework\App\RequestInterface $request): bool
    {
        $expires = (int)$request->getParam(self::PARAM_EXPIRES);
        $signature = $request->getParam(self::PARAM_SIGNATURE);

        if ($postId <= 0 || !is_string($signature) || $signature === '' || $expires < time()) {
            return false;
        }

        foreach ([AuthorisationKey::KEY_OPTION_NAME, AuthorisationKey::PREVIOUS_KEY_OPTION_NAME] as $optionName) {
            $key = (string)$this->optionRepository->get($optionName);

            if ($key !== '' && hash_equals(hash_hmac('sha256', 'preview|' . $postId . '|' . $expires, $key), $signature)) {
                return true;
            }
        }

        return false;
    }
}
