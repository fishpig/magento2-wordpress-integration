<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class TermRepository extends \FishPig\WordPress\Model\Repository\ModelRepository
{
    /**
     * @auto
     */
    protected $objectFactory = null;

    /**
     *
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\Model\TermFactory $objectFactory,
        ?string $idFieldName = null
    ) {
        $this->objectFactory = $objectFactory;
        parent::__construct($storeManager, $idFieldName);
    }

    /**
     * @param  int $id
     * @param  array|string $taxonomies
     * @return FishPig\WordPress\Model\Term
     */
    public function getWithTaxonomy($id, $taxonomies)
    {
        $object = $this->get($id);

        if (!in_array($object->getTaxonomy(), (array)$taxonomies)) {
            // Shared terms: the same term_id can exist in multiple taxonomies
            foreach ((array)$taxonomies as $taxonomy) {
                $sharedTerm = $this->objectFactory->create()->setTaxonomy($taxonomy)->load((int)$id);

                if ($sharedTerm->getId()) {
                    return $sharedTerm;
                }
            }

            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'The WordPress term exists but failed the taxonomy check. ID=%1, taxonomy=%2',
                    $object->getId(),
                    $object->getTaxonomy()
                )
            );
        }

        return $object;
    }
}
