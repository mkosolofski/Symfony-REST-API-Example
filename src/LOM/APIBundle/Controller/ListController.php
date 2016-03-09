<?php
/**
 * Contains LOM\APIBundle\Controller\ListController
 *
 * @package LOM\APIBundle
 * @subpackage Controller
 */

namespace LOM\APIBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use LOM\ListBundle\Entity\ListEntity;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

/**
 * Contains list api methods.
 *
 * @package LOM\APIBundle
 * @subpackage Controller
 */
class ListController extends Controller
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * __construct 
     * 
     * @param Doctrine\ORM\EntityManager $entityManager 
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Route("/list/{id}", requirements={"id"="\d+"})
     * @Rest\View
     * @ApiDoc(
     *     section = "List",
     *     description = "Retreives a list.",
     *     output="LOM\ListBundle\Entity\ListEntity",
     *     statusCodes = {
     *         200="List found.",
     *         204="List not found for given id (no body in response)."
     *     }
     * )
     */
    public function getListAction($id)
    {
        return $this->entityManager
            ->getRepository('LOMListBundle:ListEntity')
            ->find($id);
    }

    /**
     * @Rest\Route("/list")
     * @ParamConverter("listEntity", converter="fos_rest.request_body")
     * @Rest\View
     * @ApiDoc(
     *     section = "List",
     *     description = "Creates a list.",
     *     input = {
     *         "class" : "LOM\ListBundle\Entity\ListEntity",
     *         "groups" : {"API_LIST_CREATE"},
     *         "parsers" = {
     *             "LOM\APIBundle\Parser\Api"
     *         }
     *     },
     *     statusCodes = {
     *         200="List created."
     *     }
     * )
     */
    public function postListAction(
        ListEntity $listEntity,
        ConstraintViolationList $validationErrors
    ) {
        $errors = array();
        foreach($validationErrors as $validationError) {
            $errors[] = $validationError->getMessage();
        }
        if ($errors) throw new BadRequestHttpException(implode(' ', $errors));
 
        if ($items = $listEntity->getItems()) {
            foreach($items as $item) $item->setList($listEntity);
        }

        $this->entityManager->persist($listEntity);
        $this->entityManager->flush($listEntity);
        return $listEntity;
    }
    
    /**
     * @Rest\Route("/list/{id}", requirements={"id"="\d+"})
     * @ParamConverter("listEntity", converter="fos_rest.request_body")
     * @Rest\View
     * @ApiDoc(
     *     section = "List",
     *     description = "Updates a list.",
     *     input = {
     *         "class" : "LOM\ListBundle\Entity\ListEntity",
     *         "groups" : {"API_LIST_UPDATE"},
     *         "parsers" = {
     *             "LOM\APIBundle\Parser\Api"
     *         }
     *     },
     *     statusCodes = {
     *         200="List updated."
     *     }
     * )
     */
    public function updateListAction(
        $id,
        ListEntity $listEntity
    ) {
        $list = $this->getListAction($id);
        $list->updateProperties($listEntity->toArray());
        $this->entityManager->persist($list);
        $this->entityManager->flush();
        return $list;
    }
    
    /**
     * @Rest\Route("/list/{id}", requirements={"id"="\d+"})
     * @Rest\View
     * @ApiDoc(
     *     section = "List",
     *     description = "Deletes a list.",
     *     statusCodes = {
     *         204="List deleted."
     *     }
     * )
     */               
    public function deleteListAction($id)
    {
        $this->entityManager
           ->createQueryBuilder()
           ->delete('LOM\ListBundle\Entity\ListEntity', 'l')
           ->where('l.id = ?1')
           ->setParameter('1', $id)
           ->getQuery()
           ->execute();
    }
}
