<?php
/**
 * Contains LOM\APIBundle\Controller\ListItemController
 *
 * @package LOM\APIBundle
 * @subpackage Controller
 */

namespace LOM\APIBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use LOM\ListBundle\Entity\ListItem;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

/**
 * Contains list item api methods.
 *
 * @package LOM\APIBundle
 * @subpackage Controller
 */
class ListItemController extends Controller
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
     * @Rest\Route("/list/item/{id}", requirements={"id"="\d+"})
     * @Rest\View
     * @ApiDoc(
     *     section = "List Item",
     *     description = "Retreives a list item.",
     *     output="LOM\ListBundle\Entity\ListItem",
     *     statusCodes = {
     *         200="List item found.",
     *         204="List not found for given id (no body in response)."
     *     }
     * )
     */
    public function getListItemAction($id)
    {
        return $this->entityManager
            ->getRepository('LOMListBundle:ListItem')
            ->find($id);
    }

    /**
     * @Rest\Route("/list/{id}/item")
     * @ParamConverter("listItem", converter="fos_rest.request_body")
     * @Rest\View
     * @ApiDoc(
     *     section = "List Item",
     *     description = "Creates a list item.",
     *     input = {
     *         "class" : "LOM\ListBundle\Entity\ListItem",
     *         "groups" : {"API_LIST_ITEM_CREATE"},
     *         "parsers" = {
     *             "LOM\APIBundle\Parser\Api"
     *         }
     *     },
     *     statusCodes = {
     *         200="List item created."
     *     }
     * )
     */
    public function postListItemAction(
        $id,
        ListItem $listItem,
        ConstraintViolationList $validationErrors
    ) {
        $errors = array();
        foreach($validationErrors as $validationError) {
            $errors[] = $validationError->getMessage();
        }
        if ($errors) throw new BadRequestHttpException(implode(' ', $errors));
        
        $listItem->setList(
            $this->entityManager
                ->getRepository('LOMListBundle:ListEntity')
                ->find($id)
        );

        $this->entityManager->persist($listItem);
        $this->entityManager->flush($listItem);
        return $listItem;
    }

    /**
     * @Rest\Route("/list/item/{id}", requirements={"id"="\d+"})
     * @ParamConverter("listItem", converter="fos_rest.request_body")
     * @Rest\View
     * @ApiDoc(
     *     section = "List Item",
     *     description = "Updates a list item.",
     *     input = {
     *         "class" : "LOM\ListBundle\Entity\ListItem",
     *         "groups" : {"API_LIST_ITEM_UPDATE"},
     *         "parsers" = {
     *             "LOM\APIBundle\Parser\Api"
     *         }
     *     },
     *     statusCodes = {
     *         200="List item updated."
     *     }
     * )
     */
    public function updateListItemAction(
        $id,
        ListItem $listItem
    ) {
        $item = $this->getListItemAction($id);
        $item->updateProperties($listItem->toArray());
        $this->entityManager->persist($item);
        $this->entityManager->flush();
        return $item;
    }

    /**
     * @Rest\Route("/list/item/{id}", requirements={"id"="\d+"})
     * @Rest\View
     * @ApiDoc(
     *     section = "List Item",
     *     description = "Deletes a list item.",
     *     statusCodes = {
     *         204="List item deleted."
     *     }
     * )
     */               
    public function deleteListItemAction($id)
    {
        $this->entityManager
           ->createQueryBuilder()
           ->delete('LOM\ListBundle\Entity\ListItem', 'li')
           ->where('li.id = ?1')
           ->setParameter('1', $id)
           ->getQuery()
           ->execute();
    }
}
