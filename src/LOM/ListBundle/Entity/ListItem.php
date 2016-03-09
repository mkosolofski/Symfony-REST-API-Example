<?php
/**
 * Contains LOM\ListBundle\Entity\ListItem
 *
 * @package LOM\ListBundle
 * @subpackage Entity
 */
namespace LOM\ListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use LOM\ListBundle\Entity\ListEntity;

/**
 * @JMS\ExclusionPolicy("All")
 * @ORM\Table(name="list_item")
 * @ORM\Entity
 * @package LOM\ListBundle
 * @subpackage Entity
 */
class ListItem extends EntityAbstract
{
    /**
     * The list item id.
     *
     * @JMS\Groups({"API_LIST_UPDATE"})
     * @Assert\Regex(message="'id' - required.", pattern="/^\d*$/")
     * @JMS\Expose
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @JMS\Groups({"API_LIST_ITEM_CREATE"})
     * @ORM\ManyToOne(targetEntity="ListEntity", inversedBy="items")
     * @ORM\JoinColumn(name="listId", referencedColumnName="id", onDelete="Cascade")
     */
    protected $list;
    
    /**
     * An entry in the list
     * 
     * @JMS\Groups(
     *     {
     *         "API_LIST_ITEM_CREATE",
     *         "API_LIST_CREATE",
     *         "API_LIST_UPDATE",
     *         "API_LIST_ITEM_UPDATE"
     *     }
     * )
     * @Assert\NotNull(message="'entry' - required.")
     * @JMS\Expose
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $entry;

    /**
     * Returns the id
     * 
     * @return integer The id.
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Sets the list that this item belongs to.
     * 
     * @param ListEntity $list 
     */
    public function setList(ListEntity $list)
    {
        $this->list = $list;
    }

    /**
     * Returns the associated list.
     * 
     * @return ListEntity
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Returns the list entry. 
     * 
     * @return string The list entry.
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Sets the entry. 
     * 
     * @param string The list entry.
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }
}
