<?php
/**
 * Contains LOM\ListBundle\Entity\ListEntity
 *
 * @package LOM\ListBundle
 * @subpackage Entity
 */

namespace LOM\ListBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Called ListEntity since List is reserved.
 *
 * @JMS\ExclusionPolicy("All")
 * @ORM\Entity
 * @ORM\Table(name="list", indexes={@ORM\Index(name="idx_name", columns={"name"})})
 * @package LOM\ListBundle
 * @subpackage Entity
 */
class ListEntity extends EntityAbstract
{
    /**
     * The list id
     *
     * @Assert\Regex(message="'id' - required.", pattern="/^\d*$/")
     * @JMS\Expose
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The name of the list
     *
     * @JMS\Groups({"API_LIST_CREATE", "API_LIST_UPDATE"})
     * @Assert\NotBlank(message="'name' - required and must be a non-empty string.")
     * @JMS\Expose
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var ListItem $items
     *
     * @JMS\Groups({"API_LIST_CREATE", "API_LIST_UPDATE"})
     * @JMS\Expose
     * @ORM\OneToMany(targetEntity="ListItem", mappedBy="list", cascade={"all"})
     */
    protected $items;
    
    /**
     * @var ListItem $items
     *
     * @ORM\OneToMany(targetEntity="ListItem", mappedBy="list", cascade={"all"}, indexBy="id", fetch="EXTRA_LAZY")
     */
    protected $itemsIdIndexed;
 
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
     * Returns the name
     * 
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     * 
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Returns an array of all list items. 
     * 
     * @return ArrayCollection|null ListItem[]
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * Removes all items on the list.
     */
    public function removeItems()
    {
        $this->items->clear();
    }

    /**
     * Returns an array of all list items. 
     * 
     * @param ListItem $listItem
     * @return ArrayCollection ListItem[]
     */
    public function addItem(ListItem $listItem)
    {
        $this->items->add($listItem);
        $listItem->setList($this);
        return $this->items;
    }

    public function updateItems(array $items)
    {
        foreach ($items as $itemProperties) {
            if (!array_key_exists('id', $itemProperties)
                || !filter_var($itemProperties['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))
            ) {
                continue;
            }

            if ($item = $this->itemsIdIndexed->get($itemProperties['id'])) {
                $item->updateProperties($itemProperties);
            }
        }
    }
}   
