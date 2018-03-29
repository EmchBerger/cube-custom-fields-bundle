<?php
namespace CubeTools\CubeCustomFieldsBundle\EventListener;

use CubeTools\CubeCustomFieldsBundle\Utils\ConfigReader;
use CubeTools\CubeCustomFieldsBundle\Utils\CustomFieldRepoService;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Common\Util\ClassUtils;

class CustomFieldLinkedEntityListener
{
    public function __construct(ConfigReader $config, CustomFieldRepoService $cfRepo)
    {
        $this->config = $config;
        $this->cfRepo = $cfRepo;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // we are interested in updated entities which may be referenced through EntityCustomFields
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            // iterate over all entity types referenced in custom fields config
            foreach ($this->config->getLinkedEntities() as $linkedEntity) {
                if ($entity instanceof $linkedEntity['class']) {
                    // get all custom fields which reference the $entity
                    $affectedCustomFields = $this->cfRepo->getCustomFieldEntitiesForObject($linkedEntity['fieldId'], $entity);
                    // update the affected custom fields string representation and recompute the change-set instead of flushing, since we are already in flush process
                    foreach ($affectedCustomFields as $cf) {
                        $cf->storeStrRepresentationOnFlush($entity);
                        $em->persist($cf);
                        $cfMetadata = $em->getClassMetadata(ClassUtils::getClass($cf));
                        $uow->computeChangeSet($cfMetadata, $cf);
                    }
                }
            }
        }
    }
}
