<?php

namespace CubeTools\CubeCustomFieldsBundle\Controller;

use CubeTools\CubeCustomFieldsBundle\Utils\ConfigReader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * CustomFields Ajax controller.
 *
 * @Route("/cube_custom_fields/ajax")
 */
class AjaxFieldController extends Controller
{
    /**
     * @Route("/", name="cube_custom_fields_ajax")
     * @Method("GET")
     */
    public function ajaxAction(Request $request, ConfigReader $configReader)
    {
        $em = $this->getDoctrine()->getManager();

        $term = $request->query->get('q');
        $limit = $request->query->get('page_limit');
        if ($request->query->get('page')) {
            $page = $request->query->get('page');
        } else {
            $page = 1;
        }
        $fieldId = $request->query->get('fieldId'); // specifies the field as configured in custom_fields.yml (currently, the fieldId MUST be unique!)
        // get configuration for this fieldId
        $fieldConfig = $configReader->getConfigForFieldId($fieldId);
        if (array_key_exists('filters', $fieldConfig)) {
            $filters = $fieldConfig['filters'];
        } else {
            $filters = array();
        }

        $class = $fieldConfig['field_options']['class']; // read from the configuration: parameter containing the class of the entities to be looked up

        if (is_subclass_of($class, \CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase::class)) {
            // we are querying an internal field
            $dbStorage = $class::getStorageFieldName();
        } else {
            if (isset($fieldConfig['field_options']['text_property'])) {
                $dbStorage = $fieldConfig['field_options']['text_property'];
            } else {
                $dbStorage = null;
            }
        }
        if ($dbStorage) {
            // we can create a query
            $qb = $em->createQueryBuilder()
                ->select('cf')
                ->from($class, 'cf');
            foreach ($filters as $field => $value) {
                $qb->andWhere(sprintf('cf.%s = :%s', $field, $field))
                    ->setParameter($field, $value)
                    // here we should add a switch based on the dbStorage field type
                    ->andWhere('cf.'.$dbStorage.' LIKE :term')
                    ->setParameter('term', '%'.$term.'%')
                    ->orderBy('cf.'.$dbStorage, 'asc');
            }
            $allRelevantEntities = $qb->getQuery()->getResult();
        } else {
            // we cannot create a query, but will compare the __toString value of all available entities
            $allFoundEntities = $em->getRepository($class)->findBy($filters); // we filter by the specific filters defined in the config for this field
            if ($term) {
                // we must limit the result set to the matching ones for the search $term
                $allRelevantEntities = array();
                $oldLocale = setlocale(LC_CTYPE, null);
                setlocale(LC_CTYPE, array('en_GB', 'en_GB.utf-8'));
                foreach ($allFoundEntities as $foundEntity) {
                    if (false !== stristr(iconv('UTF-8', 'ASCII//TRANSLIT', $foundEntity->__toString()), iconv('UTF-8', 'ASCII//TRANSLIT', $term))) {
                        $allRelevantEntities[] = $foundEntity;
                    }
                }
                setlocale(LC_CTYPE, $oldLocale);
            } else {
                // no $term specified, all found are relevant
                $allRelevantEntities = $allFoundEntities;
            }
        }
        $returnArray = array();
        $returnArray['totalCount'] = count($allRelevantEntities);

        // limit to the number of tags
        $returnArray['entities'] = array();
        for ($i = ($limit * $page - $limit); $i < min(($limit * $page), $returnArray['totalCount']); ++$i) {
            $returnArray['entities'][] = $allRelevantEntities[$i];
        }

        $more = false;
        if ($limit * $page < $returnArray['totalCount']) {
            $more = true;
        }

        $entities = array();
        foreach ($returnArray['entities'] as $entity) {
            $entities[] = array(
                'id' => $entity->getId(),
                'text' => (string) $entity,
            );
        }

        if ($request->query->get('any_none') && $page == 1) {
            $anyNone = $request->query->get('any_none');
            $anyNoneElements = explode(',', $anyNone);

            foreach ($anyNoneElements as $anyNoneElement) {
                $anyNoneElementParts = explode(':', $anyNoneElement);
                $entities[] = array(
                    'id' => $anyNoneElementParts[0],
                    'text' => $anyNoneElementParts[1],
                );
            }
        }

        return new JsonResponse(array(
            'results' => $entities,
            'more' => $more,
        ));
    }
}
