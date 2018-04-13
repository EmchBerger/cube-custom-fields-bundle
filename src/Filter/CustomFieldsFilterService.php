<?php

namespace CubeTools\CubeCustomFieldsBundle\Filter;

use CubeTools\CubeCustomFieldsBundle\Utils\CustomFieldRepoService;
use Doctrine\ORM\QueryBuilder;

/**
 * Description of CustomFieldsFilterService
 *
 * @author markussc
 */
class CustomFieldsFilterService
{
    public function __construct(CustomFieldRepoService $repo)
    {
        $this->repo = $repo;
    }

    public function applyFilter($filterform, QueryBuilder $qb, $firstRootAlias = null)
    {
        if (!$firstRootAlias) {
                $firstRootAlias = $qb->getRootAliases()[0];
        }

        $entityClass = $qb->getRootEntities()[0];

        foreach ($filterform as $filterfield) {
            if ($filterfield->getConfig()->getOption('translation_domain') == 'custom_fields') {
                $filterVal = $filterfield->getData();
                if (!$filterVal || !count($filterVal)) {
                    // we are not interested in empty filters
                    continue;
                }
                $filterName = $filterfield->getName();
                $cfArr = array(); // the array which will contain the customField IDs to be filtered for
                if (is_array($filterVal) || $filterVal instanceof \ArrayAccess) {
                    // multi select filter field
                    foreach ($filterVal as $val) {
                        $cfArr = array_merge($cfArr, $this->repo->getCustomFieldEntitiesIdsForObject($filterName, $val));
                    }
                } else {
                    // single select filter field
                    $cfArr = $this->repo->getCustomFieldEntitiesIdsForObject($filterName, $filterVal);
                }
                if (count($cfArr)) {
                    // we found some relevant customField entities
                    // now we want to retrieve all entities which are linked with at least one of the found customFields (if any; otherwise, nothing shall be returned)
                    $this->repo->addWhereInIdsForCustomFieldIds($qb, $firstRootAlias . '.id', $entityClass, $cfArr);
                } else {
                    // no configField found or no entities matching the config field, so there's no need to continue
                    $qb->andWhere('TRUE = FALSE');
                    break;
                }
            }
        }
    }

    /**
     * 
     * @param type $filterform
     * @param type $ftFieldName The name of the fulltext filter field
     * @param QueryBuilder $qb
     * @param type $firstRootAlias
     */
    public function getFulltextFilterQueries($filterform, $ftFieldName, QueryBuilder $qb, $firstRootAlias = null)
    {
        if (!$firstRootAlias) {
                $firstRootAlias = $qb->getRootAliases()[0];
        }

        $entityClass = $qb->getRootEntities()[0];

        $fulltextCfQueries = array();
        $fulltextString = $filterform[$ftFieldName]->getData();
        foreach ($filterform as $filterfield) {
            if ($filterfield->getConfig()->getOption('translation_domain') == 'custom_fields') {
                $filterName = $filterfield->getName();

                // get the customField IDs to be filtered for (the ones for which the string representation match the fulltext filter value)
                $cfArr = $this->repo->getCustomFieldEntitiesIdsForString($filterName, $fulltextString);

                if (count($cfArr)) {
                    // we found some relevant customField entities
                    // now we want to retrieve all entities which are linked with at least one of the customFields
                    $inArrClause = array();
                    foreach ($cfArr as $cf) {
                        $relevantEntitiesIds = $this->repo->getEntitiesIdsForCustomFieldId($entityClass, $cf);
                        if (count($relevantEntitiesIds)) {
                            $inArrClause[] = $firstRootAlias . '.id IN (' . join(',', $relevantEntitiesIds) . ')';
                        }
                    }
                    $fulltextCfQueries = array_merge($fulltextCfQueries, $inArrClause);
                }
            }
        }
        return $fulltextCfQueries;
    }
}
