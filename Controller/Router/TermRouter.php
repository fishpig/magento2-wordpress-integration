<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Router;

class TermRouter implements \Magento\Framework\App\RouterInterface
{
    /**
     * @auto
     */
    protected $requestDispatcher = null;

    /**
     * @auto
     */
    protected $routerUrlHelper = null;

    /**
     * @auto
     */
    protected $taxonomyRepository = null;

    /**
     * @auto
     */
    protected $url = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Controller\Router\RequestDispatcher $requestDispatcher,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        \FishPig\WordPress\App\Url $url
    ) {
        $this->requestDispatcher = $requestDispatcher;
        $this->routerUrlHelper = $routerUrlHelper;
        $this->taxonomyRepository = $taxonomyRepository;
        $this->url = $url;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pathInfo = $this->routerUrlHelper->getRelativePathInfo($request);

        foreach ($this->taxonomyRepository->getAll() as $taxonomy) {
            if ($taxonomy->getSlug() && strpos($pathInfo, $taxonomy->getSlug() . '/') !== 0) {
                continue;
            }

            foreach ($taxonomy->getAllRoutes() as $termId => $route) {
                if ($pathInfo === $route) {
                    return $this->requestDispatcher->dispatch(
                        $request,
                        '*/term/view',
                        ['id' => $termId, 'taxonomy' => $taxonomy->getTaxonomy()]
                    );
                }
            }
        }

        // Now let's look for redirectable taxonomies
        foreach ($this->taxonomyRepository->getAll() as $taxonomy) {
            if ($taxonomy->getSlug() && strpos($pathInfo, $taxonomy->getSlug() . '/') !== 0) {
                continue;
            }

            if (($routes = $taxonomy->getResource()->getRedirectableUris($taxonomy, $pathInfo)) !== false) {
                foreach ($routes as $routeId => $route) {
                    if ($pathInfo === $route['source']) {
                        return $this->requestDispatcher->redirect(
                            $request,
                            $this->url->getHomeUrl($route['target'])
                        );
                    }
                }
            }
        }
        
        return false;
    }
}
