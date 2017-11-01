<?php

namespace CubeTools\CubeCustomFieldsBundle\Controller;

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
    public function ajaxAction(Request $request)
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
        $fieldConfig = $this->get('CubeTools\CubeCustomFieldsBundle\Utils\ConfigReader')->getConfigForFieldId($fieldId);
        if (array_key_exists('filters', $fieldConfig)) {
            $filters = $fieldConfig['filters'];
        } else {
            $filters = array();
        }

        $class = $fieldConfig['field_options']['class']; // read from the configuration: parameter containing the class of the entities to be looked up
        $dbStorage = $class::getStorageFieldName();
        $qb = $em->createQueryBuilder()
            ->select('cf')
            ->from($class, 'cf');
        foreach ($filters as $field => $value) {
            $qb->andWhere(sprintf('cf.%s = :%s', $field, $field))
                ->setParameter($field, $value)
                // here we should add a switch based on the dbStorage field type
                ->andWhere('cf.' . $dbStorage . ' LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }
        
        $allRelevantEntities = $qb->getQuery()->getResult();
        $returnArray = array();
        $returnArray['totalCount'] = count($allRelevantEntities);

        // limit to the number of tags
        $returnArray['entities'] = array();
        for ($i = ($limit*$page-$limit); $i<min(($limit*$page), $returnArray['totalCount']); $i++) {
            $returnArray['entities'][] = $allRelevantEntities[$i];
        }

        $more = false;
        if ($limit*$page < $returnArray['totalCount']) {
            $more = true;
        }

        $entities = array();
        foreach ($returnArray['entities'] as $entity) {
            $entities[] = array(
                'id' => $entity->getId(),
                'text' => (string)$entity,
            );
        }

        return new JsonResponse(array(
        'results' => $entities,
        'more' => $more,
        ));
    }
}
